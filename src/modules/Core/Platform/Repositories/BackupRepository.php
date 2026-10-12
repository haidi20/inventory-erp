<?php

namespace App\Modules\Core\Platform\Repositories;

use App\Modules\Core\Platform\Repositories\BackupRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Modules\Core\Platform\Entities\DatabaseBackupDownloadEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupHistoryTableEntity;
use App\Modules\Core\Platform\Entities\DatabaseRestoreEntity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class BackupRepository implements BackupRepositoryInterface
{
    public function getBackupManagementData(): array
    {
        $connection = (string) config('database.default');
        $databaseName = $this->resolveDatabaseName($connection);

        return [
            'database_name' => $databaseName,
            'database_version' => $this->getDatabaseVersion(),
            'total_size' => $this->getDatabaseSize($connection),
            'backup_count' => DB::table('database_backups')->count(),
        ];
    }

    public function getRecentBackups(): array
    {
        $backups = DB::table('database_backups')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        return $backups->map(fn ($backup) => (array) $backup)->toArray();
    }

    public function getBackupHistoryDatatable(DatabaseBackupHistoryTableEntity $entity): array
    {
        $query = DB::table('database_backups')->where('history_type', $entity->history_type);

        if (!empty($entity->search)) {
            $search = "%{$entity->search}%";
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'LIKE', $search)
                    ->orWhere('notes', 'LIKE', $search);
            });
        }

        $total = $query->count();

        $direction = $entity->order_direction ?? 'desc';
        $column = match ($entity->order_column) {
            1 => 'name',
            2 => 'size',
            3 => 'source',
            4 => 'status',
            default => 'created_at',
        };

        $data = $query->orderBy($column, $direction)
            ->offset($entity->start ?? 0)
            ->limit($entity->length ?? 10)
            ->get();

        return [
            'recordsTotal' => DB::table('database_backups')->where('history_type', $entity->history_type)->count(),
            'recordsFiltered' => $total,
            'data' => $data->map(fn ($backup) => (array) $backup)->toArray(),
        ];
    }

    public function createBackupExport(DatabaseBackupEntity $entity): array
    {
        $filename = 'backup-' . config('app.name') . '-' . now()->format('Y-m-d_His') . '.sql';
        $directory = storage_path('app/backups/manual');

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        $this->exportDatabaseViaConnection($path);

        $size = filesize($path);

        $this->insertBackupHistory([
            'name' => $filename,
            'path' => 'manual/' . $filename,
            'size' => $size,
            'status' => 'ready',
            'source' => 'system',
            'history_type' => 'manual',
            'notes' => $entity->notes,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        file_put_contents($path . '.meta.json', json_encode([
            'source' => 'system',
            'status' => 'ready',
        ], JSON_PRETTY_PRINT));

        return [
            'path' => $path,
            'filename' => $filename,
        ];
    }

    public function resolveBackupDownload(DatabaseBackupDownloadEntity $entity): array
    {
        $backup = DB::table('database_backups')
            ->where('name', $entity->filename)
            ->where('history_type', $entity->history_type)
            ->first();

        if (!$backup) {
            throw new RuntimeException('Data riwayat backup tidak ditemukan di database.');
        }

        $fullPath = storage_path('app/backups/' . $backup->path);

        if (!file_exists($fullPath)) {
            throw new RuntimeException('File fisik backup tidak ditemukan di storage: ' . $backup->path);
        }

        return [
            'path' => $fullPath,
            'filename' => $backup->name,
        ];
    }

    public function restoreBackup(DatabaseRestoreEntity $entity): void
    {
        $backupPath = $entity->stored_file_path;

        if (!file_exists($backupPath)) {
            throw new RuntimeException('File restore tidak ditemukan.');
        }

        $connectionName = (string) config('database.default');
        $connection = (array) config("database.connections.{$connectionName}", []);
        $databaseName = $this->resolveDatabaseName($connectionName);
        $driver = (string) ($connection['driver'] ?? '');

        if ($databaseName === '') {
            throw new RuntimeException('Tidak bisa mengidentifikasi database target restore.');
        }

        if ($entity->create_pre_restore_backup) {
            $this->createRestoreSafeguard();
        }

        $extension = pathinfo($backupPath, PATHINFO_EXTENSION);

        DB::disconnect($connectionName);

        if (strtolower($extension) === 'zip') {
            $this->restoreFromZipArchive($backupPath, $connection, $databaseName);
        } else {
            $this->executeRestoreSql($backupPath, $connection, $databaseName);
        }

        DB::purge($connectionName);
        DB::reconnect($connectionName);

        $this->verifyDatabaseAccessible($databaseName, $connection, $driver);
    }

    private function restoreFromZipArchive(string $zipPath, array $connection, string $databaseName): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Gagal membuka file arsip ZIP.');
        }

        $tempExtractPath = storage_path('app/backups/tmp/extract_' . uniqid());
        if (!is_dir($tempExtractPath)) {
            mkdir($tempExtractPath, 0777, true);
        }

        $zip->extractTo($tempExtractPath);
        $zip->close();

        // 1. Restore Database (.sql)
        $sqlFiles = glob($tempExtractPath . '/*.sql');
        if (empty($sqlFiles)) {
            // Coba cari rekursif jika tidak ada di root
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tempExtractPath));
            foreach ($iterator as $file) {
                if ($file->isFile() && strtolower($file->getExtension()) === 'sql') {
                    $sqlFiles[] = $file->getRealPath();
                }
            }
        }

        if (!empty($sqlFiles)) {
            // Ambil file SQL pertama (biasanya hanya satu)
            $this->executeRestoreSql($sqlFiles[0], $connection, $databaseName);
        }

        // 2. Restore Assets (images, docs, etc)
        $publicStoragePath = storage_path('app/public');
        
        // Pindahkan folder yang umum digunakan untuk aset jika ada
        $assetFolders = ['uploads', 'images', 'documents', 'assets', 'public'];
        foreach ($assetFolders as $folder) {
            $folderPath = $tempExtractPath . DIRECTORY_SEPARATOR . $folder;
            if (is_dir($folderPath)) {
                $this->copyDirectory($folderPath, $publicStoragePath . DIRECTORY_SEPARATOR . $folder);
            }
        }

        // Juga pindahkan file non-SQL apa pun yang ada di root (kecuali metadata jika ada)
        $rootFiles = scandir($tempExtractPath);
        foreach ($rootFiles as $file) {
            if (in_array($file, ['.', '..'], true)) continue;
            
            $filePath = $tempExtractPath . DIRECTORY_SEPARATOR . $file;
            if (is_file($filePath) && strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'sql') {
                copy($filePath, $publicStoragePath . DIRECTORY_SEPARATOR . $file);
            }
        }

        // Cleanup
        $this->deleteDirectory($tempExtractPath);
    }

    private function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0777, true);
                }
            } else {
                copy($item->getRealPath(), $target);
            }
        }
    }

    private function getDatabaseVersion(): string
    {
        try {
            $driver = (string) config('database.connections.' . config('database.default') . '.driver');

            if ($driver === 'sqlite') {
                return (string) (DB::selectOne('SELECT sqlite_version() AS version')?->version ?? 'SQLite');
            }

            return (string) (DB::selectOne('SELECT VERSION() AS version')?->version ?? 'Unknown');
        } catch (\Throwable) {
            return 'Unknown';
        }
    }

    private function createRestoreSafeguard(): void
    {
        $databaseName = $this->resolveDatabaseName((string) config('database.default'));
        $filename = 'safeguard-' . $databaseName . '-' . now()->format('Y-m-d_His') . '.sql';
        $directory = storage_path('app/backups/system');

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        $this->exportDatabaseViaConnection($path);

        $this->insertBackupHistory([
            'name' => $filename,
            'path' => 'system/' . $filename,
            'size' => filesize($path),
            'status' => 'restore_safeguard',
            'source' => 'system',
            'history_type' => 'system',
            'notes' => 'Backup otomatis sebelum operasi restore.',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        file_put_contents($path . '.meta.json', json_encode([
            'source' => 'system',
            'status' => 'restore_safeguard',
        ], JSON_PRETTY_PRINT));
    }

    private function executeRestoreSql(string $sourcePath, array $connection, string $databaseName): void
    {
        $driver = (string) ($connection['driver'] ?? '');
        $pdo = $this->createPdo($connection, $databaseName);

        try {
            $this->disableForeignKeys($pdo, $driver);

            foreach ($this->readMysqlStatements($sourcePath) as $statement) {
                $pdo->exec($statement);
            }
        } catch (\PDOException $exception) {
            throw new RuntimeException('Restore SQL gagal dijalankan: ' . $exception->getMessage(), 0, $exception);
        } finally {
            try {
                $this->enableForeignKeys($pdo, $driver);
            } catch (\Throwable) {
            }
        }
    }

    private function verifyDatabaseAccessible(string $databaseName, array $connection, string $driver): void
    {
        try {
            $pdo = $this->createPdo($connection, $databaseName);

            if ($driver === 'sqlite') {
                $pdo->query('SELECT name FROM sqlite_master LIMIT 1')->fetchColumn();
                return;
            }

            $activeDatabase = (string) ($pdo->query('SELECT DATABASE()')->fetchColumn() ?: '');

            if ($activeDatabase === '' || strcasecmp($activeDatabase, $databaseName) !== 0) {
                throw new RuntimeException('Verifikasi restore database gagal dijalankan: database aktif tidak sesuai dengan target restore.');
            }
        } catch (\PDOException $exception) {
            throw new RuntimeException('Verifikasi restore database gagal dijalankan: ' . $exception->getMessage(), 0, $exception);
        }
    }

    private function exportDatabaseViaConnection(string $targetPath): void
    {
        $connectionName = (string) config('database.default');
        $connection = DB::connection($connectionName);
        $databaseName = $this->resolveDatabaseName($connectionName);
        $driver = (string) config("database.connections.{$connectionName}.driver");

        \Illuminate\Support\Facades\Log::info("DEBUG: exportDatabaseViaConnection $connectionName ($driver) -> $databaseName to $targetPath");

        if ($databaseName === '') {
            throw new RuntimeException('Nama database aktif tidak ditemukan. Export SQL belum bisa dijalankan.');
        }

        if ($driver === 'sqlite') {
            $this->exportSqliteDatabaseViaConnection($connectionName, $targetPath, $databaseName);
            return;
        }

        $this->exportMysqlDatabaseViaConnection($connectionName, $targetPath, $databaseName);
    }

    private function exportMysqlDatabaseViaConnection(string $connectionName, string $targetPath, string $databaseName): void
    {
        try {
            $connection = DB::connection($connectionName);
            $pdo = $connection->getPdo();
            $lines = [
                '-- Backup database generated by application fallback',
                '-- Database: ' . $databaseName,
                '-- Generated at: ' . now()->format('Y-m-d H:i:s'),
                'SET FOREIGN_KEY_CHECKS=0;',
                '',
            ];

            $tables = $connection->select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');

            foreach ($tables as $tableRow) {
                $table = (string) array_values((array) $tableRow)[0];
                $quotedTable = str_replace('`', '``', $table);

                $createTable = $connection->selectOne('SHOW CREATE TABLE `' . $quotedTable . '`');
                $createTableValues = array_values((array) $createTable);
                $createStatement = (string) ($createTableValues[1] ?? '');

                $lines[] = 'DROP TABLE IF EXISTS `' . $quotedTable . '`;';
                $lines[] = $createStatement . ';';
                $lines[] = '';

                $rows = $connection->table($table)->get();
                foreach ($rows as $row) {
                    $rowData = (array) $row;
                    $columns = array_map(
                        static fn (string $column): string => '`' . str_replace('`', '``', $column) . '`',
                        array_keys($rowData)
                    );
                    $values = array_map(
                        static fn ($value): string => self::toSqlLiteral($pdo, $value),
                        array_values($rowData)
                    );

                    $lines[] = sprintf(
                        'INSERT INTO `%s` (%s) VALUES (%s);',
                        $quotedTable,
                        implode(', ', $columns),
                        implode(', ', $values)
                    );
                }

                $lines[] = '';
            }

            $views = $connection->select('SHOW FULL TABLES WHERE Table_type = "VIEW"');
            foreach ($views as $viewRow) {
                $view = (string) array_values((array) $viewRow)[0];
                $quotedView = str_replace('`', '``', $view);

                $createView = $connection->selectOne('SHOW CREATE VIEW `' . $quotedView . '`');
                $createViewValues = array_values((array) $createView);
                $createStatement = (string) ($createViewValues[1] ?? '');

                $lines[] = 'DROP VIEW IF EXISTS `' . $quotedView . '`;';
                $lines[] = $createStatement . ';';
                $lines[] = '';
            }

            $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
            $lines[] = '';

            file_put_contents($targetPath, implode(PHP_EOL, $lines));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Backup Export Failed: ' . $e->getMessage(), [
                'connection' => $connectionName,
                'target' => $targetPath,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function exportSqliteDatabaseViaConnection(string $connectionName, string $targetPath, string $databaseName): void
    {
        $connection = DB::connection($connectionName);
        $pdo = $connection->getPdo();
        $lines = [
            '-- Backup database generated by application fallback',
            '-- Database: ' . $databaseName,
            '-- Generated at: ' . now()->format('Y-m-d H:i:s'),
            'PRAGMA foreign_keys=OFF;',
            'BEGIN TRANSACTION;',
            '',
        ];

        $tables = $connection->select("
            SELECT name, sql
            FROM sqlite_master
            WHERE type = 'table'
              AND name NOT LIKE 'sqlite_%'
            ORDER BY name
        ");

        foreach ($tables as $tableRow) {
            $table = (string) $tableRow->name;
            $createStatement = (string) ($tableRow->sql ?? '');

            if ($createStatement === '') {
                continue;
            }

            $quotedTable = str_replace('`', '``', $table);
            $lines[] = 'DROP TABLE IF EXISTS `' . $quotedTable . '`;';
            $lines[] = $createStatement . ';';
            $lines[] = '';

            $rows = $connection->table($table)->get();
            foreach ($rows as $row) {
                $rowData = (array) $row;
                $columns = array_map(
                    static fn (string $column): string => '`' . str_replace('`', '``', $column) . '`',
                    array_keys($rowData)
                );
                $values = array_map(
                    static fn ($value): string => self::toSqlLiteral($pdo, $value),
                    array_values($rowData)
                );

                $lines[] = sprintf(
                    'INSERT INTO `%s` (%s) VALUES (%s);',
                    $quotedTable,
                    implode(', ', $columns),
                    implode(', ', $values)
                );
            }

            $lines[] = '';
        }

        $indexes = $connection->select("
            SELECT name, sql
            FROM sqlite_master
            WHERE type = 'index'
              AND name NOT LIKE 'sqlite_%'
              AND sql IS NOT NULL
            ORDER BY name
        ");

        foreach ($indexes as $index) {
            $statement = (string) ($index->sql ?? '');
            if ($statement !== '') {
                $lines[] = $statement . ';';
            }
        }

        $lines[] = '';
        $lines[] = 'COMMIT;';
        $lines[] = 'PRAGMA foreign_keys=ON;';
        $lines[] = '';

        file_put_contents($targetPath, implode(PHP_EOL, $lines));
    }

    private static function toSqlLiteral(\PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }

    private function createPdo(array $connection, ?string $databaseName = null): \PDO
    {
        $driver = (string) ($connection['driver'] ?? '');

        if ($driver === 'sqlite') {
            return new \PDO(
                'sqlite:' . (string) ($connection['database'] ?? $databaseName ?? ''),
                null,
                null,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        }

        return $this->createMysqlPdo($connection, $databaseName);
    }

    private function createMysqlPdo(array $connection, ?string $databaseName = null): \PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;charset=%s%s',
            (string) ($connection['host'] ?? '127.0.0.1'),
            (string) ($connection['port'] ?? '3306'),
            (string) ($connection['charset'] ?? 'utf8mb4'),
            $databaseName ? ';dbname=' . $databaseName : ''
        );

        return new \PDO(
            $dsn,
            (string) ($connection['username'] ?? 'root'),
            (string) ($connection['password'] ?? ''),
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    private function readMysqlStatements(string $sourcePath): \Generator
    {
        if (!is_file($sourcePath)) {
            throw new RuntimeException('File SQL untuk restore tidak ditemukan.');
        }

        $file = new \SplFileObject($sourcePath, 'r');
        $delimiter = ';';
        $buffer = '';

        while (!$file->eof()) {
            $line = $file->fgets();
            if ($line === false) {
                break;
            }

            if ($buffer === '' && str_starts_with($line, "\xEF\xBB\xBF")) {
                $line = substr($line, 3);
            }

            $trimmedLine = trim($line);

            if ($trimmedLine === '' && $buffer === '') {
                continue;
            }

            if ($buffer === '' && $this->isIgnorableMysqlComment($trimmedLine)) {
                continue;
            }

            if (preg_match('/^DELIMITER\s+(.+)$/i', $trimmedLine, $matches) === 1) {
                $delimiter = trim($matches[1]);
                continue;
            }

            $buffer .= $line;

            if (!$this->statementEndsWithDelimiter($buffer, $delimiter)) {
                continue;
            }

            $statement = $this->trimTrailingDelimiter($buffer, $delimiter);
            $buffer = '';

            if (trim($statement) !== '') {
                yield $statement;
            }
        }

        if (trim($buffer) !== '') {
            yield trim($buffer);
        }
    }

    private function disableForeignKeys(\PDO $pdo, string $driver): void
    {
        if ($driver === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = OFF');
            return;
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    }

    private function enableForeignKeys(\PDO $pdo, string $driver): void
    {
        if ($driver === 'sqlite') {
            $pdo->exec('PRAGMA foreign_keys = ON');
            return;
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    private function resolveDatabaseName(string $connectionName): string
    {
        $connection = (array) config("database.connections.{$connectionName}", []);
        $driver = (string) ($connection['driver'] ?? '');

        if ($driver === 'sqlite') {
            return basename((string) ($connection['database'] ?? 'sqlite'));
        }

        return (string) ($connection['database'] ?? 'unknown');
    }

    private function getDatabaseSize(string $connectionName): float
    {
        $connection = (array) config("database.connections.{$connectionName}", []);
        $driver = (string) ($connection['driver'] ?? '');

        if ($driver === 'sqlite') {
            $databasePath = (string) ($connection['database'] ?? '');
            return is_file($databasePath) ? (float) filesize($databasePath) : 0.0;
        }

        $databaseName = (string) ($connection['database'] ?? 'unknown');
        $totalSizeSql = "SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = ?";
        $size = DB::selectOne($totalSizeSql, [$databaseName]);

        return (float) ($size?->size ?? 0);
    }

    private function isIgnorableMysqlComment(string $line): bool
    {
        return str_starts_with($line, '-- ') || str_starts_with($line, '#');
    }

    private function statementEndsWithDelimiter(string $buffer, string $delimiter): bool
    {
        if ($delimiter === '') {
            return false;
        }

        return str_ends_with(rtrim($buffer), $delimiter);
    }

    private function trimTrailingDelimiter(string $statement, string $delimiter): string
    {
        $trimmedStatement = rtrim($statement);

        if ($delimiter !== '' && str_ends_with($trimmedStatement, $delimiter)) {
            $trimmedStatement = substr($trimmedStatement, 0, -strlen($delimiter));
        }

        return trim($trimmedStatement);
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

    private function insertBackupHistory(array $payload): void
    {
        DB::beginTransaction();
        try {
            $connectionName = (string) config('database.default');
            $columns = Schema::connection($connectionName)->getColumnListing('database_backups');

            $basePayload = [
                'uuid' => (string) Str::uuid(),
                'deleted_at' => 0,
            ] + $payload;

            $filteredPayload = array_intersect_key($basePayload, array_flip($columns));

            DB::connection($connectionName)->table('database_backups')->insert($filteredPayload);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
