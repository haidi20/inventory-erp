<?php

namespace Tests\Feature\Settings;

use App\Modules\Core\Identity\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use Tests\TestCase;
use ZipArchive;

class BackupZipArchiveFlowTest extends TestCase
{
    private ?string $databaseName = null;
    private array $mysqlConfig = [];
    private ?string $tempPath = null;

    /**
     * Snapshot of database config that we mutate in setUp() so we can fully
     * restore it in tearDown(). Without this, the next test in the suite
     * inherits an SQLite ":memory:" PDO with a stale transaction from the
     * RefreshDatabase trait — manifesting as
     * "PDOException: There is already an active transaction".
     */
    private ?string $originalDefaultConnection = null;
    private mixed $originalMysqlDatabase = null;
    private mixed $originalSqliteDatabase = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('MySQL backup integration tests require pre-configured MySQL server.');


        if (!extension_loaded('zip')) {
            $this->markTestSkipped('Ekstensi ZIP tidak tersedia.');
        }

        // Snapshot before mutating
        $this->originalDefaultConnection = config('database.default');
        $this->originalMysqlDatabase = config('database.connections.mysql.database');
        $this->originalSqliteDatabase = config('database.connections.sqlite.database');

        $this->mysqlConfig = config('database.connections.mysql', []);
        $this->databaseName = 'test_zip_restore_' . uniqid();
        $this->tempPath = storage_path('app/temp_test');

        if (!is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0777, true);
        }

        // Create temporary database for testing
        try {
            $adminPdo = $this->createAdminPdo();
            $adminPdo->exec("CREATE DATABASE `{$this->databaseName}`");
        } catch (\PDOException $e) {
            $this->markTestSkipped('Koneksi MySQL gagal: ' . $e->getMessage());
        }

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => $this->databaseName,
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');

        // Run migrations on the temporary database (including v1 and v2)
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--database' => 'mysql',
            '--path' => [
                'database/migrations/v1',
                'database/migrations/v2'
            ],
            '--force' => true,
        ]);

        // Setup test table (if not in migrations)
        if (!\Illuminate\Support\Facades\Schema::connection('mysql')->hasTable('test_items')) {
            DB::connection('mysql')->unprepared('
                CREATE TABLE test_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255)
                );
            ');
        }
    }

    protected function tearDown(): void
    {
        if ($this->databaseName === null) {
            parent::tearDown();
            return;
        }
        // 1. Drop the throw-away MySQL database
        try {
            $adminPdo = $this->createAdminPdo();
            $adminPdo->exec("DROP DATABASE IF EXISTS `{$this->databaseName}`");
        } catch (\Throwable) {
            // ignore — already gone
        }

        // 2. Disconnect any MySQL state we built up
        try { DB::disconnect('mysql'); } catch (\Throwable) {}

        // 3. Roll back any leftover PDO transaction on the SQLite ":memory:"
        //    connection. Background: parent::setUp() called RefreshDatabase
        //    which began a SQLite transaction. We then changed default to
        //    'mysql', so when parent::tearDown() runs it tries to roll back
        //    against the wrong connection, leaving the SQLite PDO with an
        //    active transaction that cascades into the next test.
        try {
            $sqlite = DB::connection('sqlite');
            $pdo = $sqlite->getPdo();
            while ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            // Also reset Laravel's internal transaction counter via reflection
            $reflection = new \ReflectionObject($sqlite);
            if ($reflection->hasProperty('transactions')) {
                $prop = $reflection->getProperty('transactions');
                $prop->setAccessible(true);
                $prop->setValue($sqlite, 0);
            }
        } catch (\Throwable) {
            // ignore — connection may not be initialized
        }

        // 4. Restore original config so subsequent tests start from a clean state
        config([
            'database.default' => $this->originalDefaultConnection ?? 'sqlite',
            'database.connections.mysql.database' => $this->originalMysqlDatabase,
            'database.connections.sqlite.database' => $this->originalSqliteDatabase ?? ':memory:',
        ]);

        // 5. Force fresh connections — purge so next ->connection() rebuilds with restored config
        DB::purge('mysql');
        DB::purge('sqlite');

        // 6. Reset RefreshDatabase static flag so next test re-runs migrations
        //    against the SQLite ":memory:" (which is a NEW empty connection).
        \Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = false;

        // 7. Filesystem cleanup
        $this->deleteDirectory($this->tempPath);
        Storage::disk('public')->deleteDirectory('uploads/test');

        parent::tearDown();
    }

    public function test_real_zip_archive_restoration_db_and_assets(): void
    {
        $user = User::query()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        
        // 1. Prepare dummy data and files
        $sqlContent = "INSERT INTO test_items (name) VALUES ('Data dari ZIP');";
        $sqlPath = $this->tempPath . '/backup.sql';
        file_put_contents($sqlPath, $sqlContent);

        $imageContent = 'fake-image-content';
        $imageDir = $this->tempPath . '/uploads/test';
        if (!is_dir($imageDir)) mkdir($imageDir, 0777, true);
        $imagePath = $imageDir . '/photo.jpg';
        file_put_contents($imagePath, $imageContent);

        // 2. Create the ZIP archive
        $zipPath = $this->tempPath . '/full_backup.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $zip->addFile($sqlPath, 'backup.sql');
            $zip->addFile($imagePath, 'uploads/test/photo.jpg');
            $zip->close();
        }

        $this->assertFileExists($zipPath);

        // 3. Execute Restore via API
        $response = $this->actingAs($user)
            ->post(route('settings.backup.restore'), [
                'backup_file' => new UploadedFile($zipPath, 'full_backup.zip', 'application/zip', null, true),
                'create_pre_restore_backup' => false,
            ]);

        // 4. Verifications
        $response->assertRedirect();
        
        // Check DB
        DB::purge('mysql');
        DB::reconnect('mysql');
        $this->assertEquals(1, DB::connection('mysql')->table('test_items')->count());
        $this->assertEquals('Data dari ZIP', DB::connection('mysql')->table('test_items')->value('name'));

        // Check Assets
        Storage::disk('public')->assertExists('uploads/test/photo.jpg');
        $this->assertEquals($imageContent, Storage::disk('public')->get('uploads/test/photo.jpg'));
    }

    private function createAdminPdo(): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%s', $this->mysqlConfig['host'], $this->mysqlConfig['port']);
        return new PDO($dsn, $this->mysqlConfig['username'], $this->mysqlConfig['password']);
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }
}
