<?php

namespace Tests\Feature\Settings;

use App\Modules\Core\Identity\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackupHistoryDatatableTest extends TestCase
{
    use WithoutMiddleware;

    private string $databasePath;
    private string $backupDirectory;

    public function refreshDatabase(): void
    {
        // Test ini membangun database SQLite manual agar skenario backup tetap terisolasi.
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->databasePath = storage_path('framework/testing/sqlite-backup-history-datatable.sqlite');
        $this->backupDirectory = storage_path('app/backups');

        $this->deleteDirectory($this->backupDirectory);

        if (file_exists($this->databasePath)) {
            @unlink($this->databasePath);
        }

        if (!is_dir(dirname($this->databasePath))) {
            mkdir(dirname($this->databasePath), 0777, true);
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

        $manualDirectory = $this->backupDirectory . DIRECTORY_SEPARATOR . 'manual';
        $systemDirectory = $this->backupDirectory . DIRECTORY_SEPARATOR . 'system';
        mkdir($manualDirectory, 0777, true);
        mkdir($systemDirectory, 0777, true);

        $manualBackupPath = $manualDirectory . DIRECTORY_SEPARATOR . 'backup-manual-utama.sql';
        file_put_contents($manualBackupPath, str_repeat('A', 1048576));

        $manualBackupSecondPath = $manualDirectory . DIRECTORY_SEPARATOR . 'backup-manual-arsip.sql';
        file_put_contents($manualBackupSecondPath, str_repeat('B', 524288));

        $systemBackupPath = $systemDirectory . DIRECTORY_SEPARATOR . 'backup-system-restore.sql';
        file_put_contents($systemBackupPath, str_repeat('C', 262144));

        DB::connection('sqlite')->table('database_backups')->insert([
            [
                'name' => 'backup-manual-utama.sql',
                'path' => 'manual/backup-manual-utama.sql',
                'size' => 1048576,
                'status' => 'ready',
                'source' => 'user',
                'history_type' => 'manual',
                'notes' => 'Backup manual utama',
                'created_at' => strtotime('2026-04-02 19:10:00'),
                'updated_at' => strtotime('2026-04-02 19:10:00'),
            ],
            [
                'name' => 'backup-manual-arsip.sql',
                'path' => 'manual/backup-manual-arsip.sql',
                'size' => 524288,
                'status' => 'ready',
                'source' => 'user',
                'history_type' => 'manual',
                'notes' => 'Backup manual arsip',
                'created_at' => strtotime('2026-04-01 10:30:00'),
                'updated_at' => strtotime('2026-04-01 10:30:00'),
            ],
            [
                'name' => 'backup-system-restore.sql',
                'path' => 'system/backup-system-restore.sql',
                'size' => 262144,
                'status' => 'restore_failed_safeguard',
                'source' => 'system',
                'history_type' => 'system',
                'notes' => 'Backup safeguard restore',
                'created_at' => strtotime('2026-04-02 20:15:00'),
                'updated_at' => strtotime('2026-04-02 20:15:00'),
            ],
        ]);
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

    public function test_manual_backup_datatable_returns_only_manual_history_rows(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.backup-table@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->get(route('settings.backup.history.datatable', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'history_type' => 'manual',
            ]));

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 2);
        $response->assertJsonPath('recordsFiltered', 2);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.history_type', 'manual');
        $response->assertJsonPath('data.0.name', 'backup-manual-utama.sql');
    }

    public function test_system_backup_datatable_returns_only_restore_safeguard_rows(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.backup-system-table@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->get(route('settings.backup.history.datatable', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
                'history_type' => 'system',
            ]));

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 1);
        $response->assertJsonPath('recordsFiltered', 1);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.history_type', 'system');
        $response->assertJsonPath('data.0.status', 'restore_failed_safeguard');
    }

    public function test_backup_datatable_can_search_file_name_server_side(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.backup-search@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->get(route('settings.backup.history.datatable', [
                'draw' => 2,
                'start' => 0,
                'length' => 10,
                'history_type' => 'manual',
                'search' => [
                    'value' => 'utama',
                ],
            ]));

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 2);
        $response->assertJsonPath('recordsFiltered', 1);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'backup-manual-utama.sql');
    }

    public function test_backup_datatable_accepts_browser_shape_search_payload_without_value(): void
    {
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin.backup-browser-shape@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->get(route('settings.backup.history.datatable', [
                'draw' => 3,
                'start' => 0,
                'length' => 10,
                'history_type' => 'manual',
                'search' => [
                    'regex' => 'false',
                ],
                'order' => [
                    ['column' => 3, 'dir' => 'desc'],
                ],
            ]));

        $response->assertOk();
        $response->assertJsonPath('recordsTotal', 2);
        $response->assertJsonPath('recordsFiltered', 2);
        $response->assertJsonCount(2, 'data');
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
