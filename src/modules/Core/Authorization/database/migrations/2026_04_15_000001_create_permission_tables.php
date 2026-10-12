<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom bisnis yang wajib dioptimasi.
     *
     * @var array<int, string>
     */
    private array $businessIndexColumns = [
        'document_no',
        'invoice_no',
        'code',
        'name',
        'status',
        'email',
    ];

    /**
     * Pola composite index standar.
     *
     * @var array<int, array<int, string>>
     */
    private array $compositeIndexPatterns = [
        ['branch_uuid', 'deleted_at'],
        ['company_uuid', 'deleted_at'],
        ['branch_uuid', 'status'],
        ['branch_uuid', 'invoice_no'],
        ['branch_uuid', 'document_no'],
        ['branch_uuid', 'invoice_date'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_uuid';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_uuid';

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['permissions'], static function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->string('label')->nullable();
            $table->string('guard_name');
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();

            $table->unique(['name', 'guard_name', 'deleted_at'], 'idx_permissions_unique_soft');
            $table->index('deleted_at', 'idx_permissions_deleted_at');
        });

        Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
            $table->uuid('uuid')->primary();
            if ($teams || config('permission.testing')) {
                $table->uuid($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');
            $table->string('label')->nullable();
            $table->string('guard_name');
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name', 'deleted_at'], 'idx_roles_team_unique_soft');
            } else {
                $table->unique(['name', 'guard_name', 'deleted_at'], 'idx_roles_unique_soft');
            }
            $table->index('deleted_at', 'idx_roles_deleted_at');
        });

        Schema::create($tableNames['model_has_permissions'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->uuid($pivotPermission);

            $table->string('model_type');
            $table->uuid($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_uuid_model_type_index');

            $table->foreign($pivotPermission)
                ->references('uuid')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }
        });

        Schema::create($tableNames['model_has_roles'], static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->uuid($pivotRole);

            $table->string('model_type');
            $table->uuid($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_uuid_model_type_index');

            $table->foreign($pivotRole)
                ->references('uuid')
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], static function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->uuid($pivotPermission);
            $table->uuid($pivotRole);

            $table->foreign($pivotPermission)
                ->references('uuid')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign($pivotRole)
                ->references('uuid')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_uuid_role_uuid_primary');
        });

        /*
        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
        */

        // Backport optimasi index dari patch migration ke migration create table.
        $this->applyLegacyIndexOptimization();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        $this->rollbackLegacyIndexOptimization();

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }

    /**
     * Terapkan optimasi index untuk seluruh tabel v1.
     */
    private function applyLegacyIndexOptimization(): void
    {
        foreach ($this->listExistingTables() as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            if ($columns === []) {
                continue;
            }

            $columnMap = array_flip($columns);

            foreach ($columns as $column) {
                $isUuidRelation = str_ends_with($column, '_uuid');
                $isBusinessColumn = in_array($column, $this->businessIndexColumns, true);

                if (!$isUuidRelation && !$isBusinessColumn) {
                    continue;
                }

                if ($this->hasLeadingColumnIndex($table, $column)) {
                    continue;
                }

                $indexName = $this->buildIndexName($table, [$column]);
                if ($this->indexExists($table, $indexName)) {
                    continue;
                }

                $this->createIndex($table, $indexName, [$column]);
            }

            foreach ($this->compositeIndexPatterns as $combo) {
                $allColumnsExist = true;
                foreach ($combo as $col) {
                    if (!isset($columnMap[$col])) {
                        $allColumnsExist = false;
                        break;
                    }
                }

                if (!$allColumnsExist) {
                    continue;
                }

                $indexName = $this->buildIndexName($table, $combo);
                if ($this->indexExists($table, $indexName)) {
                    continue;
                }

                if ($this->hasExactMultiColumnIndex($table, $combo)) {
                    continue;
                }

                $this->createIndex($table, $indexName, $combo);
            }
        }
    }

    /**
     * Rollback index yang dibuat oleh applyLegacyIndexOptimization().
     */
    private function rollbackLegacyIndexOptimization(): void
    {
        foreach ($this->listExistingTables() as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            if ($columns === []) {
                continue;
            }

            $columnMap = array_flip($columns);

            foreach ($this->compositeIndexPatterns as $combo) {
                $allColumnsExist = true;
                foreach ($combo as $col) {
                    if (!isset($columnMap[$col])) {
                        $allColumnsExist = false;
                        break;
                    }
                }

                if (!$allColumnsExist) {
                    continue;
                }

                $indexName = $this->buildIndexName($table, $combo);
                if ($this->indexExists($table, $indexName)) {
                    $this->dropIndex($table, $indexName);
                }
            }

            foreach ($columns as $column) {
                $isUuidRelation = str_ends_with($column, '_uuid');
                $isBusinessColumn = in_array($column, $this->businessIndexColumns, true);

                if (!$isUuidRelation && !$isBusinessColumn) {
                    continue;
                }

                $indexName = $this->buildIndexName($table, [$column]);
                if ($this->indexExists($table, $indexName)) {
                    $this->dropIndex($table, $indexName);
                }
            }
        }
    }

    /**
     * Ambil daftar tabel existing dari DB aktif.
     *
     * @return array<int, string>
     */
    private function listExistingTables(): array
    {
        $driver = DB::getDriverName();
        $tables = [];

        if ($driver === 'mysql') {
            $rows = DB::select(
                '
                SELECT table_name
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                  AND table_type = "BASE TABLE"
                ORDER BY table_name
                '
            );

            foreach ($rows as $row) {
                $name = (string) $this->readRowField($row, 'table_name', '');
                if ($name !== '') {
                    $tables[] = $name;
                }
            }
        } elseif ($driver === 'pgsql') {
            $rows = DB::select(
                '
                SELECT tablename AS table_name
                FROM pg_tables
                WHERE schemaname = current_schema()
                ORDER BY tablename
                '
            );

            foreach ($rows as $row) {
                $name = (string) $this->readRowField($row, 'table_name', '');
                if ($name !== '') {
                    $tables[] = $name;
                }
            }
        } elseif ($driver === 'sqlite') {
            $rows = DB::select(
                "
                SELECT name AS table_name
                FROM sqlite_master
                WHERE type = 'table'
                  AND name NOT LIKE 'sqlite_%'
                ORDER BY name
                "
            );

            foreach ($rows as $row) {
                $name = (string) $this->readRowField($row, 'table_name', '');
                if ($name !== '') {
                    $tables[] = $name;
                }
            }
        }

        return array_values(array_filter(array_unique($tables), static fn (string $table): bool => $table !== 'migrations'));
    }

    private function buildIndexName(string $table, array $columns): string
    {
        $name = 'idx_' . $table . '_' . implode('_', $columns);
        if (strlen($name) > 64) {
            $name = substr($name, 0, 60) . substr(md5($name), 0, 4);
        }
        return $name;
    }

    /**
     * Cek apakah ada index dengan kolom EXACT (urutan sama).
     */
    private function hasExactMultiColumnIndex(string $table, array $columns): bool
    {
        $driver = DB::getDriverName();
        $target = implode(',', $columns);

        if ($driver === 'mysql') {
            $rows = DB::select(
                '
                SELECT index_name, column_name, seq_in_index
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = ?
                ORDER BY index_name, seq_in_index
                ',
                [$table]
            );
            $grouped = [];
            foreach ($rows as $row) {
                $idxName = (string) $this->readRowField($row, 'index_name', '');
                $colName = (string) $this->readRowField($row, 'column_name', '');
                if ($idxName === '' || $colName === '') {
                    continue;
                }
                $grouped[$idxName][] = $colName;
            }
            foreach ($grouped as $cols) {
                if (implode(',', $cols) === $target) {
                    return true;
                }
            }
            return false;
        }

        if ($driver === 'pgsql') {
            $rows = DB::select(
                "
                SELECT i.relname AS index_name, a.attname AS column_name,
                       array_position(ix.indkey, a.attnum) AS seq
                FROM pg_class t
                JOIN pg_index ix ON t.oid = ix.indrelid
                JOIN pg_class i ON i.oid = ix.indexrelid
                JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(ix.indkey)
                WHERE t.relname = ?
                ORDER BY index_name, seq
                ",
                [$table]
            );
            $grouped = [];
            foreach ($rows as $row) {
                $idxName = (string) $this->readRowField($row, 'index_name', '');
                $colName = (string) $this->readRowField($row, 'column_name', '');
                if ($idxName === '' || $colName === '') {
                    continue;
                }
                $grouped[$idxName][] = $colName;
            }
            foreach ($grouped as $cols) {
                if (implode(',', $cols) === $target) {
                    return true;
                }
            }
            return false;
        }

        if ($driver === 'sqlite') {
            $tableLiteral = $this->quoteSqliteString($table);
            $indexes = DB::select("PRAGMA index_list({$tableLiteral})");
            foreach ($indexes as $idx) {
                $idxName = (string) ($idx->name ?? '');
                $idxNameLiteral = $this->quoteSqliteString($idxName);
                $cols = DB::select("PRAGMA index_info({$idxNameLiteral})");
                $columnsList = [];
                foreach ($cols as $c) {
                    $columnsList[(int) ($c->seqno ?? 0)] = (string) ($c->name ?? '');
                }
                ksort($columnsList);
                if (implode(',', $columnsList) === $target) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    private function hasLeadingColumnIndex(string $table, string $column): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $rows = DB::select(
                '
                SELECT index_name, column_name, seq_in_index
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = ?
                ORDER BY index_name, seq_in_index
                ',
                [$table]
            );
            foreach ($rows as $row) {
                if ((int) $this->readRowField($row, 'seq_in_index', 0) === 1
                    && (string) $this->readRowField($row, 'column_name', '') === $column) {
                    return true;
                }
            }
            return false;
        }

        if ($driver === 'pgsql') {
            $rows = DB::select(
                "
                SELECT i.relname AS index_name, a.attname AS column_name,
                       array_position(ix.indkey, a.attnum) AS seq
                FROM pg_class t
                JOIN pg_index ix ON t.oid = ix.indrelid
                JOIN pg_class i ON i.oid = ix.indexrelid
                JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(ix.indkey)
                WHERE t.relname = ?
                ",
                [$table]
            );
            foreach ($rows as $row) {
                if ((int) $this->readRowField($row, 'seq', -1) === 1
                    && (string) $this->readRowField($row, 'column_name', '') === $column) {
                    return true;
                }
            }
            return false;
        }

        if ($driver === 'sqlite') {
            $tableLiteral = $this->quoteSqliteString($table);
            $indexes = DB::select("PRAGMA index_list({$tableLiteral})");
            foreach ($indexes as $idx) {
                $idxName = (string) ($idx->name ?? '');
                $idxNameLiteral = $this->quoteSqliteString($idxName);
                $cols = DB::select("PRAGMA index_info({$idxNameLiteral})");
                foreach ($cols as $c) {
                    if ((int) ($c->seqno ?? -1) === 0
                        && (string) ($c->name ?? '') === $column) {
                        return true;
                    }
                }
            }
            return false;
        }

        return false;
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $row = DB::selectOne(
                '
                SELECT COUNT(*) AS aggregate_count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = ?
                  AND index_name = ?
                ',
                [$table, $indexName]
            );
            return (int) $this->readRowField($row, 'aggregate_count', 0) > 0;
        }

        if ($driver === 'pgsql') {
            $row = DB::selectOne(
                '
                SELECT COUNT(*) AS aggregate_count
                FROM pg_indexes
                WHERE schemaname = current_schema()
                  AND tablename = ?
                  AND indexname = ?
                ',
                [$table, $indexName]
            );
            return (int) $this->readRowField($row, 'aggregate_count', 0) > 0;
        }

        if ($driver === 'sqlite') {
            $tableLiteral = $this->quoteSqliteString($table);
            $rows = DB::select("PRAGMA index_list({$tableLiteral})");
            foreach ($rows as $row) {
                if ((string) ($row->name ?? '') === $indexName) {
                    return true;
                }
            }
        }

        return false;
    }

    private function createIndex(string $table, string $indexName, array $columns): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement(
                'CREATE INDEX ' . $this->quoteMysqlIdentifier($indexName)
                . ' ON ' . $this->quoteMysqlIdentifier($table)
                . ' (' . $this->quoteMysqlIdentifierList($columns) . ')'
            );
            return;
        }

        if ($driver === 'pgsql' || $driver === 'sqlite') {
            DB::statement(
                'CREATE INDEX IF NOT EXISTS ' . $this->quoteAnsiIdentifier($indexName)
                . ' ON ' . $this->quoteAnsiIdentifier($table)
                . ' (' . $this->quoteAnsiIdentifierList($columns) . ')'
            );
        }
    }

    private function dropIndex(string $table, string $indexName): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement(
                'DROP INDEX ' . $this->quoteMysqlIdentifier($indexName)
                . ' ON ' . $this->quoteMysqlIdentifier($table)
            );
            return;
        }

        if ($driver === 'pgsql' || $driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS ' . $this->quoteAnsiIdentifier($indexName));
        }
    }

    private function quoteMysqlIdentifier(string $value): string
    {
        return '`' . str_replace('`', '``', $value) . '`';
    }

    private function quoteAnsiIdentifier(string $value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
    }

    private function quoteMysqlIdentifierList(array $values): string
    {
        $quoted = [];
        foreach ($values as $value) {
            $quoted[] = $this->quoteMysqlIdentifier((string) $value);
        }
        return implode(', ', $quoted);
    }

    private function quoteAnsiIdentifierList(array $values): string
    {
        $quoted = [];
        foreach ($values as $value) {
            $quoted[] = $this->quoteAnsiIdentifier((string) $value);
        }
        return implode(', ', $quoted);
    }

    private function quoteSqliteString(string $value): string
    {
        return "'" . str_replace("'", "''", $value) . "'";
    }

    /**
     * Ambil field object hasil query DB secara case-insensitive.
     *
     * @param mixed $row
     * @param mixed $default
     * @return mixed
     */
    private function readRowField(mixed $row, string $field, mixed $default = null): mixed
    {
        if (!is_object($row)) {
            return $default;
        }

        $data = (array) $row;
        if ($data === []) {
            return $default;
        }

        foreach ($data as $key => $value) {
            if (strcasecmp((string) $key, $field) === 0) {
                return $value;
            }
        }

        return $default;
    }
};

