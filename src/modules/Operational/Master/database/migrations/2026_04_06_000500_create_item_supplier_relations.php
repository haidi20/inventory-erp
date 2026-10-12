<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel relasi item dengan supplier
            if (!Schema::hasTable('item_suppliers')) {
                Schema::create('item_suppliers', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->char('item_uuid', 36)->comment('Relasi ke tabel items');
                    $table->char('supplier_uuid', 36)->comment('Relasi ke tabel people');
                    $table->string('supplier_item_code', 100)->nullable()->comment('Kode SKU dari sisi vendor');
                    $table->decimal('purchase_price', 17, 5)->nullable()->default(0.00)->comment('Harga beli khusus dari supplier ini');
                    $table->boolean('is_primary')->nullable()->default(false)->comment('Penanda supplier utama');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['item_uuid', 'supplier_uuid', 'deleted_at'], 'idx_item_supplier');
                    $table->index('deleted_at', 'idx_item_supplier_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('item_suppliers');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};


