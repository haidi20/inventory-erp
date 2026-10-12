<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel log pengiriman pesan WhatsApp.
 * Menyimpan riwayat pesan yang dikirim beserta status dan respons provider.
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel log pengiriman pesan WhatsApp
            if (!Schema::hasTable('whatsapp_logs')) {
                Schema::create('whatsapp_logs', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik log');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik log');
                    $table->char('user_uuid', 36)->nullable()->comment('UUID staf pengirim pesan');
                    $table->char('person_uuid', 36)->nullable()->comment('UUID customer/supplier tujuan pesan');
                    $table->char('whatsapp_template_uuid', 36)->nullable()->comment('UUID template pesan yang dipakai');
                    $table->string('phone', 30)->comment('Nomor WhatsApp tujuan');
                    $table->text('message')->comment('Isi pesan yang dikirimkan');
                    $table->string('provider', 30)->nullable()->comment('Penyedia layanan WhatsApp (mis. fonnte, wablas)');
                    $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending')->comment('Status pengiriman pesan');
                    $table->string('reference_type', 50)->nullable()->comment('Tipe dokumen referensi (tagihan, sales, dll)');
                    $table->char('reference_uuid', 36)->nullable()->comment('UUID dokumen referensi terkait');
                    $table->json('response_payload')->nullable()->comment('Respons payload dari provider');
                    $table->bigInteger('sent_at')->nullable()->comment('Waktu pesan terkirim (Unix)');
                    $table->bigInteger('delivered_at')->nullable()->comment('Waktu pesan terbaca tujuan (Unix)');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('company_uuid', 'idx_wa_logs_company_uuid');
                    $table->index('branch_uuid', 'idx_wa_logs_branch_uuid');
                    $table->index('phone', 'idx_wa_logs_phone');
                    $table->index('person_uuid', 'idx_wa_logs_person_uuid');
                    $table->index('status', 'idx_wa_logs_status');
                    $table->index(['reference_type', 'reference_uuid'], 'idx_wa_logs_reference');
                    $table->index('deleted_at', 'idx_wa_logs_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('whatsapp_logs');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};

