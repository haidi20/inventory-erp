<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel shift kasir POS
            if (!Schema::hasTable('shifts')) {
                Schema::create('shifts', function (Blueprint $table): void {
                    $table->char('uuid', 36)->primary()->comment('Penjelasan kolom id.');
                    $table->integer('shift_number')->comment('Penjelasan kolom shift_number.');
                    $table->bigInteger('date')->comment('Penjelasan kolom date.');
                    $table->bigInteger('start_time')->comment('Penjelasan kolom start_time.');
                    $table->bigInteger('opening_balance')->comment('Penjelasan kolom opening_balance.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('location_uuid', 36)->comment('Penjelasan kolom location_uuid.');
                    $table->char('user_uuid', 36)->comment('Penjelasan kolom user_id.');
                    $table->boolean('is_closed')->nullable()->default(false)->comment('Penjelasan kolom is_closed.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                });
            }
            // Tabel transaksi POS
            if (!Schema::hasTable('transactions')) {
                Schema::create('transactions', function (Blueprint $table): void {
                    $table->char('uuid', 36)->primary()->comment('Penjelasan kolom id.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('shift_uuid', 36)->nullable()->comment('Penjelasan kolom shift_uuid.');
                    $table->char('location_uuid', 36)->comment('Penjelasan kolom location_uuid.');
                    $table->bigInteger('sequence_number')->nullable()->comment('Penjelasan kolom sequence_number.');
                    $table->char('order_type_uuid', 36)->comment('Penjelasan kolom order_type_uuid.');
                    $table->string('category_order', 255)->nullable()->comment('Penjelasan kolom category_order.');
                    $table->char('user_uuid', 36)->comment('Penjelasan kolom user_id.');
                    $table->string('status', 20)->nullable()->default('pending')->comment('Penjelasan kolom status.');
                    $table->string('payment_method', 255)->nullable()->comment('Penjelasan kolom payment_method.');
                    $table->bigInteger('date')->comment('Penjelasan kolom date.');
                    $table->text('notes')->nullable()->comment('Penjelasan kolom notes.');
                    $table->bigInteger('total_amount')->comment('Penjelasan kolom total_amount.');
                    $table->bigInteger('total_qty')->comment('Penjelasan kolom total_qty.');
                    $table->bigInteger('change_money')->nullable()->default(0)->comment('Penjelasan kolom change_money.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->index('branch_uuid', 'idx_transactions_branch_uuid');
                    $table->index('company_uuid', 'idx_transactions_company_uuid');
                });
            }
            // Tabel detail transaksi POS
            if (!Schema::hasTable('transaction_details')) {
                Schema::create('transaction_details', function (Blueprint $table): void {
                    $table->char('uuid', 36)->primary()->comment('Penjelasan kolom id.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('transaction_uuid', 36)->comment('Penjelasan kolom transaction_uuid.');
                    $table->char('product_uuid', 36)->comment('Penjelasan kolom product_uuid.');
                    $table->string('product_name', 255)->comment('Penjelasan kolom product_name.');
                    $table->bigInteger('product_price')->comment('Penjelasan kolom product_price.');
                    $table->bigInteger('qty')->comment('Penjelasan kolom qty.');
                    $table->bigInteger('subtotal')->comment('Penjelasan kolom subtotal.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->index('branch_uuid', 'idx_transaction_details_branch_uuid');
                    $table->index('company_uuid', 'idx_transaction_details_company_uuid');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('transaction_details');
            Schema::dropIfExists('transactions');
            Schema::dropIfExists('shifts');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};


