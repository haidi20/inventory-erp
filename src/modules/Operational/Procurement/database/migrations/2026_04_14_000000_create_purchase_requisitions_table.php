<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('purchase_requisitions')) {
            Schema::create('purchase_requisitions', function (Blueprint $table): void {
                $table->uuid('uuid')->primary()->comment('Primary key UUID');
                $table->char('branch_uuid', 36)->comment('UUID Cabang');
                $table->char('company_uuid', 36)->comment('UUID Perusahaan');
                $table->string('document_no', 50)->comment('Nomor Permintaan Pembelian');
                $table->string('reference_no', 50)->nullable()->comment('Nomor Referensi');
                $table->bigInteger('date')->comment('Tanggal Permintaan');
                $table->char('requested_by', 36)->nullable()->comment('UUID User Pengaju');
                $table->string('status', 20)->default('draft')->comment('Status: draft, submitted, approved, rejected, converted');
                $table->char('approved_by', 36)->nullable()->comment('UUID User Penyetuju');
                $table->bigInteger('approved_at')->nullable()->comment('Waktu Persetujuan (mikrodetik)');
                $table->text('notes')->nullable()->comment('Catatan');
                $table->bigInteger('created_at')->nullable()->comment('Dibuat pada (mikrodetik)');
                $table->bigInteger('updated_at')->nullable()->comment('Diubah pada (mikrodetik)');
                $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Dihapus pada (mikrodetik), 0 = aktif');
                $table->char('created_by', 36)->nullable()->comment('UUID Pembuat');
                $table->char('updated_by', 36)->nullable()->comment('UUID Pengubah');
                $table->char('deleted_by', 36)->nullable()->comment('UUID Penghapus');
                $table->index('deleted_at', 'idx_purchase_requisitions_deleted_at');
                $table->index(['company_uuid', 'deleted_at'], 'idx_prq_company_deleted');
                $table->index(['branch_uuid', 'deleted_at'], 'idx_prq_branch_deleted');
                $table->index('date', 'idx_prq_date');
                $table->index('status', 'idx_prq_status');
                $table->unique(['branch_uuid', 'document_no', 'deleted_at'], 'idx_prq_doc_no_unique_soft');
            });
        }

        if (!Schema::hasTable('purchase_requisition_items')) {
            Schema::create('purchase_requisition_items', function (Blueprint $table): void {
                $table->uuid('uuid')->primary()->comment('Primary key UUID');
                $table->char('purchase_requisition_uuid', 36)->comment('UUID Purchase Requisition');
                $table->char('branch_uuid', 36)->comment('UUID Cabang');
                $table->char('company_uuid', 36)->comment('UUID Perusahaan');
                $table->char('product_uuid', 36)->comment('UUID Produk/Item');
                $table->char('unit_uuid', 36)->comment('UUID Satuan');
                $table->decimal('quantity', 12, 4)->comment('Jumlah Diminta');
                $table->decimal('estimated_price', 18, 2)->nullable()->default(0)->comment('Perkiraan Harga Satuan');
                $table->decimal('subtotal', 18, 2)->nullable()->default(0)->comment('Perkiraan Subtotal');
                $table->text('notes')->nullable()->comment('Catatan');
                $table->bigInteger('created_at')->nullable()->comment('Dibuat pada (mikrodetik)');
                $table->bigInteger('updated_at')->nullable()->comment('Diubah pada (mikrodetik)');
                $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Dihapus pada (mikrodetik), 0 = aktif');
                $table->char('created_by', 36)->nullable()->comment('UUID Pembuat');
                $table->char('updated_by', 36)->nullable()->comment('UUID Pengubah');
                $table->char('deleted_by', 36)->nullable()->comment('UUID Penghapus');
                $table->index('deleted_at', 'idx_purchase_requisition_items_deleted_at');
                $table->index(['company_uuid', 'deleted_at'], 'idx_prqi_company_deleted');
                $table->index('purchase_requisition_uuid', 'idx_prqi_prq_uuid');
                $table->index('product_uuid', 'idx_prqi_product_uuid');
            });

            // FK dari purchase_order_items (dibuat sebelum PR di migrasi
            // 2026_04_11) ditambahkan di sini agar urutan referensi benar.
            if (Schema::hasTable('purchase_order_items')) {
                Schema::table('purchase_order_items', function (Blueprint $table): void {
                    $table->foreign('purchase_requisition_uuid')->references('uuid')->on('purchase_requisitions')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requisition_items');
        Schema::dropIfExists('purchase_requisitions');
    }
};
