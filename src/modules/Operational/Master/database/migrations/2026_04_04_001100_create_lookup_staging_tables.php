<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel sementara lookup untuk proses staging data.
            if (!Schema::hasTable('temp_lovs')) {
                Schema::create('temp_lovs', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID data staging lookup');
                    $table->char('lookup_value_uuid', 36)->nullable()->comment('UUID nilai lookup sumber');
                    $table->char('lookup_set_uuid', 36)->nullable()->comment('UUID set lookup sumber');
                    $table->string('program_code', 100)->nullable()->comment('Kode program nilai lookup');
                    $table->string('display_name', 255)->comment('Nama tampilan lookup');
                    $table->text('description')->nullable()->comment('Deskripsi lookup');
                    $table->integer('seq')->nullable()->comment('Urutan nilai lookup');
                    $table->char('active_flag', 1)->default('Y')->comment('Status aktif: Y/N');
                    $table->string('price_up_beli', 100)->default('HARGA BELI')->comment('Label harga beli');
                    $table->string('diskon_pilih', 32)->default('0.00000')->comment('Nilai diskon pilihan');
                    $table->string('persen1', 32)->nullable()->comment('Nilai persentase pertama');
                    $table->string('persen2', 32)->nullable()->comment('Nilai persentase kedua');

                    // System Fields (Standard Audit)
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');

                    $table->index('deleted_at', 'idx_temp_lovs_deleted_at');
                    $table->index('created_by', 'idx_temp_lovs_created_by');
                    $table->index('updated_by', 'idx_temp_lovs_updated_by');
                    $table->index('deleted_by', 'idx_temp_lovs_deleted_by');
                });
            }

            // Tabel detail lookup sementara.
            if (!Schema::hasTable('temp_lov_details')) {
                Schema::create('temp_lov_details', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID detail staging lookup');
                    $table->char('temp_lov_uuid', 36)->comment('UUID referensi ke temp_lovs');
                    $table->integer('idx')->comment('Urutan detail lookup');
                    $table->text('value')->nullable()->comment('Nilai atribut detail lookup');

                    // System Fields (Standard Audit)
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');

                    $table->index('temp_lov_uuid', 'idx_temp_lov_details_temp_lov_uuid');
                    $table->index('deleted_at', 'idx_temp_lov_details_deleted_at');
                    $table->index('created_by', 'idx_temp_lov_details_created_by');
                    $table->index('updated_by', 'idx_temp_lov_details_updated_by');
                    $table->index('deleted_by', 'idx_temp_lov_details_deleted_by');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('temp_lov_details');
            Schema::dropIfExists('temp_lovs');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};


