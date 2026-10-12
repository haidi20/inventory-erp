<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel anggaran (RAB) dan rincian akunnya.
 * Mencakup budgets (header anggaran) dan budget_lines (rincian per akun COA),
 * sesuai fitur Anggaran (RAB) pada NineSoft.
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel header anggaran
            if (!Schema::hasTable('budgets')) {
                Schema::create('budgets', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik anggaran');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik anggaran');
                    $table->string('code', 50)->comment('Kode anggaran');
                    $table->string('name', 255)->comment('Nama anggaran');
                    $table->integer('fiscal_year')->comment('Tahun fiscal anggaran');
                    $table->integer('start_period')->nullable()->comment('Periode awal YYYYMM');
                    $table->integer('end_period')->nullable()->comment('Periode akhir YYYYMM');
                    $table->decimal('total_amount', 20, 2)->default(0)->comment('Total nilai anggaran');
                    $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft')->comment('Status persetujuan anggaran');
                    $table->text('description')->nullable()->comment('Uraian anggaran');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['company_uuid', 'code', 'deleted_at'], 'idx_budgets_code_unique_soft');
                    $table->index('company_uuid', 'idx_budgets_company_uuid');
                    $table->index('branch_uuid', 'idx_budgets_branch_uuid');
                    $table->index('fiscal_year', 'idx_budgets_fiscal_year');
                    $table->index('status', 'idx_budgets_status');
                    $table->index('deleted_at', 'idx_budgets_deleted_at');
                });
            }
            // Tabel rincian anggaran berbasis akun COA
            if (!Schema::hasTable('budget_lines')) {
                Schema::create('budget_lines', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('budget_uuid', 36)->comment('UUID header anggaran induk');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik data');
                    $table->char('coa_uuid', 36)->comment('UUID akun COA yang dianggarkan');
                    $table->integer('year')->nullable()->comment('Tahun realisasi garis');
                    $table->integer('month')->nullable()->comment('Bulan realisasi garis');
                    $table->decimal('amount', 20, 2)->default(0)->comment('Nilai anggaran baris');
                    $table->decimal('realized_amount', 20, 2)->default(0)->comment('Nilai realisasi anggaran');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('budget_uuid', 'idx_budget_lines_budget_uuid');
                    $table->index('coa_uuid', 'idx_budget_lines_coa_uuid');
                    $table->index(['year', 'month'], 'idx_budget_lines_period');
                    $table->index('deleted_at', 'idx_budget_lines_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('budget_lines');
            Schema::dropIfExists('budgets');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};

