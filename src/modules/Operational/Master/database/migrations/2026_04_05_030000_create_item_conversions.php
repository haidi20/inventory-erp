<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel konversi satuan global
            if (!Schema::hasTable('item_conversions')) {
                Schema::create('item_conversions', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ITEM_CONVERTION_ID legacy');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('Set to 0/empty UUID for global');
                    $table->bigInteger('legacy_item_uuid')->nullable()->default(0)->comment('Penjelasan kolom legacy_item_id.');
                    $table->string('from_uom', 18)->comment('Penjelasan kolom from_uom.');
                    $table->string('to_uom', 18)->comment('Penjelasan kolom to_uom.');
                    $table->decimal('to_qty', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom to_qty.');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Penjelasan kolom is_active.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index(['branch_uuid', 'deleted_at'], 'idx_item_conversions_branch_deleted');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_item_conversions_company_deleted');
                    $table->index(
                        ['branch_uuid', 'company_uuid', 'from_uom', 'to_uom', 'deleted_at'],
                        'idx_item_conversions_scope_uoms'
                    );
                    $table->index('deleted_at', 'idx_item_conversions_deleted_at');
                });
            }
            // Tabel satuan khusus per item
            if (!Schema::hasTable('item_uoms')) {
                Schema::create('item_uoms', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ITEM_UOM_ID legacy');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('Penjelasan kolom item_uuid.');
                    $table->bigInteger('legacy_item_uuid')->nullable()->default(0)->comment('Penjelasan kolom legacy_item_id.');
                    $table->string('uom_code', 18)->comment('Penjelasan kolom uom_code.');
                    $table->boolean('is_base_uom')->nullable()->default(false)->comment('Penjelasan kolom is_base_uom.');
                    $table->decimal('to_qty', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom to_qty.');
                    $table->string('barcode', 255)->nullable()->comment('Penjelasan kolom barcode.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_item_uoms_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('item_uoms');
            Schema::dropIfExists('item_conversions');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};


