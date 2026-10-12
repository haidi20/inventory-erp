<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel-tabel Farmasi:
 * dispensings (pengeluaran obat), purchase_returns (retur pembelian), 
 * dan penambahan kolom pada stock_movements serta purchases.
 */
return new class extends Migration
{
    public function up(): void
    {
        // â”€â”€ Tambah kolom ke stock_movements â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->default(0)->after('qty')->comment('Harga satuan barang saat mutasi');
            $table->text('notes')->nullable()->after('price')->comment('Catatan tambahan mutasi stok');
            $table->foreignUuid('user_uuid')->nullable()->constrained('users', 'uuid')->nullOnDelete()->after('notes')->comment('ID Petugas pelaksana mutasi');
        });

        // â”€â”€ Dispensing (pengeluaran obat ke pasien) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        Schema::create('dispensings', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->nullable()->comment('UUID cabang pemilik dispensing');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik dispensing');
            $table->string('dispensing_no')->unique()->comment('Nomor dokumen pengeluaran obat');
            $table->foreignUuid('patient_uuid')->nullable()->constrained('patients', 'uuid')->nullOnDelete()->comment('ID Pasien penerima obat');
            $table->foreignUuid('dispensed_by_uuid')->nullable()->constrained('users', 'uuid')->nullOnDelete()->comment('ID Petugas apotek');
            $table->text('notes')->nullable()->comment('Catatan pengeluaran obat');
            $table->decimal('total', 12, 2)->default(0)->comment('Total nilai obat yang dikeluarakan');
            $table->bigInteger('dispensed_at')->comment('Tanggal pengeluaran obat (Unix)');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_dispensings_company_deleted');
        });

        Schema::create('dispensing_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->nullable()->comment('UUID cabang pemilik detail dispensing');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik detail dispensing');
            $table->foreignUuid('dispensing_uuid')->constrained('dispensings', 'uuid')->cascadeOnDelete()->comment('ID Dokumen dispensing');
            $table->foreignUuid('product_uuid')->constrained('items', 'uuid')->cascadeOnDelete()->comment('ID Produk/Obat');
            $table->integer('qty')->default(1)->comment('Jumlah obat');
            $table->decimal('price_sell', 12, 2)->default(0)->comment('Harga jual saat diberikan');
            $table->decimal('subtotal', 12, 2)->default(0)->comment('Total nilai per item (qty x price)');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_dispensing_items_company_deleted');
        });

        // â”€â”€ Purchase Returns (retur ke supplier) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->nullable()->comment('UUID cabang pemilik retur pembelian');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik retur pembelian');
            $table->string('return_no')->unique()->comment('Nomor dokumen retur pembelian');
            $table->foreignUuid('purchase_uuid')->constrained('purchases', 'uuid')->cascadeOnDelete()->comment('ID Pembelian asal');
            $table->foreignUuid('supplier_uuid')->constrained('suppliers', 'uuid')->cascadeOnDelete()->comment('ID Supplier');
            $table->text('reason')->nullable()->comment('Alasan pengembalian barang');
            $table->decimal('total', 12, 2)->default(0)->comment('Total nilai barang yang diretur');
            $table->foreignUuid('returned_by_uuid')->nullable()->constrained('users', 'uuid')->nullOnDelete()->comment('ID Petugas pelaksana retur');
            $table->bigInteger('returned_at')->comment('Tanggal pelaksanaan retur (Unix)');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_purchase_returns_company_deleted');
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->nullable()->comment('UUID cabang pemilik detail retur');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik detail retur');
            $table->foreignUuid('return_uuid')->constrained('purchase_returns', 'uuid')->cascadeOnDelete()->comment('ID Dokumen retur');
            $table->foreignUuid('product_uuid')->constrained('items', 'uuid')->cascadeOnDelete()->comment('ID Produk yang diretur');
            $table->integer('qty')->default(1)->comment('Jumlah barang');
            $table->decimal('price', 12, 2)->default(0)->comment('Harga satuan saat diretur');
            $table->decimal('subtotal', 12, 2)->default(0)->comment('Total nilai per item');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_purchase_return_items_company_deleted');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_uuid');
            $table->dropColumn(['price', 'notes']);
        });
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('dispensing_items');
        Schema::dropIfExists('dispensings');
    }
};

