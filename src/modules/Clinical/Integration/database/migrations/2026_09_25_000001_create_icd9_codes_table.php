<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icd9_codes', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('code', 10)->comment('Kode ICD-9-CM standar');
            $table->string('name_uuid')->comment('Nama diagnosis/prosedur dalam Bahasa Indonesia');
            $table->string('name_en')->nullable()->comment('Nama diagnosis/prosedur dalam Bahasa Inggris');
            $table->string('category', 100)->nullable()->comment('Kategori/bab ICD-9-CM');
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();

            $table->unique('code');
            $table->index('name_uuid', 'idx_icd9_codes_name_uuid');
            $table->index('name_en', 'idx_icd9_codes_name_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icd9_codes');
    }
};
