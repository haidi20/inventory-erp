<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel-tabel operasional utama:
 * registrations, encounters, sales, purchases, receivables, payables, dan stock movements.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang pendaftaran');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik data pendaftaran');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->string('queue_no')->nullable()->comment('Nomor antrean pasien');
            $table->string('payment_type')->default('mandiri')->index()->comment('Tipe pembayaran: mandiri, bpjs');
            $table->string('bpjs_no')->nullable()->comment('Nomor BPJS saat kunjungan');
            $table->string('poli')->nullable()->comment('Poli/Unit tujuan');
            $table->uuid('patient_uuid')->comment('UUID Pasien');
            $table->foreign('patient_uuid')->references('uuid')->on('patients')->cascadeOnDelete();
            $table->uuid('doctor_uuid')->nullable()->comment('UUID Dokter yang dituju');
            $table->foreign('doctor_uuid')->references('uuid')->on('employees')->nullOnDelete();
            $table->string('status')->default('waiting')->index()->comment('Status pendaftaran: waiting, in_progress, done, cancelled');
            $table->bigInteger('registered_at')->index()->comment('Waktu pendaftaran (Unix)');
            $table->text('notes')->nullable()->comment('Catatan pendaftaran');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_registrations_company_deleted');
        });

        Schema::create('encounters', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang pemeriksaan');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik data pemeriksaan');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('registration_uuid')->comment('UUID Pendaftaran terkait');
            $table->foreign('registration_uuid')->references('uuid')->on('registrations')->cascadeOnDelete();
            $table->uuid('patient_uuid')->comment('UUID Pasien');
            $table->foreign('patient_uuid')->references('uuid')->on('patients')->cascadeOnDelete();
            $table->uuid('doctor_uuid')->nullable()->comment('UUID Dokter pemeriksa');
            $table->foreign('doctor_uuid')->references('uuid')->on('employees')->nullOnDelete();
            $table->text('complaint')->nullable()->comment('Keluhan utama pasien');
            $table->text('diagnosis')->nullable()->comment('Diagnosis/Asesmen narasi');
            $table->text('notes')->nullable()->comment('Catatan tambahan pemeriksaan');
            $table->string('status')->default('draft')->index()->comment('Status records: draft, posted');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index('created_at');
            $table->index(['company_uuid', 'deleted_at'], 'idx_encounters_company_deleted');
        });

        Schema::create('encounter_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik detail pemeriksaan');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('encounter_uuid')->comment('UUID Pemeriksaan terkait');
            $table->foreign('encounter_uuid')->references('uuid')->on('encounters')->cascadeOnDelete();
            $table->enum('item_type', ['service', 'product'])->comment('Tipe item: layanan atau produk');
            $table->uuid('item_uuid')->comment('UUID referensi ke tabel services atau products');
            $table->integer('qty')->default(1)->comment('Jumlah item yang diberikan');
            $table->decimal('price', 12, 2)->default(0)->comment('Harga satuan saat pemeriksaan');
            $table->decimal('subtotal', 12, 2)->default(0)->comment('Total per item');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['item_type', 'item_uuid']);
            $table->index(['company_uuid', 'deleted_at'], 'idx_encounter_items_company_deleted');
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang penjualan');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik transaksi penjualan');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('registration_uuid')->nullable()->comment('UUID Pendaftaran terkait');
            $table->foreign('registration_uuid')->references('uuid')->on('registrations')->nullOnDelete();
            $table->string('sale_no')->comment('Nomor faktur penjualan');
            $table->unique(['branch_uuid', 'sale_no']);
            $table->uuid('patient_uuid')->nullable()->comment('UUID Pasien pembeli');
            $table->foreign('patient_uuid')->references('uuid')->on('patients')->nullOnDelete();
            $table->uuid('cashier_uuid')->nullable()->comment('UUID Kasir pelaksana');
            $table->foreign('cashier_uuid')->references('uuid')->on('employees')->nullOnDelete();
            $table->decimal('total_amount', 12, 2)->default(0)->comment('Total tagihan faskes');
            $table->decimal('paid', 12, 2)->default(0)->comment('Jumlah yang dibayarkan');
            $table->decimal('change', 12, 2)->default(0)->comment('Uang kembalian');
            $table->string('payment_status')->default('paid')->index()->comment('Status bayar: paid, unpaid, partial');
            $table->string('payment_method')->nullable()->comment('Metode pembayaran');
            $table->bigInteger('sale_at')->index()->comment('Waktu transaksi penjualan (Unix)');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_sales_company_deleted');
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik detail penjualan');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('sale_uuid')->comment('UUID Faktur terkait');
            $table->foreign('sale_uuid')->references('uuid')->on('sales')->cascadeOnDelete();
            $table->enum('item_type', ['service', 'product'])->comment('Tipe item: layanan atau produk');
            $table->uuid('item_uuid')->comment('UUID referensi ke tabel services atau products');
            $table->integer('qty')->default(1)->comment('Jumlah item terjual');
            $table->decimal('price', 12, 2)->default(0)->comment('Harga jual saat transaksi');
            $table->decimal('subtotal', 12, 2)->default(0)->comment('Total per item terjual');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['item_type', 'item_uuid']);
            $table->index(['company_uuid', 'deleted_at'], 'idx_sale_items_company_deleted');
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang pembelian');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik transaksi pembelian');
            $table->string('invoice_no')->comment('Nomor faktur dari supplier');
            $table->unique(['branch_uuid', 'invoice_no']);
            $table->uuid('supplier_uuid')->comment('UUID Supplier');
            $table->foreign('supplier_uuid')->references('uuid')->on('suppliers')->cascadeOnDelete();
            $table->uuid('purchase_order_uuid')->nullable()->index()->comment('UUID referensi ke Pesanan Pembelian (PO)');
            $table->bigInteger('received_at')->nullable()->comment('Waktu barang diterima fisik (Unix)');
            $table->uuid('received_by_uuid')->nullable()->comment('UUID User penerima barang');
            $table->foreign('received_by_uuid')->references('uuid')->on('users')->nullOnDelete();
            $table->decimal('total', 12, 2)->default(0)->comment('Total tagihan supplier');
            $table->decimal('paid', 12, 2)->default(0)->comment('Jumlah yang sudah dibayarkan');
            $table->string('status')->default('unpaid')->index()->comment('Status bayar ke supplier: paid, unpaid, partial');
            $table->bigInteger('purchase_date')->index()->comment('Tanggal faktur pembelian (Unix)');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index('branch_uuid');
            $table->index(['company_uuid', 'deleted_at'], 'idx_purchases_company_deleted');
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik transaksi pembelian');
            $table->uuid('purchase_uuid')->comment('UUID Faktur terkait');
            $table->foreign('purchase_uuid')->references('uuid')->on('purchases')->cascadeOnDelete();
            $table->uuid('product_uuid')->comment('UUID Produk yang dibeli');
            $table->foreign('product_uuid')->references('uuid')->on('items')->cascadeOnDelete();
            $table->uuid('unit_uuid')->nullable()->index()->comment('UUID Satuan barang');
            $table->integer('qty')->default(1)->comment('Jumlah barang yang dibeli');
            $table->decimal('price', 12, 2)->default(0)->comment('Harga beli per unit');
            $table->decimal('subtotal', 12, 2)->default(0)->comment('Total per item');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index('branch_uuid');
            $table->index(['company_uuid', 'deleted_at'], 'idx_purchase_items_company_deleted');
        });

        Schema::create('product_purchases', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('purchase_uuid')->index()->comment('UUID header pembelian');
            $table->uuid('product_uuid')->index()->comment('UUID produk yang dibeli');
            $table->uuid('product_batch_uuid')->nullable()->comment('UUID batch produk bila tersedia');
            $table->uuid('variant_uuid')->nullable()->comment('UUID varian produk bila tersedia');
            $table->text('imei_number')->nullable()->comment('Nomor IMEI untuk produk serial');
            $table->decimal('qty', 15, 4)->default(0)->comment('Jumlah beli');
            $table->decimal('recieved', 15, 4)->default(0)->comment('Jumlah diterima');
            $table->uuid('purchase_unit_uuid')->comment('UUID satuan pembelian');
            $table->decimal('net_unit_cost', 18, 2)->default(0)->comment('Harga bersih per unit');
            $table->decimal('discount', 18, 2)->default(0)->comment('Nilai diskon');
            $table->decimal('tax_rate', 8, 2)->default(0)->comment('Persentase pajak');
            $table->decimal('tax', 18, 2)->default(0)->comment('Nilai pajak');
            $table->decimal('total', 18, 2)->default(0)->comment('Nilai total baris pembelian');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index('deleted_at', 'idx_product_purchases_deleted_at');
        });

        Schema::create('product_quotation', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('quotation_uuid')->index()->comment('UUID header quotation');
            $table->uuid('product_uuid')->index()->comment('UUID produk yang di-quote');
            $table->uuid('product_batch_uuid')->nullable()->comment('UUID batch produk bila tersedia');
            $table->uuid('variant_uuid')->nullable()->comment('UUID varian produk bila tersedia');
            $table->decimal('qty', 15, 4)->default(0)->comment('Jumlah quotation');
            $table->uuid('sale_unit_uuid')->comment('UUID satuan penjualan');
            $table->decimal('net_unit_price', 18, 2)->default(0)->comment('Harga bersih per unit');
            $table->decimal('discount', 18, 2)->default(0)->comment('Nilai diskon');
            $table->decimal('tax_rate', 8, 2)->default(0)->comment('Persentase pajak');
            $table->decimal('tax', 18, 2)->default(0)->comment('Nilai pajak');
            $table->decimal('total', 18, 2)->default(0)->comment('Nilai total baris quotation');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index('deleted_at', 'idx_product_quotation_deleted_at');
        });

        Schema::create('product_sales', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('sale_uuid')->index()->comment('UUID header penjualan');
            $table->uuid('product_uuid')->index()->comment('UUID produk yang dijual');
            $table->uuid('product_batch_uuid')->nullable()->comment('UUID batch produk bila tersedia');
            $table->uuid('variant_uuid')->nullable()->comment('UUID varian produk bila tersedia');
            $table->text('imei_number')->nullable()->comment('Nomor IMEI untuk produk serial');
            $table->decimal('qty', 15, 4)->default(0)->comment('Jumlah jual');
            $table->decimal('return_qty', 15, 4)->default(0)->comment('Jumlah retur');
            $table->uuid('sale_unit_uuid')->comment('UUID satuan penjualan');
            $table->decimal('net_unit_price', 18, 2)->default(0)->comment('Harga bersih per unit');
            $table->decimal('discount', 18, 2)->default(0)->comment('Nilai diskon');
            $table->decimal('tax_rate', 8, 2)->default(0)->comment('Persentase pajak');
            $table->decimal('tax', 18, 2)->default(0)->comment('Nilai pajak');
            $table->decimal('total', 18, 2)->default(0)->comment('Nilai total baris penjualan');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index('deleted_at', 'idx_product_sales_deleted_at');
        });

        Schema::create('receivables', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang piutang');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik piutang');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->string('reference_type')->comment('Jenis referensi: sale, bpjs, dll');
            $table->uuid('reference_uuid')->comment('UUID referensi ke tabel terkait');
            $table->decimal('amount', 12, 2)->default(0)->comment('Total piutang');
            $table->decimal('paid', 12, 2)->default(0)->comment('Jumlah yang sudah tertagih');
            $table->string('status')->default('unpaid')->index()->comment('Status tagihan: paid, unpaid, partial');
            $table->bigInteger('due_date')->nullable()->index()->comment('Tanggal jatuh tempo piutang (Unix)');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['reference_type', 'reference_uuid']);
            $table->index(['company_uuid', 'deleted_at'], 'idx_receivables_company_deleted');
        });

        Schema::create('payables', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang hutang');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik hutang');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->string('reference_type')->comment('Jenis referensi: purchase, dll');
            $table->uuid('reference_uuid')->comment('UUID referensi ke tabel terkait');
            $table->decimal('amount', 12, 2)->default(0)->comment('Total hutang');
            $table->decimal('paid', 12, 2)->default(0)->comment('Jumlah yang sudah dibayarkan');
            $table->string('status')->default('unpaid')->index()->comment('Status kewajiban: paid, unpaid, partial');
            $table->bigInteger('due_date')->nullable()->index()->comment('Tanggal jatuh tempo hutang (Unix)');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['reference_type', 'reference_uuid']);
            $table->index(['company_uuid', 'deleted_at'], 'idx_payables_company_deleted');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang mutasi stok');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('company_uuid')->nullable()->comment('UUID Perusahaan pemilik transaksi');
            $table->uuid('product_uuid')->comment('UUID Produk yang bermutasi');
            $table->foreign('product_uuid')->references('uuid')->on('items')->cascadeOnDelete();
            $table->uuid('location_uuid')->nullable()->comment('UUID Gudang mutasi stok');
            $table->integer('qty')->default(0)->comment('Jumlah perubahan (positif jika masuk, negatif jika keluar)');
            $table->string('type')->index()->comment('Tipe mutasi: in, out, adjust');
            $table->string('reference_type')->nullable()->comment('Jenis referensi transaksi asal');
            $table->uuid('reference_uuid')->nullable()->comment('UUID referensi transaksi asal');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['reference_type', 'reference_uuid']);
            $table->index(['company_uuid', 'deleted_at'], 'idx_stock_movements_company_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('payables');
        Schema::dropIfExists('receivables');
        Schema::dropIfExists('product_sales');
        Schema::dropIfExists('product_quotation');
        Schema::dropIfExists('product_purchases');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('encounter_items');
        Schema::dropIfExists('encounters');
        Schema::dropIfExists('registrations');
    }
};


