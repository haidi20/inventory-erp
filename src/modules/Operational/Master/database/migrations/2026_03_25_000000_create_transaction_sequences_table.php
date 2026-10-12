<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel transaction_sequences.
 * Digunakan untuk mengelola penomoran otomatis dokumen (auto-numbering) per cabang dan periode.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_sequences', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('branch_uuid')->constrained('branches', 'uuid')->onDelete('cascade')->comment('ID Cabang');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik sequence transaksi');
            $table->string('type_code', 10)->comment('Kode tipe transaksi (e.g. INV, JRN)');
            $table->string('period', 6)->comment('Periode tahun-bulan (YYYYMM)');
            $table->unsignedInteger('last_index')->default(0)->comment('Indeks terakhir yang digunakan');
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable(); $table->bigInteger('updated_at')->nullable();

            $table->unique(['branch_uuid', 'type_code', 'period'], 'idx_branch_type_period');
            $table->index(['company_uuid', 'deleted_at'], 'idx_transaction_sequences_company_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_sequences');
    }
};

