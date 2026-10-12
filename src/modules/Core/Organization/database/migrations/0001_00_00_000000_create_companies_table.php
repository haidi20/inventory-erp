<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel profil fasilitas kesehatan/perusahaan
            if (!Schema::hasTable('companies')) {
                Schema::create('companies', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->string('name', 255)->comment('Nama resmi perusahaan/faskes');
                    $table->string('clinic_type', 255)->nullable()->default('klinik pratama')->comment('Jenis faskes (Pratama, Utama, dll)');
                    $table->string('registration_number', 255)->nullable()->comment('Nomor izin operasional resmi');
                    $table->string('phone', 255)->nullable()->comment('Nomor telepon faskes');
                    $table->string('email', 255)->nullable()->comment('Email resmi faskes');
                    $table->text('address')->nullable()->comment('Alamat lengkap faskes');
                    $table->string('province', 255)->nullable()->comment('Provinsi');
                    $table->string('city', 255)->nullable()->comment('Kota/Kabupaten');
                    $table->string('district', 255)->nullable()->comment('Kecamatan');
                    $table->string('postal_code', 255)->nullable()->comment('Kode Pos');
                    $table->string('website', 255)->nullable()->comment('URL Website faskes');
                    $table->string('timezone', 64)
                        ->default('Asia/Makassar')
                        ->comment('Zona waktu operasional perusahaan (WITA/GMT+8, termasuk Samarinda)');
                    $table->string('bank_name', 255)->nullable()->comment('Nama Bank faskes');
                    $table->string('bank_account', 255)->nullable()->comment('Nomor Rekening faskes');
                    $table->string('bank_holder', 255)->nullable()->comment('Atas Nama Rekening faskes');
                    $table->string('satusehat_org_uuid', 255)->nullable()->comment('ID Organisasi resmi dari portal SATUSEHAT');
                    $table->bigInteger('synced_at')->nullable()->comment('Waktu sinkronisasi profil terakhir');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Status aktif perusahaan');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['name', 'deleted_at'], 'idx_companies_name');
                    $table->index('deleted_at', 'idx_companies_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('companies');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};

