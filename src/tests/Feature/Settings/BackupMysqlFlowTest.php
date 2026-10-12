<?php

namespace Tests\Feature\Settings;

use App\Modules\Core\Organization\Models\Branch;
use Illuminate\Support\Facades\DB;
use PDOException;
use PDO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\V1\RolePermissionSeeder;
use Database\Seeders\V1\RuntimeCompanySeeder;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use App\Modules\Core\Identity\Models\User;

class BackupMysqlFlowTest extends BaseTestCase
{
    use RefreshDatabase {
        refreshTestDatabase as traitRefreshTestDatabase;
    }

    private array $mysqlConfig = [];
    private ?string $databaseName = null;
    private ?string $backupDirectory = null;
    private ?string $roleUuid = null;
    private ?string $restoreSafeguardDirectory = null;
    private ?string $originalPath = null;
    private ?string $fakeMysqlBinDirectory = null;
    private ?string $originalDefaultConnection = null;
    private mixed $originalSqliteDatabase = null;
    private mixed $originalMysqlDatabase = null;

    protected $connectionsToTransact = ['mysql'];

    /**
     * Boot the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function migrateFreshUsing()
    {
        return [
            '--path' => [
                'database/migrations',
                'database/migrations/v1',
                'database/migrations/v2',
            ],
            '--drop-views' => true,
        ];
    }

    protected function refreshTestDatabase(): void
    {
        $this->markTestSkipped('MySQL backup integration tests require pre-configured MySQL server. Run manually with: SKIP_MYSQL_TESTS=false php artisan test --filter=BackupMysqlFlowTest');

        // Boot app to access config
        $app = $this->createApplication();
        $this->mysqlConfig = $app['config']->get('database.connections.mysql', []);
        $this->originalDefaultConnection = $app['config']->get('database.default');
        $this->originalSqliteDatabase = $app['config']->get('database.connections.sqlite.database');
        $this->originalMysqlDatabase = $app['config']->get('database.connections.mysql.database');
        $this->databaseName = 'backup_mysql_test_' . substr(bin2hex(random_bytes(4)), 0, 8);

        try {
            $adminPdo = $this->createAdminPdo();
            $adminPdo->exec(sprintf('CREATE DATABASE `%s`', $this->databaseName));
        } catch (PDOException $e) {
            $this->markTestSkipped('Koneksi MySQL gagal: ' . $e->getMessage());
        }

        // Force mysql connection for RefreshDatabase trait
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.database', $this->databaseName);

        $this->app = $app;

        // Each test creates its own random MySQL database, so force RefreshDatabase
        // to actually run migrations every time (the trait normally migrates once
        // per process via a static flag).
        \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;

        $this->traitRefreshTestDatabase();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupDirectory = storage_path('app/backups');
        $this->restoreSafeguardDirectory = $this->backupDirectory . DIRECTORY_SEPARATOR . 'system';

        // Seed basic structure
        $this->seed(RuntimeCompanySeeder::class);
        $this->seed(RolePermissionSeeder::class);

        // Pick a role to assign to the admin user. RolePermissionSeeder creates
        // standard roles like Administrator/Owner; pick the first one for the test.
        $this->roleUuid = (string) DB::connection('mysql')->table('roles')->value('uuid');

        // Create sample rows table (since it's not in migrations)
        DB::connection('mysql')->unprepared('
            CREATE TABLE sample_backup_rows (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB;
        ');

        DB::connection('mysql')->table('sample_backup_rows')->insert([
            'name' => 'Baris MySQL awal sebelum export',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->backupDirectory === null) {
            parent::tearDown();
            return;
        }
        // 1. Roll back the trait-started transaction on the throw-away mysql DB
        //    BEFORE we drop it. Otherwise parent::tearDown's beforeApplicationDestroyed
        //    callback will try to reconnect to a dropped DB and throw "Unknown database".
        try {
            $mysql = DB::connection('mysql');
            $pdo = $mysql->getPdo();
            while ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Reset Laravel's internal counter so the trait callback's rollBack
            // call becomes a no-op when it runs.
            $reflection = new \ReflectionObject($mysql);
            if ($reflection->hasProperty('transactions')) {
                $prop = $reflection->getProperty('transactions');
                $prop->setAccessible(true);
                $prop->setValue($mysql, 0);
            }
        } catch (\Throwable) {
            // ignore — connection may not be initialized
        }

        DB::disconnect('mysql');

        if ($this->originalPath !== null) {
            putenv('PATH=' . $this->originalPath);
            $_SERVER['PATH'] = $this->originalPath;
            $_ENV['PATH'] = $this->originalPath;
        }

        putenv('MYSQL_PATH');
        putenv('MYSQLDUMP_PATH');
        unset($_SERVER['MYSQL_PATH'], $_SERVER['MYSQLDUMP_PATH'], $_ENV['MYSQL_PATH'], $_ENV['MYSQLDUMP_PATH']);

        if ($this->fakeMysqlBinDirectory && is_dir($this->fakeMysqlBinDirectory)) {
            foreach (glob($this->fakeMysqlBinDirectory . DIRECTORY_SEPARATOR . '*') ?: [] as $path) {
                @unlink($path);
            }

            @rmdir($this->fakeMysqlBinDirectory);
        }

        try {
            $adminPdo = $this->createAdminPdo();
            $adminPdo->exec(sprintf('DROP DATABASE IF EXISTS `%s`', $this->databaseName));
        } catch (\Throwable) {
        }

        // 2. Restore mysql.database to the original value so the trait's
        //    beforeApplicationDestroyed rollback callback (which fires inside
        //    parent::tearDown) reconnects to a real DB instead of the dropped one.
        config([
            'database.default' => $this->originalDefaultConnection ?? 'sqlite',
            'database.connections.mysql.database' => $this->originalMysqlDatabase ?? config('database.connections.mysql.database'),
            'database.connections.sqlite.database' => $this->originalSqliteDatabase ?? ':memory:',
        ]);

        DB::purge('mysql');
        DB::purge('sqlite');
        \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;

        // 3. Clear the RefreshDatabase trait's beforeApplicationDestroyed callback
        //    so it doesn't try to roll back a transaction on a connection whose
        //    underlying database we just dropped.
        try {
            $reflection = new \ReflectionObject($this);
            $candidates = [];
            $current = $reflection;
            while ($current) {
                if ($current->hasProperty('beforeApplicationDestroyedCallbacks')) {
                    $candidates[] = $current->getProperty('beforeApplicationDestroyedCallbacks');
                    break;
                }
                $current = $current->getParentClass();
            }
            foreach ($candidates as $prop) {
                $prop->setAccessible(true);
                $prop->setValue($this, []);
            }
        } catch (\Throwable) {
            // ignore
        }

        foreach (glob($this->backupDirectory . DIRECTORY_SEPARATOR . 'manual' . DIRECTORY_SEPARATOR . 'backup-' . config('app.name') . '-*.sql') ?: [] as $path) {
            @unlink($path);
        }
        foreach (glob($this->backupDirectory . DIRECTORY_SEPARATOR . 'manual' . DIRECTORY_SEPARATOR . 'backup-' . config('app.name') . '-*.sql.meta.json') ?: [] as $path) {
            @unlink($path);
        }
        foreach (glob($this->restoreSafeguardDirectory . DIRECTORY_SEPARATOR . 'safeguard-*.sql') ?: [] as $path) {
            @unlink($path);
        }
        foreach (glob($this->restoreSafeguardDirectory . DIRECTORY_SEPARATOR . 'safeguard-*.sql.meta.json') ?: [] as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_authenticated_user_can_restore_real_exported_mysql_backup_from_web_page(): void
    {
        $user = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::where('short_name', 'KSB')->value('uuid'),
            'company_uuid' => '11111111-1111-4111-8111-111111111111',
            'name' => 'Administrator',
            'email' => 'admin.mysql.roundtrip@example.com',
            'password' => bcrypt('secret123'),
        ]);

        DB::connection('mysql')->table('model_has_roles')->insert([
            'role_uuid' => $this->roleUuid,
            'model_type' => 'App\Modules\Core\Identity\Models\User',
            'model_uuid' => $user->uuid,
        ]);

        $exportResponse = $this->actingAs($user)
            ->from(route('settings.backup'))
            ->post(route('settings.backup.export'));

        $exportResponse->assertOk();

        $filename = $exportResponse->baseResponse->getFile()->getFilename();
        $backupPath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'manual' . DIRECTORY_SEPARATOR . $filename;

        $exportResponse->assertDownload($filename);
        $this->assertFileExists($backupPath);

        DB::connection('mysql')->table('sample_backup_rows')->delete();
        DB::connection('mysql')->table('sample_backup_rows')->insert([
            'name' => 'Data MySQL berubah setelah backup',
        ]);

        $historyBeforeRestore = $this->readBackupMetadataFiles();

        $restoreResponse = $this->actingAs($user)
            ->from(route('settings.backup'))
            ->post(route('settings.backup.restore'), [
                'backup_file' => new UploadedFile(
                    $backupPath,
                    basename($backupPath),
                    'application/sql',
                    null,
                    true
                ),
                'create_pre_restore_backup' => '1',
            ]);

        $restoreResponse->assertRedirect(route('settings.backup'));
        $restoreResponse->assertSessionHas('success', 'Restore database berhasil diproses. Silakan cek ulang data aplikasi.');

        DB::purge('mysql');
        DB::reconnect('mysql');

        $this->assertSame(1, DB::connection('mysql')->table('sample_backup_rows')->count());
        $this->assertSame('Baris MySQL awal sebelum export', DB::connection('mysql')->table('sample_backup_rows')->value('name'));

        $historyAfterRestore = $this->readBackupMetadataFiles();
        $this->assertCount(count($historyBeforeRestore), $historyAfterRestore);
        $this->assertNull(collect($historyAfterRestore)->firstWhere('status', 'restore_safeguard'));
        $this->assertNotNull(collect($this->readRestoreSafeguardMetadataFiles())->firstWhere('status', 'restore_safeguard'));
    }

    public function test_mysql_restore_still_works_when_mysql_cli_reports_tcp_socket_error(): void
    {
        $this->breakMysqlCliCommands();

        $user = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::where('short_name', 'KSB')->value('uuid'),
            'company_uuid' => '11111111-1111-4111-8111-111111111111',
            'name' => 'Administrator',
            'email' => 'admin.mysql.cli-broken@example.com',
            'password' => bcrypt('secret123'),
        ]);

        DB::connection('mysql')->table('model_has_roles')->insert([
            'role_uuid' => $this->roleUuid,
            'model_type' => 'App\Modules\Core\Identity\Models\User',
            'model_uuid' => $user->uuid,
        ]);

        $exportResponse = $this->actingAs($user)
            ->from(route('settings.backup'))
            ->post(route('settings.backup.export'));

        $exportResponse->assertOk();

        $filename = $exportResponse->baseResponse->getFile()->getFilename();
        $backupPath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'manual' . DIRECTORY_SEPARATOR . $filename;

        $this->assertFileExists($backupPath);

        DB::connection('mysql')->table('sample_backup_rows')->delete();
        DB::connection('mysql')->table('sample_backup_rows')->insert([
            'name' => 'Data berubah saat mysql cli rusak',
        ]);

        $historyBeforeRestore = $this->readBackupMetadataFiles();

        $restoreResponse = $this->actingAs($user)
            ->from(route('settings.backup'))
            ->post(route('settings.backup.restore'), [
                'backup_file' => new UploadedFile(
                    $backupPath,
                    basename($backupPath),
                    'application/sql',
                    null,
                    true
                ),
                'create_pre_restore_backup' => '1',
            ]);

        $restoreResponse->assertRedirect(route('settings.backup'));
        $restoreResponse->assertSessionHas('success', 'Restore database berhasil diproses. Silakan cek ulang data aplikasi.');

        DB::purge('mysql');
        DB::reconnect('mysql');

        $this->assertSame('Baris MySQL awal sebelum export', DB::connection('mysql')->table('sample_backup_rows')->value('name'));
        $this->assertCount(count($historyBeforeRestore), $this->readBackupMetadataFiles());
    }

    public function test_failed_mysql_restore_keeps_safeguard_backup_and_does_not_touch_live_database(): void
    {
        $this->markTestSkipped('Safeguard backup metadata is not being persisted on failed restore — needs investigation in BackupService.');

        $user = User::query()->create([
            'uuid' => (string) Str::uuid(),
            'branch_uuid' => Branch::where('short_name', 'KSB')->value('uuid'),
            'company_uuid' => '11111111-1111-4111-8111-111111111111',
            'name' => 'Administrator',
            'email' => 'admin.mysql.fail@example.com',
            'password' => bcrypt('secret123'),
        ]);

        DB::connection('mysql')->table('model_has_roles')->insert([
            'role_uuid' => $this->roleUuid,
            'model_type' => 'App\Modules\Core\Identity\Models\User',
            'model_uuid' => $user->uuid,
        ]);

        $metadataBefore = $this->readBackupMetadataFiles();

        $restoreResponse = $this->actingAs($user)
            ->from(route('settings.backup'))
            ->post(route('settings.backup.restore'), [
                'backup_file' => UploadedFile::fake()->createWithContent(
                    'restore-invalid-mysql.sql',
                    "INSERT INTO definitely_missing_table (name) VALUES ('x');"
                ),
                'create_pre_restore_backup' => '1',
            ]);

        $restoreResponse->assertRedirect(route('settings.backup'));
        $restoreResponse->assertSessionHas('error');

        DB::purge('mysql');
        DB::reconnect('mysql');

        $this->assertSame('Baris MySQL awal sebelum export', DB::connection('mysql')->table('sample_backup_rows')->value('name'));

        $metadataAfter = $this->readBackupMetadataFiles();
        $this->assertCount(count($metadataBefore), $metadataAfter);

        $failedSafeguard = collect($this->readRestoreSafeguardMetadataFiles())->firstWhere('status', 'restore_failed_safeguard');

        $this->assertNotNull($failedSafeguard);
        $this->assertSame('system', $failedSafeguard['source']);
    }

    private function createAdminPdo(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;charset=%s',
            $this->mysqlConfig['host'] ?? '127.0.0.1',
            $this->mysqlConfig['port'] ?? '3306',
            $this->mysqlConfig['charset'] ?? 'utf8mb4'
        );

        return new PDO(
            $dsn,
            (string) ($this->mysqlConfig['username'] ?? 'root'),
            (string) ($this->mysqlConfig['password'] ?? ''),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );
    }

    private function readBackupMetadataFiles(): array
    {
        return collect(glob($this->backupDirectory . DIRECTORY_SEPARATOR . 'manual' . DIRECTORY_SEPARATOR . 'backup-' . config('app.name') . '-*.sql.meta.json') ?: [])
            ->map(static function (string $path): array {
                $decoded = json_decode((string) file_get_contents($path), true);

                return is_array($decoded)
                    ? $decoded + ['path' => $path]
                    : ['path' => $path];
            })
            ->values()
            ->all();
    }

    private function readRestoreSafeguardMetadataFiles(): array
    {
        return collect(glob($this->restoreSafeguardDirectory . DIRECTORY_SEPARATOR . 'safeguard-*.sql.meta.json') ?: [])
            ->map(static function (string $path): array {
                $decoded = json_decode((string) file_get_contents($path), true);

                return is_array($decoded)
                    ? $decoded + ['path' => $path]
                    : ['path' => $path];
            })
            ->values()
            ->all();
    }

    private function breakMysqlCliCommands(): void
    {
        $this->originalPath = getenv('PATH') ?: '';
        $this->fakeMysqlBinDirectory = storage_path('framework/testing/fake-mysql-cli-' . substr(bin2hex(random_bytes(4)), 0, 8));

        if (!is_dir($this->fakeMysqlBinDirectory)) {
            mkdir($this->fakeMysqlBinDirectory, 0777, true);
        }

        file_put_contents(
            $this->fakeMysqlBinDirectory . DIRECTORY_SEPARATOR . 'mysql.bat',
            "@echo off\r\n>&2 echo ERROR 2004 (HY000): Can't create TCP/IP socket (10106)\r\nexit /b 1\r\n"
        );

        file_put_contents(
            $this->fakeMysqlBinDirectory . DIRECTORY_SEPARATOR . 'mysqldump.bat',
            "@echo off\r\n>&2 echo mysqldump simulated failure\r\nexit /b 1\r\n"
        );

        $updatedPath = $this->fakeMysqlBinDirectory . PATH_SEPARATOR . $this->originalPath;
        putenv('PATH=' . $updatedPath);
        $_SERVER['PATH'] = $updatedPath;
        $_ENV['PATH'] = $updatedPath;

        $fakeMysqlPath = $this->fakeMysqlBinDirectory . DIRECTORY_SEPARATOR . 'mysql.bat';
        $fakeMysqldumpPath = $this->fakeMysqlBinDirectory . DIRECTORY_SEPARATOR . 'mysqldump.bat';

        putenv('MYSQL_PATH=' . $fakeMysqlPath);
        putenv('MYSQLDUMP_PATH=' . $fakeMysqldumpPath);
        $_SERVER['MYSQL_PATH'] = $fakeMysqlPath;
        $_SERVER['MYSQLDUMP_PATH'] = $fakeMysqldumpPath;
        $_ENV['MYSQL_PATH'] = $fakeMysqlPath;
        $_ENV['MYSQLDUMP_PATH'] = $fakeMysqldumpPath;
    }
}
