<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel master bahan baku
            if (!Schema::hasTable('ingredients')) {
                Schema::create('ingredients', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->char('branch_uuid', 36)->comment('ID Cabang pemilik data');
                    $table->char('company_uuid', 36)->comment('ID Perusahaan pemilik data');
                    $table->string('name', 255)->comment('Nama bahan baku');
                    $table->char('unit_uuid', 36)->comment('ID Satuan bahan baku');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_ingredients_deleted_at');
                });
            }
            // Tabel posisi stok bahan baku
            if (!Schema::hasTable('stocks')) {
                Schema::create('stocks', function (Blueprint $table): void {
                    $table->char('uuid', 36)->primary()->comment('Primary key UUID posisi stok');
                    $table->char('branch_uuid', 36)->comment('UUID cabang pemilik stok');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik stok');
                    $table->char('ingredient_uuid', 36)->comment('UUID bahan baku');
                    $table->bigInteger('first_stock')->default(0)->comment('Stok awal periode');
                    $table->bigInteger('stock_in')->default(0)->comment('Total stok masuk');
                    $table->bigInteger('stock_used')->default(0)->comment('Total stok terpakai');
                    $table->bigInteger('last_stock')->default(0)->comment('Stok akhir periode');
                    $table->bigInteger('stock_close_input')->default(0)->comment('Input stok saat tutup periode');
                    $table->bigInteger('difference_stock')->default(0)->comment('Selisih stok aktual dan sistem');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('branch_uuid', 'idx_stocks_branch_uuid');
                    $table->index('company_uuid', 'idx_stocks_company_uuid');
                    $table->index('ingredient_uuid', 'idx_stocks_ingredient_uuid');
                    $table->index('deleted_at', 'idx_stocks_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('stocks');
            Schema::dropIfExists('ingredients');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};


