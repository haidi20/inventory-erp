<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('files')) {
            Schema::create('files', function (Blueprint $table): void {
                $table->uuid('uuid')->primary()->comment('UUID file internal');
                $table->unsignedBigInteger('id')->nullable()->comment('ID legacy numeric');
                $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik file');
                $table->char('company_uuid', 36)->nullable()->comment('UUID perusahaan pemilik file');
                $table->char('parent_uuid', 36)->nullable()->comment('UUID parent record');
                $table->string('parent_model', 150)->nullable()->comment('Nama model parent');
                $table->string('feature', 100)->nullable()->comment('Nama fitur pemilik file');
                $table->string('context_type', 100)->nullable()->comment('Tipe konteks file');
                $table->string('context_identifier', 150)->nullable()->comment('Identifier konteks tambahan');
                $table->unsignedInteger('sort_order')->default(0)->comment('Urutan tampilan');
                $table->boolean('is_primary')->default(false)->comment('Penanda file utama');
                $table->unsignedInteger('version')->default(1)->comment('Versi file');
                $table->boolean('is_latest')->default(true)->comment('Penanda versi terbaru');
                $table->string('original_filename')->comment('Nama file asli');
                $table->string('file_path')->comment('Path penyimpanan file');
                $table->unsignedBigInteger('file_size')->default(0)->comment('Ukuran file dalam byte');
                $table->string('mime_type', 150)->nullable()->comment('Mime type file');
                $table->string('file_hash', 255)->nullable()->comment('Hash konten file');
                $table->string('file_category', 100)->nullable()->comment('Kategori file');
                $table->string('processing_status', 50)->default('ready')->comment('Status proses file');
                $table->string('disk', 50)->default('public')->comment('Disk storage Laravel');
                $table->char('uploaded_by_uuid', 36)->nullable()->comment('UUID user uploader');
                $table->string('uploaded_ip', 45)->nullable()->comment('IP uploader');
                $table->bigInteger('accessed_at')->nullable()->comment('Waktu akses terakhir');
                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->char('created_by', 36)->nullable();
                $table->char('updated_by', 36)->nullable();
                $table->char('deleted_by', 36)->nullable();

                $table->index(['branch_uuid', 'deleted_at'], 'idx_files_branch_deleted');
                $table->index(['company_uuid', 'deleted_at'], 'idx_files_company_deleted');
                $table->index(['parent_model', 'parent_uuid'], 'idx_files_parent');
                $table->index(['feature', 'context_type'], 'idx_files_feature_context');
                $table->index('file_hash', 'idx_files_file_hash');
            });
        }

        if (!Schema::hasTable('file_variants')) {
            Schema::create('file_variants', function (Blueprint $table): void {
                $table->uuid('uuid')->primary()->comment('UUID varian file');
                $table->unsignedBigInteger('id')->nullable()->comment('ID legacy numeric');
                $table->char('file_uuid', 36)->comment('UUID file induk');
                $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik file');
                $table->char('company_uuid', 36)->nullable()->comment('UUID perusahaan pemilik file');
                $table->string('variant_type', 100)->comment('Jenis varian, mis. thumbnail');
                $table->string('file_path')->comment('Path file varian');
                $table->unsignedInteger('width')->nullable()->comment('Lebar gambar');
                $table->unsignedInteger('height')->nullable()->comment('Tinggi gambar');
                $table->unsignedBigInteger('file_size')->default(0)->comment('Ukuran file dalam byte');
                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->char('created_by', 36)->nullable();
                $table->char('updated_by', 36)->nullable();
                $table->char('deleted_by', 36)->nullable();

                $table->index('file_uuid', 'idx_file_variants_file_uuid');
                $table->index(['company_uuid', 'deleted_at'], 'idx_file_variants_company_deleted');
            });
        }

        if (!Schema::hasTable('file_audits')) {
            Schema::create('file_audits', function (Blueprint $table): void {
                $table->uuid('uuid')->primary()->comment('UUID audit file');
                $table->unsignedBigInteger('id')->nullable()->comment('ID legacy numeric');
                $table->char('file_uuid', 36)->nullable()->comment('UUID file terkait');
                $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik file');
                $table->char('company_uuid', 36)->nullable()->comment('UUID perusahaan pemilik file');
                $table->char('actor_uuid', 36)->nullable()->comment('UUID actor');
                $table->string('action', 100)->comment('Aksi terhadap file');
                $table->text('note')->nullable()->comment('Catatan audit');
                $table->string('actor_ip', 45)->nullable()->comment('IP actor');
                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->char('created_by', 36)->nullable();
                $table->char('updated_by', 36)->nullable();
                $table->char('deleted_by', 36)->nullable();

                $table->index('file_uuid', 'idx_file_audits_file_uuid');
                $table->index(['company_uuid', 'deleted_at'], 'idx_file_audits_company_deleted');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('file_audits');
        Schema::dropIfExists('file_variants');
        Schema::dropIfExists('files');
    }
};

