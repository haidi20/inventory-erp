<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('goods_receipts')) {
            Schema::create('goods_receipts', function (Blueprint $table): void {
                $table->uuid('uuid')->primary()->comment('Primary key UUID');
                $table->char('branch_uuid', 36)->comment('UUID Cabang');
                $table->char('company_uuid', 36)->comment('UUID Perusahaan');
                $table->string('document_no', 50)->comment('Nomor Penerimaan Barang');
                $table->string('reference_no', 50)->nullable()->comment('Nomor Surat Jalan/Referensi');
                $table->bigInteger('date')->comment('Tanggal Penerimaan');
                $table->char('supplier_uuid', 36)->comment('UUID Supplier');
                $table->char('location_uuid', 36)->comment('UUID Lokasi/Gudang Penerimaan');
                $table->char('purchase_order_uuid', 36)->nullable()->comment('UUID Purchase Order sumber');
                $table->char('purchase_invoice_uuid', 36)->nullable()->comment('UUID Faktur Pembelian (3-way matching)');
                $table->char('received_by', 36)->nullable()->comment('UUID User Penerima');
                $table->string('status', 20)->default('draft')->comment('Status: draft, posted, cancelled');
                $table->decimal('total', 18, 2)->nullable()->default(0)->comment('Subtotal items');
                $table->decimal('tax_amount', 18, 2)->nullable()->default(0)->comment('Nilai PPN');
                $table->decimal('net_amount', 18, 2)->nullable()->default(0)->comment('Grand Total');
                $table->text('notes')->nullable()->comment('Catatan');
                $table->bigInteger('created_at')->nullable()->comment('Dibuat pada (mikrodetik)');
                $table->bigInteger('updated_at')->nullable()->comment('Diubah pada (mikrodetik)');
                $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Dihapus pada (mikrodetik), 0 = aktif');
                $table->char('created_by', 36)->nullable()->comment('UUID Pembuat');
                $table->char('updated_by', 36)->nullable()->comment('UUID Pengubah');
                $table->char('deleted_by', 36)->nullable()->comment('UUID Penghapus');
                $table->index('deleted_at', 'idx_goods_receipts_deleted_at');
                $table->index(['company_uuid', 'deleted_at'], 'idx_gr_company_deleted');
                $table->index(['branch_uuid', 'deleted_at'], 'idx_gr_branch_deleted');
                $table->index('supplier_uuid', 'idx_gr_supplier_uuid');
                $table->index('location_uuid', 'idx_gr_location_uuid');
                $table->index('purchase_order_uuid', 'idx_gr_purchase_order_uuid');
                $table->index('date', 'idx_gr_date');
                $table->index('status', 'idx_gr_status');
                $table->unique(['branch_uuid', 'document_no', 'deleted_at'], 'idx_gr_doc_no_unique_soft');
            });
        }

        if (!Schema::hasTable('goods_receipt_items')) {
            Schema::create('goods_receipt_items', function (Blueprint $table): void {
                $table->uuid('uuid')->primary()->comment('Primary key UUID');
                $table->char('goods_receipt_uuid', 36)->comment('UUID Goods Receipt');
                $table->char('branch_uuid', 36)->comment('UUID Cabang');
                $table->char('company_uuid', 36)->comment('UUID Perusahaan');
                $table->char('product_uuid', 36)->comment('UUID Produk/Item');
                $table->string('barcode', 100)->nullable()->comment('Barcode Produk');
                $table->char('unit_uuid', 36)->comment('UUID Satuan');
                $table->decimal('quantity', 12, 4)->comment('Jumlah Diterima');
                $table->char('purchase_order_item_uuid', 36)->nullable()->comment('UUID PO Item sumber (3-way matching)');
                $table->decimal('price', 18, 2)->default(0)->comment('Harga Satuan');
                $table->decimal('discount', 18, 2)->nullable()->default(0)->comment('Diskon');
                $table->decimal('subtotal', 18, 2)->nullable()->default(0)->comment('Subtotal');
                $table->text('notes')->nullable()->comment('Catatan');
                $table->bigInteger('created_at')->nullable()->comment('Dibuat pada (mikrodetik)');
                $table->bigInteger('updated_at')->nullable()->comment('Diubah pada (mikrodetik)');
                $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Dihapus pada (mikrodetik), 0 = aktif');
                $table->char('created_by', 36)->nullable()->comment('UUID Pembuat');
                $table->char('updated_by', 36)->nullable()->comment('UUID Pengubah');
                $table->char('deleted_by', 36)->nullable()->comment('UUID Penghapus');
                $table->index('deleted_at', 'idx_goods_receipt_items_deleted_at');
                $table->index(['company_uuid', 'deleted_at'], 'idx_gri_company_deleted');
                $table->index('goods_receipt_uuid', 'idx_gri_gr_uuid');
                $table->index('product_uuid', 'idx_gri_product_uuid');
                $table->index('purchase_order_item_uuid', 'idx_gri_purchase_order_item_uuid');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
    }
};
