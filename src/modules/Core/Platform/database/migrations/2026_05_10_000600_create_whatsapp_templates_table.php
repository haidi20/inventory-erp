<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel template pesan WhatsApp.
 * Sesuai fitur Info Whatsapp pada NineSoft (notifikasi tagihan, terima kasih, dsb).
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel template pesan WhatsApp
            if (!Schema::hasTable('whatsapp_templates')) {
                Schema::create('whatsapp_templates', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik template');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik template');
                    $table->string('code', 50)->comment('Kode template pesan');
                    $table->string('name', 255)->comment('Nama template pesan');
                    $table->string('module', 50)->nullable()->comment('Modul terkait template (sales, finance, dll)');
                    $table->text('body')->comment('Isi pesan dengan placeholder dinamis');
                    $table->json('variables')->nullable()->comment('Daftar variabel placeholder yang tersedia');
                    $table->boolean('is_active')->default(true)->comment('Status aktif template');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['company_uuid', 'code', 'deleted_at'], 'idx_wa_templates_code_unique_soft');
                    $table->index('company_uuid', 'idx_wa_templates_company_uuid');
                    $table->index('deleted_at', 'idx_wa_templates_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('whatsapp_templates');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};

