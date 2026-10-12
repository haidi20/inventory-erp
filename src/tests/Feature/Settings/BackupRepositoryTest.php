<?php

namespace Tests\Feature\Settings;

use App\Modules\Core\Platform\Entities\DatabaseBackupEntity;
use App\Modules\Core\Platform\Entities\DatabaseRestoreEntity;
use App\Modules\Core\Platform\Repositories\BackupRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackupRepositoryTest extends TestCase
{
    private string $databasePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->databasePath = storage_path('framework/testing/sqlite-backup-test.sqlite');

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
            CREATE TABLE database_backups (
                uuid CHAR(36) PRIMARY KEY,
                id INTEGER,
                name VARCHAR(255),
                path VARCHAR(255),
                size BIGINT,
                status VARCHAR(50),
                source VARCHAR(50),
                history_type VARCHAR(50),
                notes TEXT,
                created_at BIGINT,
                updated_at BIGINT
            );
        ');

        DB::connection('sqlite')->unprepared('
            CREATE TABLE sample_backup_rows (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL
            );
        ');

        DB::connection('sqlite')->table('sample_backup_rows')->insert([
            'name' => 'Baris Contoh Backup',
        ]);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');

        if (file_exists($this->databasePath)) {
            @unlink($this->databasePath);
        }

        parent::tearDown();
    }

    public function test_repository_can_export_and_restore_sqlite_backup(): void
    {
        $repository = app(BackupRepository::class);

        $backup = $repository->createBackupExport(DatabaseBackupEntity::fromArray([]));

        $this->assertFileExists($backup['path']);
        $this->assertStringContainsString(now()->format('Y-m-d'), $backup['filename']);
        $this->assertMatchesRegularExpression(
            '/^backup-[A-Za-z0-9_-]+-\d{4}-\d{2}-\d{2}_\d{6}(?:-\d+)?\.sql$/',
            $backup['filename']
        );
        $this->assertStringContainsString('sample_backup_rows', file_get_contents($backup['path']) ?: '');

        $restoreFile = storage_path('framework/testing/sqlite-restore-test.sql');
        file_put_contents($restoreFile, <<<SQL
CREATE TABLE restore_probe (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    label TEXT NOT NULL
);
INSERT INTO restore_probe (label) VALUES ('Restore berhasil');
SQL);

        $repository->restoreBackup(DatabaseRestoreEntity::fromArray([
            'stored_file_path' => $restoreFile,
            'create_pre_restore_backup' => false,
        ]));

        $this->assertSame(1, DB::connection('sqlite')->table('restore_probe')->count());

        if (file_exists($backup['path'])) {
            @unlink($backup['path']);
        }
    }
}
