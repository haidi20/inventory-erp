<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel job impor data (staging/async).
 * Mencakup import_jobs (header proses impor) dan import_job_details (rincian baris per dokumen),
 * sesuai fitur Ambil Data Secara Paralel pada NineSoft.
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel header job impor data
            if (!Schema::hasTable('import_jobs')) {
                Schema::create('import_jobs', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik job impor');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik job impor');
                    $table->string('source_module', 50)->nullable()->comment('Modul sumber impor (sales, purchase, stok)');
                    $table->string('target_module', 50)->nullable()->comment('Modul tujuan impor');
                    $table->string('file_name', 255)->nullable()->comment('Nama file impor tersimpan');
                    $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->comment('Status proses job impor');
                    $table->unsignedInteger('total_rows')->default(0)->comment('Total jumlah baris data');
                    $table->unsignedInteger('processed_rows')->default(0)->comment('Jumlah baris yang sudah diproses');
                    $table->unsignedInteger('failed_rows')->default(0)->comment('Jumlah baris yang gagal diproses');
                    $table->text('error_message')->nullable()->comment('Pesan galat proses job');
                    $table->bigInteger('started_at')->nullable()->comment('Waktu job mulai diproses (Unix)');
                    $table->bigInteger('finished_at')->nullable()->comment('Waktu job selesai diproses (Unix)');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('company_uuid', 'idx_import_jobs_company_uuid');
                    $table->index('branch_uuid', 'idx_import_jobs_branch_uuid');
                    $table->index('target_module', 'idx_import_jobs_target_module');
                    $table->index('status', 'idx_import_jobs_status');
                    $table->index('deleted_at', 'idx_import_jobs_deleted_at');
                });
            }
            // Tabel rincian baris job impor per dokumen
            if (!Schema::hasTable('import_job_details')) {
                Schema::create('import_job_details', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('import_job_uuid', 36)->comment('UUID header job impor induk');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik data');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik data');
                    $table->string('document_code', 50)->nullable()->comment('Kode dokumen yang diimpor');
                    $table->enum('status', ['pending', 'success', 'failed'])->default('pending')->comment('Status impor dokumen baris');
                    $table->char('reference_uuid', 36)->nullable()->comment('UUID dokumen hasil impor');
                    $table->text('error_message')->nullable()->comment('Pesan galat impor baris');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('import_job_uuid', 'idx_import_job_details_job_uuid');
                    $table->index('company_uuid', 'idx_import_job_details_company_uuid');
                    $table->index('status', 'idx_import_job_details_status');
                    $table->index('deleted_at', 'idx_import_job_details_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('import_job_details');
            Schema::dropIfExists('import_jobs');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};
