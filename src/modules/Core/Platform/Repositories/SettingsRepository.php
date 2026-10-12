<?php

namespace App\Modules\Core\Platform\Repositories;

use App\Modules\Core\Organization\Models\Company;
use Illuminate\Support\Facades\DB;
use App\Modules\Core\Platform\Entities\DatabaseBackupDownloadEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupEntity;
use App\Modules\Core\Platform\Entities\DatabaseBackupHistoryTableEntity;
use App\Modules\Core\Platform\Entities\DatabaseRestoreEntity;
use Illuminate\Support\Facades\File;
use App\Modules\Clinical\Classification\Models\Icd10Code;
use App\Modules\Core\Platform\Entities\Icd10Entity;
use App\Modules\Clinical\Integration\Entities\SatusehatSettingsEntity;
use App\Modules\Clinical\Integration\Models\SatusehatSetting;
use App\Modules\Core\Platform\Entities\SettingsEntity;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * SettingsRepository
 * 
 * Implementasi repository untuk manajemen pengaturan sistem.
 * Menangani profil organisasi, integrasi SATUSEHAT, dan katalog diagnosa medis (ICD-10),
 * serta fitur manajemen database (backup/restore).
 */
class SettingsRepository implements SettingsRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getCompanyData(): array
    {
        $company = Company::query()->orderBy('id', 'ASC')->first();
        return $company ? $company->toArray() : ['name' => 'Klinik Demo'];
    }

    /**
     * @inheritDoc
     */
    public function getSatusehatSettings(): array
    {
        $settings = DB::table('satusehat_settings')->orderBy('id', 'ASC')->first();
        return $settings ? (array) $settings : [];
    }

    /**
     * @inheritDoc
     */
    public function saveSatusehatSettings(SatusehatSettingsEntity $entity): void
    {
        DB::beginTransaction();
        try {
            $data = [
                'org_uuid' => $entity->org_uuid,
                'client_uuid' => $entity->client_uuid,
                'client_secret' => $entity->client_secret,
                'base_url' => $entity->base_url,
                'auth_url' => $entity->auth_url,
                'is_active' => $entity->is_active,
                'updated_at' => time(),
            ];

            $exists = DB::table('satusehat_settings')->where('id', 1)->exists();

            if ($exists) {
                DB::table('satusehat_settings')->where('id', 1)->update($data);
            } else {
                $data['id'] = 1;
                $data['uuid'] = (string) \Illuminate\Support\Str::uuid();
                $data['created_at'] = time();
                DB::table('satusehat_settings')->insert($data);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function getIcd10PageData(Icd10Entity $entity): array
    {
        $query = DB::table('icd10_codes')->orderBy('code', 'ASC');

        if (!empty($entity->search)) {
            $s = strtolower($entity->search);
            $query->where(function ($q) use ($s) {
                $q->whereRaw("LOWER(code) LIKE ?", ["%{$s}%"])
                  ->orWhereRaw("LOWER(name_uuid) LIKE ?", ["%{$s}%"]);
            });
        }

        $paginator = $query->paginate($entity->per_page ?? 10, ['*'], 'page', $entity->page ?? 1);

        return [
            'items' => $paginator->items(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function storeIcd10(Icd10Entity $entity): void
    {
        DB::beginTransaction();
        try {
            DB::table('icd10_codes')->insert([
                'code' => trim($entity->code),
                'name_uuid' => trim($entity->name_uuid),
                'name_en' => trim($entity->name_en),
                'category' => $entity->category,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteIcd10(int $id, string $actorUuid): void
    {
        DB::beginTransaction();
        try {
            DB::table('icd10_codes')->where('id', $id)->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function getDatabaseManagementData(): array
    {
        $connection = (string) config('database.default');
        $databaseName = (string) (config("database.connections.{$connection}.database") ?? 'unknown');

        $totalSizeSql = "SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = ?";
        $size = DB::selectOne($totalSizeSql, [$databaseName]);

        return [
            'database_name' => $databaseName,
            'database_version' => $this->getDatabaseVersion(),
            'total_size' => (float) ($size?->size ?? 0),
            'backup_count' => DB::table('database_backups')->count(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getRecentBackupFiles(): array
    {
        $backups = DB::table('database_backups')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get();

        return $backups->map(fn ($b) => (array) $b)->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getBackupHistoryDatatable(DatabaseBackupHistoryTableEntity $entity): array
    {
        $query = DB::table('database_backups')->where('history_type', $entity->history_type);

        if (!empty($entity->search)) {
            $s = "%{$entity->search}%";
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', $s)
                  ->orWhere('notes', 'LIKE', $s);
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
            'data' => $data->map(fn ($b) => (array) $b)->toArray(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function exportDatabase(DatabaseBackupEntity $entity): array
    {
        $filename = 'backup-' . config('app.name') . '-' . now()->format('Y-m-d_His') . '.sql';
        $directory = storage_path('app/backups/manual');

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        // Fallback manual dump to ensure reliability without external dependencies
        $this->exportMysqlDatabaseViaConnection($path);

        $size = filesize($path);
        
        DB::table('database_backups')->insert([
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

        return [
            'path' => $path,
            'filename' => $filename,
        ];
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function restoreDatabase(DatabaseRestoreEntity $entity): void
    {
        $backupPath = $entity->stored_file_path;

        if (!file_exists($backupPath)) {
            throw new RuntimeException('File restore tidak ditemukan.');
        }

        $connectionName = (string) config('database.default');
        $connection = config("database.connections.{$connectionName}");
        $databaseName = (string) (config("database.connections.{$connectionName}.database") ?? '');

        if ($databaseName === '') {
            throw new RuntimeException('Tidak bisa mengidentifikasi database target restore.');
        }

        // 1. Create safeguard backup if requested
        if ($entity->create_pre_restore_backup) {
            $this->createRestoreSafeguard($databaseName);
        }

        // 2. Perform restore
        $this->executeRestoreSql($backupPath, $connection, $databaseName);

        // 3. Verify restore
        $this->verifyMysqlDatabaseAccessible($databaseName, $connection);
    }

    private function getDatabaseVersion(): string
    {
        try {
            return (string) (DB::selectOne('SELECT VERSION() AS version')?->version ?? 'Unknown');
        } catch (\Throwable) {
            return 'Unknown';
        }
    }

    private function createRestoreSafeguard(string $databaseName): void
    {
        $filename = 'safeguard-' . $databaseName . '-' . now()->format('Y-m-d_His') . '.sql';
        $directory = storage_path('app/backups/system');

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        $this->exportMysqlDatabaseViaConnection($path);

        DB::table('database_backups')->insert([
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
    }

    private function executeRestoreSql(string $sourcePath, array $connection, string $databaseName): void
    {
        $pdo = $this->createMysqlPdo($connection, $databaseName);

        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

            foreach ($this->readMysqlStatements($sourcePath) as $statement) {
                $pdo->exec($statement);
            }
        } catch (\PDOException $pdoException) {
            throw new RuntimeException('Restore SQL gagal dijalankan: ' . $pdoException->getMessage(), 0, $pdoException);
        } finally {
            try {
                $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Throwable) {
            }
        }
    }

    private function verifyMysqlDatabaseAccessible(string $databaseName, array $connection): void
    {
        try {
            $pdo = $this->createMysqlPdo($connection, $databaseName);
            $activeDatabase = (string) ($pdo->query('SELECT DATABASE()')->fetchColumn() ?: '');

            if ($activeDatabase === '' || strcasecmp($activeDatabase, $databaseName) !== 0) {
                throw new RuntimeException('Verifikasi restore MySQL gagal dijalankan: database aktif tidak sesuai dengan target restore.');
            }
        } catch (\PDOException $pdoException) {
            throw new RuntimeException('Verifikasi restore MySQL gagal dijalankan: ' . $pdoException->getMessage(), 0, $pdoException);
        }
    }

    private function exportMysqlDatabaseViaConnection(string $targetPath): void
    {
        $connectionName = (string) config('database.default');
        $connection = DB::connection($connectionName);
        $databaseName = (string) ($connection->getDatabaseName() ?: '');

        if ($databaseName === '') {
            throw new RuntimeException('Nama database aktif tidak ditemukan. Export SQL belum bisa dijalankan.');
        }

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
}

