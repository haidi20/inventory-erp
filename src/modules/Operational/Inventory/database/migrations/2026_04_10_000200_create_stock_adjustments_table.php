<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel header penyesuaian stok
            if (!Schema::hasTable('stock_adjustments')) {
                Schema::create('stock_adjustments', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('location_uuid', 36)->comment('Penjelasan kolom location_uuid.');
                    $table->string('adjustment_no', 50)->comment('Penjelasan kolom adjustment_no.');
                    $table->string('reference_no', 100)->nullable()->comment('Penjelasan kolom reference_no.');
                    $table->bigInteger('date')->comment('Penjelasan kolom date.');
                    $table->string('status', 20)->default('draft')->comment('Penjelasan kolom status.');
                    $table->char('account_uuid', 36)->nullable()->comment('Penjelasan kolom account_uuid.');
                    $table->text('notes')->nullable()->comment('Penjelasan kolom notes.');
                    $table->boolean('is_value_adjustment')->nullable()->default(0)->comment('Penjelasan kolom is_value_adjustment.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->index('deleted_at', 'idx_adj_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_adj_company_deleted');
                    $table->index('location_uuid', 'idx_adj_location_uuid');
                });
            }
            // Tabel detail penyesuaian stok
            if (!Schema::hasTable('stock_adjustment_items')) {
                Schema::create('stock_adjustment_items', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('adjustment_uuid', 36)->comment('Penjelasan kolom adjustment_uuid.');
                    $table->char('item_uuid', 36)->comment('Penjelasan kolom item_uuid.');
                    $table->char('unit_uuid', 36)->comment('Penjelasan kolom unit_uuid.');
                    $table->decimal('price', 18, 2)->default(0)->comment('Penjelasan kolom price.');
                    $table->decimal('book_qty', 12, 4)->comment('Penjelasan kolom book_qty.');
                    $table->decimal('actual_qty', 12, 4)->comment('Penjelasan kolom actual_qty.');
                    $table->decimal('adjustment_qty', 12, 4)->comment('Penjelasan kolom adjustment_qty.');
                    $table->string('reason', 255)->nullable()->comment('Penjelasan kolom reason.');
                    $table->text('notes')->nullable()->comment('Penjelasan kolom notes.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->index('deleted_at', 'idx_adj_item_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_adj_item_company_deleted');
                    $table->index('adjustment_uuid', 'idx_adj_item_adj_uuid');
                    $table->index('item_uuid', 'idx_adj_item_item_uuid');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('stock_adjustment_items');
            Schema::dropIfExists('stock_adjustments');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};




