<?php

namespace Tests\Feature\Settings;

use App\Modules\Core\Identity\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackupUserFlowTest extends TestCase
{
    use WithoutMiddleware;

    private string $databasePath;
    private string $backupDirectory;
    private ?string $createdBackupPath = null;

    public function refreshDatabase(): void
    {
        // Test ini membangun database SQLite manual agar skenario backup tetap terisolasi.
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->databasePath = storage_path('framework/testing/sqlite-backup-user-flow.sqlite');
        $this->backupDirectory = storage_path('app/backups');

        if (file_exists($this->databasePath)) {
            @unlink($this->databasePath);
        }

        if (!is_dir(dirname($this->databasePath))) {
            mkdir(dirname($this->databasePath), 0777, true);
        }

        if (!is_dir($this->backupDirectory)) {
            mkdir($this->backupDirectory, 0777, true);
        }

        touch($this->databasePath);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->databasePath,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE companies (
                uuid TEXT NOT NULL PRIMARY KEY,
                id INTEGER NULL,
                name TEXT NOT NULL,
                deleted_at INTEGER NOT NULL DEFAULT 0,
                created_at INTEGER NULL,
                updated_at INTEGER NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE branches (
                uuid TEXT NOT NULL PRIMARY KEY,
                id INTEGER NULL,
                company_uuid TEXT NOT NULL,
                name TEXT NOT NULL,
                short_name TEXT NOT NULL,
                deleted_at INTEGER NOT NULL DEFAULT 0,
                created_at INTEGER NULL,
                updated_at INTEGER NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE users (
                uuid TEXT NOT NULL PRIMARY KEY,
                id INTEGER NULL,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                branch_uuid TEXT NULL,
                company_uuid TEXT NULL,
                deleted_at INTEGER NOT NULL DEFAULT 0,
                remember_token TEXT NULL,
                created_at INTEGER NULL,
                updated_at INTEGER NULL
            );
        ');

        DB::connection('sqlite')->table('companies')->insert([
            'uuid' => 'company-sandbox-test',
            'name' => 'Sandbox Test',
            'deleted_at' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        DB::connection('sqlite')->table('branches')->insert([
            'uuid' => 'branch-sandbox-test',
            'company_uuid' => 'company-sandbox-test',
            'name' => 'Cabang Sandbox',
            'short_name' => 'KSB',
            'deleted_at' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        DB::connection('sqlite')->unprepared('
            CREATE TABLE database_backups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                path TEXT NOT NULL,
                size INTEGER NOT NULL,
                status TEXT NOT NULL,
                source TEXT NOT NULL,
                history_type TEXT NOT NULL,
                notes TEXT NULL,
                created_at INTEGER NOT NULL,
                updated_at INTEGER NOT NULL
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE sample_backup_rows (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL
            );
        ');

        DB::connection('sqlite')->table('sample_backup_rows')->insert([
            'name' => 'Baris awal sebelum export',
        ]);

        $manualDirectory = $this->backupDirectory . DIRECTORY_SEPARATOR . 'manual';
        $systemDirectory = $this->backupDirectory . DIRECTORY_SEPARATOR . 'system';
        if (!is_dir($manualDirectory)) {
            mkdir($manualDirectory, 0777, true);
        }
        if (!is_dir($systemDirectory)) {
            mkdir($systemDirectory, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (file_exists($this->databasePath)) {
            @unlink($this->databasePath);
        }

        $this->deleteDirectory($this->backupDirectory);

        parent::tearDown();
    }

    public function test_authenticated_user_can_export_database_backup_from_web_page(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.backup@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($user)
            ->post(route('settings.backup.export'));

        $response->assertOk();

        $filename = $response->baseResponse->getFile()->getFilename();
        $this->createdBackupPath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'manual' . DIRECTORY_SEPARATOR . $filename;

        $response->assertDownload($filename);
        $this->assertMatchesRegularExpression(
            '/^backup-[A-Za-z0-9_-]+-\d{4}-\d{2}-\d{2}_\d{6}(?:-\d+)?\.sql$/',
            $filename
        );
        $this->assertFileExists($this->createdBackupPath);
        $this->assertStringContainsString('sample_backup_rows', file_get_contents($this->createdBackupPath) ?: '');
        $this->assertStringContainsString('Baris awal sebelum export', file_get_contents($this->createdBackupPath) ?: '');
    }

    public function test_authenticated_user_can_export_database_backup_via_ajax(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.backup-ajax@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('settings.backup.export'));

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'filename',
            'download_url',
        ]);

        $filename = $response->json('filename');
        $this->createdBackupPath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'manual' . DIRECTORY_SEPARATOR . $filename;

        $this->assertMatchesRegularExpression(
            '/^backup-[A-Za-z0-9_-]+-\d{4}-\d{2}-\d{2}_\d{6}(?:-\d+)?\.sql$/',
            $filename
        );
        $this->assertFileExists($this->createdBackupPath);
        $this->assertStringContainsString('/settings/backup/download/', $response->json('download_url'));
    }

    public function test_authenticated_user_can_restore_database_backup_from_web_page(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.restore@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $restoreSql = <<<SQL
CREATE TABLE restore_probe (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    label TEXT NOT NULL
);
INSERT INTO restore_probe (label) VALUES ('Restore berhasil lewat web');
SQL;

        $response = $this->actingAs($user)
            ->from(route('settings.backup'))
            ->post(route('settings.backup.restore'), [
                'backup_file' => UploadedFile::fake()->createWithContent('restore-backup.sql', $restoreSql),
                'create_pre_restore_backup' => '0',
            ]);

        $response->assertRedirect(route('settings.backup'));
        $response->assertSessionHas('success', 'Restore database berhasil diproses. Silakan cek ulang data aplikasi.');
        $this->assertSame(1, DB::connection('sqlite')->table('restore_probe')->count());
        $this->assertSame('Restore berhasil lewat web', DB::connection('sqlite')->table('restore_probe')->value('label'));
    }

    public function test_authenticated_user_can_restore_database_backup_via_ajax(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.restore-ajax@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $restoreSql = <<<SQL
CREATE TABLE restore_probe_ajax (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    label TEXT NOT NULL
);
INSERT INTO restore_probe_ajax (label) VALUES ('Restore berhasil lewat AJAX');
SQL;

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->post(route('settings.backup.restore'), [
                'backup_file' => UploadedFile::fake()->createWithContent('restore-backup-ajax.sql', $restoreSql),
                'create_pre_restore_backup' => '0',
            ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Restore database berhasil diproses. Silakan cek ulang data aplikasi.',
        ]);

        $this->assertSame(1, DB::connection('sqlite')->table('restore_probe_ajax')->count());
        $this->assertSame('Restore berhasil lewat AJAX', DB::connection('sqlite')->table('restore_probe_ajax')->value('label'));
    }

    public function test_authenticated_user_can_restore_real_exported_backup_file_from_web_page(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.roundtrip@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $exportResponse = $this->actingAs($user)
            ->post(route('settings.backup.export'));

        $exportResponse->assertOk();

        $filename = $exportResponse->baseResponse->getFile()->getFilename();
        $this->createdBackupPath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'manual' . DIRECTORY_SEPARATOR . $filename;

        DB::connection('sqlite')->table('sample_backup_rows')->delete();
        DB::connection('sqlite')->table('sample_backup_rows')->insert([
            'name' => 'Data berubah setelah backup',
        ]);

        $restoreResponse = $this->actingAs($user)
            ->from(route('settings.backup'))
            ->post(route('settings.backup.restore'), [
                'backup_file' => new UploadedFile(
                    $this->createdBackupPath,
                    basename($this->createdBackupPath),
                    'application/sql',
                    null,
                    true
                ),
                'create_pre_restore_backup' => '0',
            ]);

        $restoreResponse->assertRedirect(route('settings.backup'));
        $restoreResponse->assertSessionHas('success', 'Restore database berhasil diproses. Silakan cek ulang data aplikasi.');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->assertSame(1, DB::connection('sqlite')->table('sample_backup_rows')->count());
        $this->assertSame('Baris awal sebelum export', DB::connection('sqlite')->table('sample_backup_rows')->value('name'));
    }

    public function test_failed_restore_does_not_create_new_backup_history(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.restore-fail@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $historyBefore = $this->getBackupHistoryFiles();
        $backupRowsBefore = DB::connection('sqlite')->table('database_backups')->count();

        $response = $this->actingAs($user)
            ->from(route('settings.backup'))
            ->post(route('settings.backup.restore'), [
                'backup_file' => UploadedFile::fake()->createWithContent('restore-invalid.sql', "INSERT INTO definitely_missing_table (name) VALUES ('x');"),
                'create_pre_restore_backup' => '1',
            ]);

        $response->assertRedirect(route('settings.backup'));
        $response->assertSessionHas('error');
        $historyAfter = $this->getBackupHistoryFiles();
        $this->assertCount(count($historyBefore) + 1, $historyAfter);
        $this->assertStringStartsWith('safeguard-', $historyAfter[array_key_last($historyAfter)]);
        $this->assertSame($backupRowsBefore + 1, DB::connection('sqlite')->table('database_backups')->count());
    }

    public function test_failed_export_does_not_create_new_backup_history(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.export-fail@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $historyBefore = $this->getBackupHistoryFiles();
        $backupRowsBefore = DB::connection('sqlite')->table('database_backups')->count();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql' => [
                'driver' => 'mysql',
                'host' => 'invalid-host-backup-test',
                'port' => 3306,
                'database' => 'backup_test_fail',
                'username' => 'root',
                'password' => '',
            ],
        ]);

        $response = $this->actingAs($user)
            ->from(route('settings.backup'))
            ->post(route('settings.backup.export'));

        $response->assertRedirect(route('settings.backup'));
        $response->assertSessionHas('error');
        $this->assertSame($historyBefore, $this->getBackupHistoryFiles());
        $this->assertSame($backupRowsBefore, DB::connection('sqlite')->table('database_backups')->count());
    }

    private function getBackupHistoryFiles(): array
    {
        return collect(glob($this->backupDirectory . DIRECTORY_SEPARATOR . '*/*.sql') ?: [])
            ->map(static fn (string $path): string => basename($path))
            ->sort()
            ->values()
            ->all();
    }

    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if (in_array($item, ['.', '..'], true)) {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
                continue;
            }

            @unlink($path);
        }

        @rmdir($directory);
    }
}
