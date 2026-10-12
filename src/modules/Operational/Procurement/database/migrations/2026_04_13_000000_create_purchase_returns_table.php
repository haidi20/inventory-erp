<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel header retur pembelian
            if (!Schema::hasTable('purchase_returns')) {
                Schema::create('purchase_returns', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID');
                    $table->bigInteger('id')->nullable()->comment('ID Legacy');
                    $table->char('branch_uuid', 36)->comment('UUID Cabang');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->string('document_no', 50)->comment('Nomor Retur');
                    $table->string('reference_no', 50)->nullable()->comment('Nomor Referensi/Reff');
                    $table->bigInteger('date')->comment('Tanggal Retur');
                    $table->char('supplier_uuid', 36)->comment('UUID Supplier');
                    $table->char('location_uuid', 36)->comment('UUID Gudang');
                    $table->char('purchase_invoice_uuid', 36)->nullable()->comment('Link ke Faktur Pembelian (NO FAKTUR)');
                    $table->char('term_uuid', 36)->nullable()->comment('Link ke Jangka Waktu');
                    $table->decimal('total', 18, 2)->nullable()->default(0)->comment('Subtotal items');
                    $table->decimal('tax_amount', 18, 2)->nullable()->default(0)->comment('Nilai PPN');
                    $table->decimal('net_amount', 18, 2)->nullable()->default(0)->comment('Grand Total');
                    $table->string('status', 20)->default('posted')->comment('Status');
                    $table->text('notes')->nullable()->comment('Catatan');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->index('deleted_at', 'idx_purchase_returns_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_pr_company_deleted');
                    $table->index(['branch_uuid', 'deleted_at'], 'idx_pr_branch_deleted');
                    $table->index('supplier_uuid', 'idx_pr_supplier_uuid');
                    $table->index('date', 'idx_pr_date');
                    $table->index('status', 'idx_pr_status');
                    $table->unique(['branch_uuid', 'document_no', 'deleted_at'], 'idx_pr_doc_no_unique_soft');
                });
            }
            // Tabel detail retur pembelian
            if (!Schema::hasTable('purchase_return_items')) {
                Schema::create('purchase_return_items', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->char('purchase_return_uuid', 36)->comment('Penjelasan kolom purchase_return_uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('Penjelasan kolom item_uuid.');
                    $table->string('barcode', 100)->nullable()->comment('Penjelasan kolom barcode.');
                    $table->char('unit_uuid', 36)->comment('Penjelasan kolom unit_uuid.');
                    $table->decimal('quantity', 12, 4)->comment('Penjelasan kolom quantity.');
                    $table->decimal('input_price', 18, 2)->nullable()->default(0)->comment('Penjelasan kolom input_price.');
                    $table->decimal('price', 18, 2)->default(0)->comment('Penjelasan kolom price.');
                    $table->decimal('discount', 18, 2)->nullable()->default(0)->comment('Penjelasan kolom discount.');
                    $table->decimal('subtotal', 18, 2)->nullable()->default(0)->comment('Penjelasan kolom subtotal.');
                    $table->text('notes')->nullable()->comment('Penjelasan kolom notes.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->index('deleted_at', 'idx_purchase_return_items_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_pri_company_deleted');
                    $table->index('purchase_return_uuid', 'idx_pri_pr_uuid');
                    $table->index('item_uuid', 'idx_pri_item_uuid');
                });
            }
            // Tabel pembayaran retur pembelian
            if (!Schema::hasTable('purchase_return_payments')) {
                Schema::create('purchase_return_payments', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->char('purchase_return_uuid', 36)->comment('Penjelasan kolom purchase_return_uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->string('voucher_no', 50)->comment('Penjelasan kolom voucher_no.');
                    $table->string('reference_no', 50)->nullable()->comment('Penjelasan kolom reference_no.');
                    $table->bigInteger('date')->comment('Penjelasan kolom date.');
                    $table->char('account_uuid', 36)->comment('Penjelasan kolom account_uuid.');
                    $table->string('cheque_no', 50)->nullable()->comment('Penjelasan kolom cheque_no.');
                    $table->decimal('discount', 18, 2)->nullable()->default(0)->comment('Penjelasan kolom discount.');
                    $table->decimal('amount', 18, 2)->comment('Penjelasan kolom amount.');
                    $table->text('notes')->nullable()->comment('Penjelasan kolom notes.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->index('deleted_at', 'idx_purchase_return_payments_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_prp_company_deleted');
                    $table->index('purchase_return_uuid', 'idx_prp_pr_uuid');
                    $table->index('date', 'idx_prp_date');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('purchase_return_payments');
            Schema::dropIfExists('purchase_return_items');
            Schema::dropIfExists('purchase_returns');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};



