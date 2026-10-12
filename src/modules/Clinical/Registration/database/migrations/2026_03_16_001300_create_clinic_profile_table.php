<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel clinic_profile (Profil Institusi Faskes).
 * Berisi data legalitas dan alamat resmi klinik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_profile', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('name')->comment('Nama resmi klinik/faskes');
            $table->string('clinic_type')->default('klinik pratama')->comment('Jenis faskes (Pratama, Utama, dll)');
            $table->string('registration_number')->nullable()->comment('Nomor izin operasional resmi');
            $table->string('phone')->nullable()->comment('Nomor telepon faskes');
            $table->string('email')->nullable()->comment('Email resmi faskes');
            $table->text('address')->nullable()->comment('Alamat lengkap faskes');
            $table->string('province')->nullable()->comment('Provinsi');
            $table->string('city')->nullable()->comment('Kota/Kabupaten');
            $table->string('district')->nullable()->comment('Kecamatan');
            $table->string('postal_code')->nullable()->comment('Kode Pos');
            $table->string('website')->nullable()->comment('URL Website faskes');
            $table->string('satusehat_org_uuid')->nullable()->comment('ID Organisasi resmi dari portal SATUSEHAT');
            $table->bigInteger('synced_at')->nullable()->comment('Waktu sinkronisasi profil terakhir (Unix)');
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable(); $table->bigInteger('updated_at')->nullable();
        });

        // Insert default row
        \DB::table('clinic_profile')->insert([
            'uuid'       => \Illuminate\Support\Str::uuid(),
            'name'       => 'Klinik Saya',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_profile');
    }
};

