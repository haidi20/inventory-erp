<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel template cetakan.
 * Mencakup template faktur, surat jalan, PO, dan dokumen lain dengan layout/content JSON
 * (legacy: erp_rpt + erp_rpt_detail + erp_rpt_item).
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel template cetakan dokumen
            if (!Schema::hasTable('print_templates')) {
                Schema::create('print_templates', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik template');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik template');
                    $table->string('code', 50)->comment('Kode template cetakan');
                    $table->string('menu', 50)->nullable()->comment('Menu terkait template (mis. sales, purchase)');
                    $table->string('module', 50)->nullable()->comment('Modul terkait template');
                    $table->string('name', 255)->comment('Nama template cetakan');
                    $table->enum('paper_size', ['a4', 'a5', 'thermal', 'kecil', 'besar'])->default('a4')->comment('Ukuran kertas cetakan');
                    $table->json('layout')->nullable()->comment('Konfigurasi layout grid designer (JSON)');
                    $table->longText('content')->nullable()->comment('Konten template dengan placeholder dinamis');
                    $table->boolean('is_default')->default(false)->comment('Penanda template default untuk menu tersebut');
                    $table->boolean('is_active')->default(true)->comment('Status aktif template');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['company_uuid', 'code', 'deleted_at'], 'idx_print_templates_code_unique_soft');
                    $table->index('company_uuid', 'idx_print_templates_company_uuid');
                    $table->index('branch_uuid', 'idx_print_templates_branch_uuid');
                    $table->index('menu', 'idx_print_templates_menu');
                    $table->index('deleted_at', 'idx_print_templates_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('print_templates');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};

