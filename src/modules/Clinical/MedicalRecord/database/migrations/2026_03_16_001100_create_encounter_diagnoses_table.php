<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel encounter_diagnoses (Diagnosis Medis).
 * Berbasis standar FHIR Condition dan merujuk pada kamus ICD-10.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounter_diagnoses', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID cabang diagnosis');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik diagnosis');
            $table->uuid('encounter_uuid')->comment('UUID Pemeriksaan medis terkait');
            $table->foreign('encounter_uuid')->references('uuid')->on('encounters')->cascadeOnDelete();
            // FHIR Condition / Observation - Diagnosis
            $table->string('icd10_code', 10)->nullable()->comment('Kode ICD-10 yang ditegakkan');
            $table->string('icd10_name')->nullable()->comment('Nama diagnosis standar ICD-10');
            $table->enum('type', ['primary', 'secondary'])->default('primary')->comment('Status diagnosis: primer atau sekunder');
            $table->text('symptoms')->nullable()->comment('Keluhan subyektif pasien');
            $table->text('assessment_notes')->nullable()->comment('Catatan asesmen/pertimbangan medis');
            $table->enum('status', ['active', 'resolved', 'inactive'])->default('active')->comment('Status kondisi: aktif, sembuh, atau tidak aktif');
            // SATUSEHAT sync status
            $table->string('satusehat_uuid')->nullable()->comment('ID resource resmi di portal SATUSEHAT');
            $table->bigInteger('synced_at')->nullable()->comment('Waktu sinkronisasi SATUSEHAT terakhir (Unix)');
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable(); $table->bigInteger('updated_at')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_encounter_diagnoses_company_deleted');

            $table->foreign('icd10_code')->references('code')->on('icd10_codes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounter_diagnoses');
    }
};

