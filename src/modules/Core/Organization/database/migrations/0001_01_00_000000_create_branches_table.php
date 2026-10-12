<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel master cabang atau fasilitas kesehatan.
            if (!Schema::hasTable('branches')) {
                Schema::create('branches', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->string('name', 255)->comment('Nama lengkap cabang klinik atau faskes');
                    $table->string('short_name', 5)->comment('Kode singkat cabang untuk prefix transaksi');
                    $table->text('description')->nullable()->comment('Catatan tambahan untuk kebutuhan operasional cabang');
                    $table->boolean('is_active')->default(true)->comment('Status aktif cabang');
                    $table->char('company_uuid', 36)->nullable()->comment('Relasi ke perusahaan induk');

                    // System Fields (Standard Audit)
                    $table->bigInteger('created_at')->nullable();
                    $table->bigInteger('updated_at')->nullable();
                    $table->bigInteger('deleted_at')->unsigned()->default(0);
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');

                    $table->unique(['short_name', 'deleted_at'], 'idx_branches_short_name');
                    $table->index('company_uuid', 'idx_branches_company_uuid');
                    $table->index('deleted_at', 'idx_branches_deleted_at');
                });

                return;
            }

            // Jika tabel sudah ada, tambahkan kolom yang belum tersedia agar setara dengan skema BE.
            Schema::table('branches', function (Blueprint $table): void {
                if (!Schema::hasColumn('branches', 'description')) {
                    $table->text('description')->nullable()->comment('Catatan tambahan untuk kebutuhan operasional cabang');
                }
                if (!Schema::hasColumn('branches', 'company_uuid')) {
                    $table->char('company_uuid', 36)->nullable()->comment('Relasi ke perusahaan induk');
                }
            });

            $this->ensureIndex('branches', 'idx_branches_company_uuid', ['company_uuid']);
            $this->ensureIndex('branches', 'idx_branches_deleted_at', ['deleted_at']);
            $this->ensureIndex('branches', 'idx_branches_created_by', ['created_by']);
            $this->ensureIndex('branches', 'idx_branches_updated_by', ['updated_by']);
            $this->ensureIndex('branches', 'idx_branches_deleted_by', ['deleted_by']);
            $this->ensureUniqueIndex('branches', 'idx_branches_short_name', ['short_name', 'deleted_at']);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            // No-op: tidak menurunkan tabel branches agar data dasar cabang tetap aman.
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    private function ensureIndex(string $table, string $indexName, array $columns): void
    {
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }
        if ($this->indexExists($table, $indexName)) {
            return;
        }

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

    private function ensureUniqueIndex(string $table, string $indexName, array $columns): void
    {
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement(
                'ALTER TABLE ' . $this->quoteMysqlIdentifier($table)
                . ' ADD UNIQUE INDEX ' . $this->quoteMysqlIdentifier($indexName)
                . ' (' . $this->quoteMysqlIdentifierList($columns) . ')'
            );
            return;
        }

        if ($driver === 'pgsql' || $driver === 'sqlite') {
            DB::statement(
                'CREATE UNIQUE INDEX IF NOT EXISTS ' . $this->quoteAnsiIdentifier($indexName)
                . ' ON ' . $this->quoteAnsiIdentifier($table)
                . ' (' . $this->quoteAnsiIdentifierList($columns) . ')'
            );
        }
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
            return (int) ($row->aggregate_count ?? 0) > 0;
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
            return (int) ($row->aggregate_count ?? 0) > 0;
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
};


