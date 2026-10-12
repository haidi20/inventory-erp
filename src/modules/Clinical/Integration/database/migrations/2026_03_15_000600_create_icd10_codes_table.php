<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel icd10_codes (Kamus Internasional Penyakit v10).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icd10_codes', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('code', 10)->unique()->comment('Kode ICD-10 standar');
            $table->string('name_uuid')->comment('Nama diagnosis dalam Bahasa Indonesia');
            $table->string('name_en')->nullable()->comment('Nama diagnosis dalam Bahasa Inggris');
            $table->string('category', 100)->nullable()->comment('Kategori besar penyakit');
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable(); $table->bigInteger('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icd10_codes');
    }
};

