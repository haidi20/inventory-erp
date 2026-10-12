<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel jenis lookup standar aplikasi.
            if (!Schema::hasTable('lookup_types')) {
                Schema::create('lookup_types', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID jenis lookup');
                    $table->string('code', 255)->comment('Kode unik jenis lookup');
                    $table->string('name', 255)->comment('Nama jenis lookup');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik data');
                    $table->text('description')->nullable()->comment('Deskripsi jenis lookup');
                    $table->boolean('is_active')->default(true)->comment('Status aktif jenis lookup');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['code', 'deleted_at'], 'idx_lookup_types_code');
                    $table->index('company_uuid', 'idx_lookup_types_company_uuid');
                    $table->index('deleted_at', 'idx_lookup_types_deleted_at');
                });
            }

            // Tabel master set lookup gabungan (APP + legacy).
            if (!Schema::hasTable('lookup_sets')) {
                Schema::create('lookup_sets', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID lookup set');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy umum');
                    $table->integer('lookup_set_uuid')->nullable()->comment('ID legacy lookup set');
                    $table->string('code', 150)->nullable()->comment('Kode set lookup');
                    $table->string('name', 255)->comment('Nama set lookup');
                    $table->string('source', 20)->default('app')->comment('Sumber data: app atau legacy');
                    $table->string('program_code', 100)->nullable()->comment('Kode program lookup');
                    $table->char('company_uuid', 36)
                        ->nullable()
                        ->comment('ID perusahaan pemilik data; null untuk lookup global');
                    $table->text('description')->nullable()->comment('Deskripsi set lookup');
                    $table->char('user_can_edit_flag', 1)->default('N')->comment('Penanda edit oleh user: Y/N');
                    $table->boolean('is_active')->default(true)->comment('Status aktif set lookup');

                    // System Fields (Standard Audit)
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');

                    $table->unique(['company_uuid', 'code', 'deleted_at'], 'idx_lookup_sets_company_code');
                    $table->index('lookup_set_uuid', 'idx_lookup_sets_legacy_uuid');
                    $table->index('deleted_at', 'idx_lookup_sets_deleted_at');
                });
            }

            // Tabel nilai lookup gabungan (APP + legacy).
            if (!Schema::hasTable('lookup_values')) {
                Schema::create('lookup_values', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID lookup value');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy umum');
                    $table->integer('lookup_value_uuid')->nullable()->comment('ID legacy lookup value');
                    $table->char('lookup_set_uuid', 36)->comment('Relasi ke lookup_sets.uuid');
                    $table->string('code', 150)->nullable()->comment('Kode nilai lookup');
                    $table->string('display_name', 255)->comment('Nama tampil nilai lookup');
                    $table->string('value', 255)->nullable()->comment('Nilai generik lookup');
                    $table->string('program_code', 100)->nullable()->comment('Kode program untuk nilai');
                    $table->integer('seq')->nullable()->comment('Urutan nilai lookup');
                    $table->bigInteger('start_date')->nullable()->comment('Tanggal mulai berlaku (unix timestamp)');
                    $table->bigInteger('end_date')->nullable()->comment('Tanggal akhir berlaku (unix timestamp)');
                    $table->integer('order_priority')->default(0)->comment('Prioritas urutan tampilan');
                    $table->char('primary_flag', 1)->default('N')->comment('Y = default/primary');
                    $table->decimal('max_stock', 17, 5)->nullable()->comment('Stok maksimum');
                    $table->boolean('is_default')->default(false)->comment('Penanda nilai default');
                    $table->boolean('is_active')->default(true)->comment('Status aktif nilai');
                    $table->char('active_flag', 1)->default('Y')->comment('Flag aktif: Y/N');
                    $table->string('price_up_beli', 100)->default('HARGA BELI')->comment('Label harga beli');
                    $table->string('diskon_pilih', 32)->default('0.00000')->comment('Nilai diskon pilih');
                    $table->string('persen1', 32)->nullable()->comment('Persentase 1');
                    $table->string('persen2', 32)->nullable()->comment('Persentase 2');
                    $table->char('company_uuid', 36)->comment('ID perusahaan pemilik data');
                    $table->text('description')->nullable()->comment('Deskripsi nilai lookup');

                    // System Fields (Standard Audit)
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');

                    $table->unique(['lookup_set_uuid', 'code', 'deleted_at'], 'idx_lookup_values_set_code');
                    $table->index('lookup_value_uuid', 'idx_lookup_values_legacy_uuid');
                    $table->index('company_uuid', 'idx_lookup_values_company_uuid');
                    $table->index('deleted_at', 'idx_lookup_values_deleted_at');
                });
            }

            // Tabel detail atribut nilai lookup untuk kebutuhan atribut dinamis.
            if (!Schema::hasTable('lookup_value_details')) {
                Schema::create('lookup_value_details', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID detail lookup value');
                    $table->char('lookup_value_uuid', 36)->comment('Relasi ke lookup_values.uuid');
                    $table->integer('idx')->default(0)->comment('Urutan atribut detail');
                    $table->text('value')->nullable()->comment('Nilai atribut detail');
                    $table->char('company_uuid', 36)->nullable()->comment('ID perusahaan pemilik data');

                    // System Fields (Standard Audit)
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');

                    $table->index('lookup_value_uuid', 'idx_lookup_value_details_lookup_value_uuid');
                    $table->index('deleted_at', 'idx_lookup_value_details_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('lookup_value_details');
            Schema::dropIfExists('lookup_values');
            Schema::dropIfExists('lookup_sets');
            Schema::dropIfExists('lookup_types');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};


