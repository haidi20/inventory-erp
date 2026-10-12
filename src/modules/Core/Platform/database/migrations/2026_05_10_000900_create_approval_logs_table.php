<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel persetujuan / approval berjenjang.
 * Mencatat riwayat persetujuan dokumen (mis. anggaran atau pengajuan uang) yang multi-level.
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel log persetujuan dokumen
            if (!Schema::hasTable('approval_logs')) {
                Schema::create('approval_logs', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik log');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik log');
                    $table->string('reference_type', 50)->comment('Tipe dokumen yang disetujui');
                    $table->char('reference_uuid', 36)->comment('UUID dokumen yang disetujui');
                    $table->unsignedTinyInteger('level')->default(1)->comment('Tingkat persetujuan berjenjang');
                    $table->char('user_uuid', 36)->comment('UUID persetujuan');
                    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->comment('Status persetujuan pada level ini');
                    $table->text('note')->nullable()->comment('Catatan pemberi persetujuan');
                    $table->bigInteger('approved_at')->nullable()->comment('Waktu keputusan persetujuan (Unix)');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index(['reference_type', 'reference_uuid'], 'idx_approval_logs_reference');
                    $table->index('company_uuid', 'idx_approval_logs_company_uuid');
                    $table->index('user_uuid', 'idx_approval_logs_user_uuid');
                    $table->index('status', 'idx_approval_logs_status');
                    $table->index('deleted_at', 'idx_approval_logs_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('approval_logs');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};
