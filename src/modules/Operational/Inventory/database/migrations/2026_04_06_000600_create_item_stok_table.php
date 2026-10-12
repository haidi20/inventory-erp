<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel stok item format legacy
            if (!Schema::hasTable('item_stok')) {
                Schema::create('item_stok', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->bigInteger('id')->nullable()->comment('Penjelasan kolom id.');
                    $table->char('item_uuid', 36)->comment('Penjelasan kolom item_uuid.');
                    $table->char('location_uuid', 36)->comment('Penjelasan kolom location_uuid.');
                    $table->bigInteger('stock_date')->nullable()->comment('Penjelasan kolom stock_date.');
                    $table->decimal('stock_awal', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom stock_awal.');
                    $table->decimal('qty_awal', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom qty_awal.');
                    $table->decimal('qty_masuk', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom qty_masuk.');
                    $table->decimal('qty_keluar', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom qty_keluar.');
                    $table->decimal('hpp', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom hpp.');
                    $table->boolean('default_flag')->nullable()->default(0)->comment('Penjelasan kolom default_flag.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->unique(['branch_uuid', 'item_uuid', 'location_uuid', 'deleted_at'], 'idx_item_stok_unique_soft');
                    $table->index('deleted_at', 'idx_item_stok_deleted_at');
                    $table->index(['company_uuid', 'branch_uuid', 'deleted_at'], 'idx_item_stok_company_branch_deleted');
        });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('item_stok');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};
