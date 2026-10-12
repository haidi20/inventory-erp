<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->index();
            $table->uuid('company_uuid')->index();
            $table->uuid('supplier_uuid')->index();
            $table->uuid('location_uuid')->index();
            $table->string('document_no')->unique();
            $table->string('reference_no')->nullable();
            $table->bigInteger('date')->index();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->index(['company_uuid', 'deleted_at'], 'idx_purchase_orders_company_deleted');
            $table->index('deleted_at', 'idx_purchase_orders_deleted_at');
            $table->index(['company_uuid', 'branch_uuid', 'deleted_at'], 'idx_purchase_orders_company_branch_deleted');
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('purchase_order_uuid')->index();
            $table->uuid('branch_uuid')->index();
            $table->uuid('company_uuid')->index();
            $table->uuid('product_uuid')->index();
            $table->uuid('purchase_requisition_uuid')->nullable()->comment('UUID PR sumber (null = direct)');
            $table->uuid('unit_uuid')->index();
            $table->decimal('quantity', 15, 2);
            $table->decimal('price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->index(['company_uuid', 'deleted_at'], 'idx_purchase_order_items_company_deleted');
            $table->index('purchase_requisition_uuid', 'idx_poi_purchase_requisition_uuid');

            $table->foreign('purchase_order_uuid')->references('uuid')->on('purchase_orders')->onDelete('cascade');
            // FK ke purchase_requisitions ditambahkan pada migrasi 2026_04_14
            // (create_purchase_requisitions_table) karena tabel PR dibuat
            // lebih lambat; menambahkannya di sini akan gagal referensi.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
