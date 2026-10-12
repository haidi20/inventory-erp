<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel master satuan barang
            if (!Schema::hasTable('units')) {
                Schema::create('units', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->char('branch_uuid', 36)->nullable()->comment('ID cabang bila satuan bersifat khusus per cabang');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->string('code', 50)->comment('Kode satuan seperti PCS atau BOX');
                    $table->string('name', 100)->comment('Nama satuan yang tampil di sistem');
                    $table->string('symbol', 20)->nullable()->comment('Simbol singkat satuan');
                    $table->text('description')->nullable()->comment('Keterangan tambahan satuan');
                    $table->boolean('is_base_global')->nullable()->default(false)->comment('Penanda satuan dasar global');
                    $table->boolean('is_primary')->nullable()->default(false)->comment('Penanda satuan utama');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Status aktif satuan');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['branch_uuid', 'code', 'deleted_at'], 'idx_branch_unit');
                    $table->unique(['company_uuid', 'name', 'deleted_at'], 'idx_units_company_name');
                    $table->index('deleted_at', 'idx_units_deleted_at');
                });
            }
            // Tabel master level harga
            if (!Schema::hasTable('price_levels')) {
                Schema::create('price_levels', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->char('branch_uuid', 36)->nullable()->comment('ID cabang bila level harga khusus per cabang');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->string('code', 50)->comment('Kode level harga seperti UMUM atau BPJS');
                    $table->string('name', 100)->comment('Nama level harga yang tampil di sistem');
                    $table->text('description')->nullable()->comment('Keterangan tambahan level harga');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Status aktif level harga');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['branch_uuid', 'code', 'deleted_at'], 'idx_branch_price_level');
                    $table->index('deleted_at', 'idx_price_levels_deleted_at');
                });
            }
            // Tabel harga item per level
            if (!Schema::hasTable('item_prices')) {
                Schema::create('item_prices', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->char('branch_uuid', 36)->nullable()->comment('ID cabang pemilik harga item');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('ID item atau SKU yang diberi harga');
                    $table->char('price_level_uuid', 36)->comment('ID level harga item');
                    $table->decimal('price', 12, 2)->nullable()->default(0)->comment('Nilai harga jual untuk level terkait');
                    $table->decimal('price_buy', 12, 2)->nullable()->default(0);
                    $table->decimal('discount_amount', 12, 2)->nullable()->default(0);
                    $table->decimal('discount_percent', 8, 2)->nullable()->default(0);
                    $table->decimal('base_qty', 12, 4)->nullable()->default(1);
                    $table->bigInteger('started_at')->nullable()->comment('Waktu mulai harga berlaku');
                    $table->bigInteger('ended_at')->nullable()->comment('Waktu akhir harga berlaku');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Status aktif harga item');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['item_uuid', 'price_level_uuid', 'deleted_at'], 'idx_item_price');
                    $table->index('deleted_at', 'idx_item_prices_deleted_at');
                });
            }
            // Tabel satuan per item
            if (!Schema::hasTable('item_units')) {
                Schema::create('item_units', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->char('branch_uuid', 36)->nullable()->comment('ID cabang pemilik konversi satuan');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('ID item atau SKU yang memiliki satuan');
                    $table->char('unit_uuid', 36)->comment('ID satuan untuk item');
                    $table->decimal('conversion_ratio', 12, 4)->nullable()->default(1)->comment('Rasio konversi ke satuan dasar');
                    $table->text('note')->nullable();
                    $table->boolean('is_active')->nullable()->default(true);
                    $table->boolean('is_base')->nullable()->default(false)->comment('Penanda satuan dasar item');
                    $table->boolean('is_purchase_unit')->nullable()->default(false)->comment('Penanda satuan default pembelian');
                    $table->boolean('is_sales_unit')->nullable()->default(false)->comment('Penanda satuan default penjualan');
                    $table->string('barcode', 40)->nullable()->comment('Barcode khusus untuk satuan tertentu');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['item_uuid', 'unit_uuid', 'deleted_at'], 'idx_item_unit');
                    $table->index('deleted_at', 'idx_item_units_deleted_at');
                });
            }
            // Tabel mapping akun per item
            if (!Schema::hasTable('item_accounts')) {
                Schema::create('item_accounts', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->char('branch_uuid', 36)->nullable()->comment('ID cabang pemilik mapping akun item');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('ID item atau SKU yang dipetakan');
                    $table->string('account_role', 50)->comment('Peran akun seperti inventory, hpp, sales, purchase');
                    $table->char('account_uuid', 36)->comment('ID akun akuntansi untuk item');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['item_uuid', 'account_role', 'deleted_at'], 'idx_item_account');
                    $table->index('deleted_at', 'idx_item_accounts_deleted_at');
                });
            }
            // Tabel saldo stok per item dan gudang
            if (!Schema::hasTable('item_stocks')) {
                Schema::create('item_stocks', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->char('branch_uuid', 36)->comment('ID cabang pemilik saldo stok');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('ID item atau SKU yang disimpan');
                    $table->string('source_type', 50)
                        ->default('location')
                        ->comment('Tipe sumber stok: location, counter, vehicle, project');
                    $table->char('source_id', 36)
                        ->nullable()
                        ->comment('UUID sumber stok sesuai source_type');
                    $table->char('location_uuid', 36)
                        ->nullable()
                        ->comment('ID gudang tempat stok berada (deprecated; gunakan source_type/source_id)');
                    $table->decimal('on_hand_qty', 12, 4)->nullable()->default(0)->comment('Jumlah stok fisik yang tersedia');
                    $table->decimal('reserved_qty', 12, 4)->nullable()->default(0)->comment('Jumlah stok yang sedang dipesan');
                    $table->decimal('available_qty', 12, 4)->nullable()->default(0)->comment('Jumlah stok yang benar-benar tersedia');
                    $table->decimal('avg_cost', 12, 2)->nullable()->default(0)->comment('Biaya rata-rata item di gudang');
                    $table->bigInteger('last_movement_at')->nullable()->comment('Waktu mutasi stok terakhir');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['branch_uuid', 'item_uuid', 'location_uuid', 'deleted_at'], 'idx_item_stock');
                    $table->index(['source_type', 'source_id'], 'idx_item_stocks_source');
                    $table->index('deleted_at', 'idx_item_stocks_deleted_at');
                });
            }
            // Tabel saldo awal item
            if (!Schema::hasTable('item_opening_balances')) {
                Schema::create('item_opening_balances', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->char('branch_uuid', 36)->comment('ID cabang pemilik saldo awal');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('ID item atau SKU saldo awal');
                    $table->char('location_uuid', 36)->comment('ID gudang untuk saldo awal item');
                    $table->string('period_month', 7)->comment('Periode saldo awal (YYYY-MM)');
                    $table->decimal('opening_qty', 12, 4)->nullable()->default(0)->comment('Jumlah saldo awal item');
                    $table->decimal('opening_unit_cost', 12, 2)->nullable()->default(0)->comment('Biaya per unit untuk saldo awal');
                    $table->text('note')->nullable()->comment('Catatan saldo awal item');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['branch_uuid', 'item_uuid', 'location_uuid', 'period_month'], 'idx_opening_bal');
                    $table->index('deleted_at', 'idx_item_opening_balances_deleted_at');
                });
            }
            // Tabel detail item (serial/batch)
            if (!Schema::hasTable('item_details')) {
                Schema::create('item_details', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->char('branch_uuid', 36)->comment('ID cabang pemilik detail item');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('ID item atau SKU induk');
                    $table->char('location_uuid', 36)->nullable()->comment('ID gudang lokasi detail item');
                    $table->string('serial_no', 100)->nullable()->comment('Nomor serial item');
                    $table->string('batch_no', 100)->nullable()->comment('Nomor batch item');
                    $table->string('status', 50)->nullable()->default('available')->comment('Status detail item');
                    $table->text('note')->nullable()->comment('Catatan tambahan detail item');
                    $table->bigInteger('manufactured_at')->nullable()->comment('Waktu produksi item');
                    $table->bigInteger('expired_at')->nullable()->comment('Waktu kedaluwarsa item');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_item_details_deleted_at');
                });
            }
            // Tabel aturan diskon item
            if (!Schema::hasTable('item_discount_rules')) {
                Schema::create('item_discount_rules', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->char('branch_uuid', 36)->comment('ID cabang pemilik aturan diskon');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('ID item atau SKU yang diberi diskon');
                    $table->char('unit_uuid', 36)->nullable();
                    $table->char('price_level_uuid', 36)->nullable()->comment('ID level harga');
                    $table->string('discount_type', 20)->comment('Jenis diskon (percentage/amount)');
                    $table->decimal('min_qty', 12, 4)->nullable()->default(0)->comment('Jumlah minimum diskon');
                    $table->decimal('base_qty', 12, 4)->nullable()->default(1);
                    $table->decimal('max_qty', 12, 4)->nullable()->default(0)->comment('Jumlah maksimum diskon');
                    $table->decimal('amount', 12, 2)->nullable()->default(0)->comment('Nilai diskon');
                    $table->bigInteger('start_at')->nullable()->comment('Waktu mulai diskon');
                    $table->bigInteger('end_at')->nullable()->comment('Waktu akhir diskon');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Status aktif diskon');
                    $table->text('note')->nullable()->comment('Catatan diskon');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_item_discount_rules_deleted_at');
                });
            }
            // Tabel konversi satuan item
            if (!Schema::hasTable('item_conversions')) {
                Schema::create('item_conversions', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->char('branch_uuid', 36)->comment('ID cabang pemilik konversi item');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik konversi item');
                    $table->char('item_uuid', 36)->nullable()->comment('ID item bila konversi khusus item');
                    $table->char('from_unit_uuid', 36)->comment('ID satuan asal');
                    $table->char('to_unit_uuid', 36)->comment('ID satuan tujuan');
                    $table->decimal('conversion_ratio', 17, 5)->default(0)->comment('Nilai hasil konversi');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Status aktif konversi');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index(['branch_uuid', 'deleted_at'], 'idx_item_conversions_branch_deleted');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_item_conversions_company_deleted');
                    $table->index(
                        ['branch_uuid', 'company_uuid', 'from_unit_uuid', 'to_unit_uuid', 'deleted_at'],
                        'idx_item_conversions_scope_units'
                    );
                    $table->index('deleted_at', 'idx_item_conversions_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('item_conversions');
            Schema::dropIfExists('item_discount_rules');
            Schema::dropIfExists('item_details');
            Schema::dropIfExists('item_opening_balances');
            Schema::dropIfExists('item_stocks');
            Schema::dropIfExists('item_accounts');
            Schema::dropIfExists('item_units');
            Schema::dropIfExists('item_prices');
            Schema::dropIfExists('price_levels');
            Schema::dropIfExists('units');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};
