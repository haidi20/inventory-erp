<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel agenda / events kalender.
 * Mencatat jadwal operasional seperti jadwal praktik dokter dan hari libur klinik.
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel agenda kalender
            if (!Schema::hasTable('calendar_events')) {
                Schema::create('calendar_events', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik agenda');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik agenda');
                    $table->string('title', 255)->comment('Judul agenda');
                    $table->text('description')->nullable()->comment('Uraian agenda');
                    $table->enum('event_type', ['doctor_schedule', 'holiday', 'meeting', 'reminder', 'other'])->default('other')->comment('Tipe agenda');
                    $table->string('weekday', 20)->nullable()->comment('Hari berulang (Monday...Sunday)');
                    $table->char('user_uuid', 36)->nullable()->comment('UUID dokter/staf terkait agenda');
                    $table->bigInteger('event_date')->nullable()->comment('Tanggal agenda dalam Unix timestamp mikrodetik (µs)');
                    $table->bigInteger('start_time')->nullable()->comment('Jam mulai dalam Unix timestamp mikrodetik (µs)');
                    $table->bigInteger('end_time')->nullable()->comment('Jam selesai dalam Unix timestamp mikrodetik (µs)');
                    $table->boolean('is_repeatable')->default(false)->comment('Penanda agenda berulang mingguan');
                    $table->boolean('is_active')->default(true)->comment('Status aktif agenda');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('company_uuid', 'idx_calendar_events_company_uuid');
                    $table->index('branch_uuid', 'idx_calendar_events_branch_uuid');
                    $table->index('user_uuid', 'idx_calendar_events_user_uuid');
                    $table->index(['event_type', 'is_active'], 'idx_calendar_events_type_active');
                    $table->index('event_date', 'idx_calendar_events_event_date');
                    $table->index('deleted_at', 'idx_calendar_events_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('calendar_events');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};

