<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel-tabel Penunjang Medis:
 * lab_orders, lab_order_items (Laboratorium), dan radiology_orders (Radiologi).
 */
return new class extends Migration
{
    public function up(): void
    {
        // â”€â”€ Laboratorium â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->nullable()->comment('UUID cabang pemilik order laboratorium');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik order laboratorium');
            $table->string('order_no')->unique()->comment('Nomor dokumen order laboratorium');
            $table->foreignUuid('patient_uuid')->constrained('patients', 'uuid')->cascadeOnDelete()->comment('ID Pasien');
            $table->foreignUuid('doctor_uuid')->nullable()->constrained('employees', 'uuid')->nullOnDelete()->comment('ID Dokter yang melakukan permintaan');
            $table->bigInteger('ordered_at')->comment('Tanggal permintaan pemeriksaan (Unix)');
            $table->string('status')->default('pending')->comment('Status order: pending, proses, selesai');
            $table->text('clinical_notes')->nullable()->comment('Indikasi klinis permintaan');
            $table->foreignUuid('resulted_by')->nullable()->constrained('users', 'uuid')->nullOnDelete()->comment('ID Petugas pemeriksa lab');
            $table->bigInteger('resulted_at')->nullable()->comment('Waktu hasil dikeluarkan (Unix)');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_lab_orders_company_deleted');
        });

        Schema::create('lab_order_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->nullable()->comment('UUID cabang pemilik detail lab');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik detail lab');
            $table->foreignUuid('lab_order_uuid')->constrained('lab_orders', 'uuid')->cascadeOnDelete()->comment('ID Dokumen lab order');
            $table->string('test_name')->comment('Nama parameter pemeriksaan (e.g. Hemoglobin)');
            $table->string('test_code')->nullable()->comment('Kode internal panel pemeriksaan');
            $table->string('result')->nullable()->comment('Nilai hasil pemeriksaan (angka/string)');
            $table->string('unit')->nullable()->comment('Satuan hasil (e.g. mg/dL)');
            $table->string('normal_range')->nullable()->comment('Nilai rujukan normal');
            $table->string('flag')->nullable()->comment('Penanda klinis (e.g. Low, High, Critical)');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_lab_order_items_company_deleted');
        });

        // â”€â”€ Radiologi â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        Schema::create('radiology_orders', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->nullable()->comment('UUID cabang pemilik order radiologi');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik order radiologi');
            $table->string('order_no')->unique()->comment('Nomor dokumen order radiologi');
            $table->foreignUuid('patient_uuid')->constrained('patients', 'uuid')->cascadeOnDelete()->comment('ID Pasien');
            $table->foreignUuid('doctor_uuid')->nullable()->constrained('employees', 'uuid')->nullOnDelete()->comment('ID Dokter pengirim');
            $table->string('modality')->comment('Jenis pemeriksaan (e.g. Rontgen, USG, MRI)');
            $table->string('body_part')->comment('Area tubuh yang diperiksa');
            $table->text('clinical_notes')->nullable()->comment('Indikasi klinis/kecurigaan medis');
            $table->string('status')->default('pending')->comment('Status order: pending, proses, selesai');
            $table->text('findings')->nullable()->comment('Deskripsi temuan radiologis');
            $table->text('impression')->nullable()->comment('Kesan/Diagnosis akhir radiologis');
            $table->foreignUuid('radiologist_uuid')->nullable()->constrained('users', 'uuid')->nullOnDelete()->comment('ID Petugas/Radiologis');
            $table->bigInteger('ordered_at')->comment('Tanggal permintaan pemeriksaan (Unix)');
            $table->bigInteger('resulted_at')->nullable()->comment('Waktu hasil dikeluarkan (Unix)');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_radiology_orders_company_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_order_items');
        Schema::dropIfExists('lab_orders');
        Schema::dropIfExists('radiology_orders');
    }
};

