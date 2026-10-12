<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel rekonsiliasi bank.
 * Mencakup tabel bank_reconciliations (header rekonsiliasi) dan bank_reconciliation_details
 * (mutasi fisik yang dicocokkan), sesuai legacy rekonsiliasi_bank + rekonsiliasi_bank_d_fisik.
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel header rekonsiliasi bank
            if (!Schema::hasTable('bank_reconciliations')) {
                Schema::create('bank_reconciliations', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik rekonsiliasi');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik rekonsiliasi');
                    $table->char('bank_account_uuid', 36)->comment('UUID akun bank yang direkonsiliasi');
                    $table->string('document_no', 40)->nullable()->comment('Nomor dokumen rekonsiliasi');
                    $table->bigInteger('document_date')->nullable()->comment('Tanggal rekonsiliasi (Unix)');
                    $table->integer('period_name')->nullable()->comment('Periode akuntansi terdampak YYYYMM');
                    $table->decimal('statement_balance', 17, 5)->default(0)->comment('Saldo menurut rekening koran');
                    $table->decimal('book_balance', 17, 5)->default(0)->comment('Saldo menurut buku besar');
                    $table->decimal('adjusted_balance', 17, 5)->default(0)->comment('Saldo setelah penyesuaian');
                    $table->enum('status', ['open', 'closed'])->default('open')->comment('Status rekonsiliasi');
                    $table->text('note')->nullable()->comment('Catatan rekonsiliasi');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('company_uuid', 'idx_bank_reconciliations_company_uuid');
                    $table->index('bank_account_uuid', 'idx_bank_reconciliations_bank_account_uuid');
                    $table->index('period_name', 'idx_bank_reconciliations_period_name');
                    $table->index('deleted_at', 'idx_bank_reconciliations_deleted_at');
                });
            }
            // Tabel rincian fisik rekonsiliasi bank
            if (!Schema::hasTable('bank_reconciliation_details')) {
                Schema::create('bank_reconciliation_details', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('bank_reconciliation_uuid', 36)->comment('UUID header rekonsiliasi induk');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik data');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik data');
                    $table->char('bank_account_uuid', 36)->nullable()->comment('UUID akun bank mutasi');
                    $table->bigInteger('document_date')->nullable()->comment('Tanggal mutasi fisik (Unix)');
                    $table->integer('period_name')->nullable()->comment('Periode mutasi YYYYMM');
                    $table->decimal('debit', 17, 5)->default(0)->comment('Nilai mutasi debet');
                    $table->decimal('credit', 17, 5)->default(0)->comment('Nilai mutasi kredit');
                    $table->text('note')->nullable()->comment('Keterangan mutasi fisik');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('bank_reconciliation_uuid', 'idx_bank_rec_details_reconciliation_uuid');
                    $table->index('company_uuid', 'idx_bank_rec_details_company_uuid');
                    $table->index('deleted_at', 'idx_bank_rec_details_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('bank_reconciliation_details');
            Schema::dropIfExists('bank_reconciliations');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};

