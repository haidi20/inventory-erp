<?php

namespace Tests\Feature\Settings;

use App\Modules\Core\Platform\Services\BackupService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackupPresentationTest extends TestCase
{
    private string $backupDirectory;
    private string $backupPath;
    private string $backupMetaPath;

    protected function setUp(): void
    {
        parent::setUp();
        // Indonesian timezone (Asia/Makassar = WITA, UTC+8) so file mtimes and
        // their formatted labels both stay in the same zone for assertion equality.
        // Must run AFTER parent::setUp() because Laravel bootstrap resets the timezone.
        config(['app.timezone' => 'Asia/Makassar']);
        date_default_timezone_set('Asia/Makassar');
        \Illuminate\Support\Carbon::setTestNow(null);

        app('view')->addLocation(base_path('modules/Core/Platform/resources/views'));

        $this->backupDirectory = storage_path('app/backups');
        if (!is_dir($this->backupDirectory)) {
            mkdir($this->backupDirectory, 0777, true);
        }

        $this->backupPath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'backup-presentasi-uji.sql';
        $this->backupMetaPath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'backup-presentasi-uji.sql.meta.json';
        file_put_contents($this->backupPath, str_repeat('A', 1572864));
        file_put_contents($this->backupMetaPath, json_encode([
            'source' => 'user',
            'status' => 'ready',
        ], JSON_PRETTY_PRINT));
        touch($this->backupPath, strtotime('2026-04-03 17:46:00'));

        DB::connection('sqlite')->table('database_backups')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'backup-presentasi-uji.sql',
            'path' => 'manual/backup-presentasi-uji.sql',
            'size' => 1572864,
            'status' => 'ready',
            'source' => 'user',
            'history_type' => 'manual',
            'created_at' => strtotime('2026-04-03 17:46:00'),
            'updated_at' => strtotime('2026-04-03 17:46:00'),
        ]);

        $legacyRestorePath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'backup-restore-lama.sql';
        $legacyRestoreMetaPath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'backup-restore-lama.sql.meta.json';
        file_put_contents($legacyRestorePath, str_repeat('B', 1048576));
        file_put_contents($legacyRestoreMetaPath, json_encode([
            'source' => 'system',
            'status' => 'restore_failed_safeguard',
        ], JSON_PRETTY_PRINT));
        touch($legacyRestorePath, strtotime('2026-04-02 18:59:00'));
    }

    protected function tearDown(): void
    {
        if (is_file($this->backupPath)) {
            @unlink($this->backupPath);
        }

        if (is_file($this->backupMetaPath)) {
            @unlink($this->backupMetaPath);
        }

        $legacyRestorePath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'backup-restore-lama.sql';
        $legacyRestoreMetaPath = $this->backupDirectory . DIRECTORY_SEPARATOR . 'backup-restore-lama.sql.meta.json';

        if (is_file($legacyRestorePath)) {
            @unlink($legacyRestorePath);
        }

        if (is_file($legacyRestoreMetaPath)) {
            @unlink($legacyRestoreMetaPath);
        }

        parent::tearDown();
    }

    public function test_database_page_data_formats_backup_labels_for_indonesian_users(): void
    {
        // Indonesian timezone (Asia/Makassar = WITA, UTC+8) so the formatted label matches.
        config(['app.timezone' => 'Asia/Makassar']);
        date_default_timezone_set('Asia/Makassar');

        $service = app(BackupService::class);

        $payload = $service->index();
        $backup = collect($payload['recentBackups'])->firstWhere('name', 'backup-presentasi-uji.sql');

        $this->assertNotNull($backup);
        $this->assertSame('3 April 2026, 17:46', $backup['updated_at_label']);
        $this->assertSame('1,5 MB', $backup['size_label']);
        $this->assertSame('Dibuat oleh Pengguna', $backup['source_label']);
        $this->assertSame('Backup siap digunakan', $backup['status_label']);
    }

    public function test_restore_safeguard_files_are_not_shown_in_backup_history(): void
    {
        $service = app(BackupService::class);

        $payload = $service->index();
        $backup = collect($payload['recentBackups'])->firstWhere('name', 'backup-restore-lama.sql');

        $this->assertNull($backup);
    }

    public function test_database_page_renders_manual_and_system_history_tabs(): void
    {
        // View Blade settings.database sudah diganti komponen Inertia
        // (Core/Platform/presentations/views/backupIndex.vue); test kini
        // meng-assert payload service yang dikirim ke halaman tersebut.
        $service = app(BackupService::class);

        $payload = $service->index();

        $this->assertArrayHasKey('database', $payload);
        $this->assertArrayHasKey('recentBackups', $payload);
        $this->assertIsArray($payload['recentBackups']);
        $this->assertNotEmpty($payload['recentBackups']);
    }
}
