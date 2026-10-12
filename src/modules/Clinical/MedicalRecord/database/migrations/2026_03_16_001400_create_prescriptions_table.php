<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel prescriptions (Resep Obat).
 * Berbasis standar FHIR MedicationRequest dan terintegrasi dengan SATUSEHAT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->nullable()->comment('UUID cabang pemilik resep');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik resep');
            $table->uuid('encounter_uuid')->comment('UUID Pemeriksaan medis terkait');
            $table->foreign('encounter_uuid')->references('uuid')->on('encounters')->cascadeOnDelete();
            $table->uuid('patient_uuid')->comment('UUID Pasien');
            $table->foreign('patient_uuid')->references('uuid')->on('patients')->cascadeOnDelete();
            $table->uuid('doctor_uuid')->comment('UUID Dokter pemberi resep');
            $table->foreign('doctor_uuid')->references('uuid')->on('employees')->cascadeOnDelete();
            $table->string('medication_name')->comment('Nama obat yang diresepkan');
            $table->string('medication_code')->nullable()->comment('Kode obat standar (e.g. KFA, lokal)');
            $table->string('dosage')->nullable()->comment('Dosis obat (e.g. 500mg)');
            $table->string('frequency')->nullable()->comment('Frekuensi pemakaian (e.g. 3x sehari)');
            $table->string('route')->nullable()->comment('Cara pemberian (e.g. Oral, Injeksi)');
            $table->integer('duration_days')->nullable()->comment('Durasi pemberian obat (hari)');
            $table->text('instructions')->nullable()->comment('Instruksi pemakaian tambahan');
            $table->integer('quantity')->default(1)->comment('Jumlah obat yang diberikan');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active')->comment('Status resep');
            $table->string('satusehat_uuid')->nullable()->comment('ID unik resource di portal SATUSEHAT');
            $table->bigInteger('synced_at')->nullable()->comment('Waktu sinkronisasi SATUSEHAT (Unix)');
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable(); $table->bigInteger('updated_at')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_prescriptions_company_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};

