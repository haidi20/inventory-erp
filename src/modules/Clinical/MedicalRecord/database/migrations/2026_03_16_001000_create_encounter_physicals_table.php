<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel encounter_physicals (Pemeriksaan Fisik).
 * Berdasarkan standar FHIR Observation - Physical Examination.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounter_physicals', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID cabang pemeriksaan fisik');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik pemeriksaan fisik');
            $table->uuid('encounter_uuid')->comment('UUID Pemeriksaan medis terkait');
            $table->foreign('encounter_uuid')->references('uuid')->on('encounters')->cascadeOnDelete();
            $table->text('head_neck')->nullable()->comment('Hasil pemeriksaan area kepala dan leher');
            $table->text('thorax_lung')->nullable()->comment('Hasil pemeriksaan area dada dan paru-paru');
            $table->text('abdomen')->nullable()->comment('Hasil pemeriksaan area perut');
            $table->text('extremities')->nullable()->comment('Hasil pemeriksaan anggota gerak (tangan/kaki)');
            $table->text('skin')->nullable()->comment('Hasil pemeriksaan kondisi kulit');
            $table->text('neurological')->nullable()->comment('Hasil pemeriksaan sistem saraf');
            $table->text('general_condition')->nullable()->comment('Kesan umum kondisi pasien');
            $table->enum('consciousness', ['composmentis', 'apatis', 'somnolen', 'sopor', 'koma'])->nullable()->comment('Tingkat kesadaran pasien');
            $table->string('satusehat_uuid')->nullable()->comment('ID resource resmi di portal SATUSEHAT');
            $table->bigInteger('synced_at')->nullable()->comment('Waktu sinkronisasi SATUSEHAT terakhir (Unix)');
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable(); $table->bigInteger('updated_at')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_encounter_physicals_company_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounter_physicals');
    }
};

