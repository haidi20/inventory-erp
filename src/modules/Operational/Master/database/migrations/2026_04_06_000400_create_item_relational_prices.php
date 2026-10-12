<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel harga jual item berbasis level relasional
            if (!Schema::hasTable('item_prices')) {
                Schema::create('item_prices', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->char('item_uuid', 36)->comment('Relasi ke tabel items');
                    $table->char('price_level_uuid', 36)->comment('ID Level Harga (FK ke lookup_values)');
                    $table->decimal('price', 17, 5)->nullable()->default(0.00)->comment('Nilai harga jual untuk level ini');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['item_uuid', 'price_level_uuid', 'deleted_at'], 'idx_item_price_level');
                    $table->index('deleted_at', 'idx_item_price_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('item_prices');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};


