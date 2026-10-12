<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel encounter_vitals (Tanda-tanda Vital).
 * Berdasarkan standar FHIR Observation - Vital Signs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounter_vitals', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID cabang pemeriksaan vital');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik pemeriksaan vital');
            $table->uuid('encounter_uuid')->comment('UUID Pemeriksaan medis terkait');
            $table->foreign('encounter_uuid')->references('uuid')->on('encounters')->cascadeOnDelete();
            $table->decimal('weight_kg', 5, 2)->nullable()->comment('Berat badan pasien (Kg)');
            $table->decimal('height_cm', 5, 2)->nullable()->comment('Tinggi badan pasien (Cm)');
            $table->integer('respiratory_rate')->nullable()->comment('Laju pernapasan (kali per menit)');
            $table->integer('heart_rate')->nullable()->comment('Denyut nadi (kali per menit)');
            $table->decimal('spo2', 5, 2)->nullable()->comment('Saturasi oksigen darah (%)');
            $table->decimal('systolic_bp', 5, 1)->nullable()->comment('Tekanan darah sistolik (mmHg)');
            $table->decimal('diastolic_bp', 5, 1)->nullable()->comment('Tekanan darah diastolik (mmHg)');
            $table->decimal('temperature_c', 4, 1)->nullable()->comment('Suhu tubuh (Â°C)');
            $table->string('satusehat_uuid')->nullable()->comment('ID resource resmi di portal SATUSEHAT');
            $table->bigInteger('synced_at')->nullable()->comment('Waktu sinkronisasi SATUSEHAT terakhir (Unix)');
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable(); $table->bigInteger('updated_at')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_encounter_vitals_company_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounter_vitals');
    }
};

