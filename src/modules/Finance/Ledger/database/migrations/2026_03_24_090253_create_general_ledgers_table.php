<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel general_ledgers (Buku Besar).
 * Berisi detail mutasi setiap akun per baris transaksi secara kronologis.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('general_ledgers', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('branch_uuid')->constrained('branches', 'uuid')->cascadeOnDelete()->comment('ID Cabang');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik buku besar');
            $table->foreignUuid('account_uuid')->constrained('accounts', 'uuid')->cascadeOnDelete()->comment('ID Akun');
            $table->foreignUuid('journal_uuid')->constrained('journals', 'uuid')->cascadeOnDelete()->comment('ID Jurnal induk');
            $table->foreignUuid('journal_line_uuid')->constrained('journal_lines', 'uuid')->cascadeOnDelete()->comment('ID Baris jurnal asal');
            
            $table->bigInteger('journal_date')->comment('Tanggal efektif transaksi (Unix)');
            $table->string('reference_no')->comment('Nomor referensi dokumen');
            $table->string('description')->nullable()->comment('Keterangan baris transaksi');
            
            $table->decimal('debit', 18, 2)->default(0)->comment('Nilai mutasi debit');
            $table->decimal('credit', 18, 2)->default(0)->comment('Nilai mutasi kredit');
            $table->decimal('balance', 18, 2)->default(0)->comment('Saldo berjalan (running balance) setelah transaksi');
            $table->boolean('is_reconciled')->default(false)->comment('Flag sudah direkonsialisasi');
            $table->boolean('is_system')->default(false)->comment('Flag dibuat oleh sistem');
            
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->unique(['journal_line_uuid', 'deleted_at'], 'idx_gl_journal_line_unique_soft');
            // Performa Query
            $table->index(['account_uuid', 'branch_uuid', 'journal_date']);
            $table->index(['company_uuid', 'deleted_at'], 'idx_general_ledgers_company_deleted');
            $table->index('deleted_at', 'idx_general_ledgers_deleted_at');
            $table->index(['company_uuid', 'branch_uuid', 'deleted_at'], 'idx_general_ledgers_company_branch_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledgers');
    }
};
