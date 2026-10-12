<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel header pesanan penjualan
            if (!Schema::hasTable('sales_orders')) {
                Schema::create('sales_orders', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('UUID unik record');
                    $table->bigInteger('id')->nullable()->comment('Penjelasan kolom id.');
                    $table->integer('legacy_id')->nullable()->comment('ID lama dari sistem legacy');
                    $table->char('branch_uuid', 36)->comment('ID Cabang');
                    $table->char('company_uuid', 36)->comment('ID Perusahaan');
                    $table->char('person_uuid', 36)->comment('ID Customer');
                    $table->string('sales_order_no', 50)->comment('No Sales Order');
                    $table->bigInteger('sales_order_date')->comment('Tgl Pesanan (Unix timestamp)');
                    $table->char('location_uuid', 36)->comment('ID Gudang');
                    $table->char('payment_term_uuid', 36)->comment('ID Termin');
                    $table->text('description')->nullable()->comment('Penjelasan kolom description.');
                    $table->string('status', 20)->nullable()->default('open')->comment('Penjelasan kolom status.');
                    $table->decimal('total_amount', 18, 2)->nullable()->comment('Penjelasan kolom total_amount.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_sales_orders_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_so_company_deleted');
                    $table->index(['branch_uuid', 'deleted_at'], 'idx_so_branch_deleted');
                    $table->index('person_uuid', 'idx_so_person_uuid');
                    $table->index('sales_order_date', 'idx_so_date');
                    $table->index('status', 'idx_so_status');
                    $table->unique(['branch_uuid', 'sales_order_no', 'deleted_at'], 'idx_so_no_unique_soft');
                });
            }
            // Tabel detail pesanan penjualan
            if (!Schema::hasTable('sales_order_details')) {
                Schema::create('sales_order_details', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->char('sales_order_uuid', 36)->comment('Penjelasan kolom sales_order_uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('Penjelasan kolom item_uuid.');
                    $table->decimal('qty', 12, 4)->comment('Penjelasan kolom qty.');
                    $table->decimal('price', 18, 2)->comment('Penjelasan kolom price.');
                    $table->decimal('discount', 18, 2)->nullable()->default(0)->comment('Penjelasan kolom discount.');
                    $table->decimal('total', 18, 2)->comment('Penjelasan kolom total.');
                    $table->text('description')->nullable()->comment('Penjelasan kolom description.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_sales_order_details_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_so_details_company_deleted');
                    $table->index('sales_order_uuid', 'idx_so_details_so_uuid');
                    $table->index('item_uuid', 'idx_so_details_item_uuid');
                });
            }
            // Tabel header pengiriman barang
            if (!Schema::hasTable('delivery_orders')) {
                Schema::create('delivery_orders', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->bigInteger('id')->nullable()->comment('Penjelasan kolom id.');
                    $table->integer('legacy_id')->nullable()->comment('Penjelasan kolom legacy_id.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('sales_order_uuid', 36)->nullable()->comment('Penjelasan kolom sales_order_uuid.');
                    $table->char('person_uuid', 36)->comment('Penjelasan kolom person_uuid.');
                    $table->string('delivery_no', 50)->comment('Penjelasan kolom delivery_no.');
                    $table->bigInteger('delivery_date')->comment('Tgl Pengiriman (Unix timestamp)');
                    $table->char('location_uuid', 36)->comment('Penjelasan kolom location_uuid.');
                    $table->text('description')->nullable()->comment('Penjelasan kolom description.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_delivery_orders_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_do_company_deleted');
                    $table->index(['branch_uuid', 'deleted_at'], 'idx_do_branch_deleted');
                    $table->index('person_uuid', 'idx_do_person_uuid');
                    $table->index('sales_order_uuid', 'idx_do_so_uuid');
                    $table->index('delivery_date', 'idx_do_date');
                    $table->unique(['branch_uuid', 'delivery_no', 'deleted_at'], 'idx_do_no_unique_soft');
                });
            }
            // Tabel detail pengiriman barang
            if (!Schema::hasTable('delivery_order_details')) {
                Schema::create('delivery_order_details', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->char('delivery_order_uuid', 36)->comment('Penjelasan kolom delivery_order_uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('Penjelasan kolom item_uuid.');
                    $table->decimal('qty', 12, 4)->comment('Penjelasan kolom qty.');
                    $table->text('description')->nullable()->comment('Penjelasan kolom description.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_delivery_order_details_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_do_details_company_deleted');
                    $table->index('delivery_order_uuid', 'idx_do_details_do_uuid');
                    $table->index('item_uuid', 'idx_do_details_item_uuid');
                });
            }
            // Tabel header faktur penjualan
            if (!Schema::hasTable('sales_invoices')) {
                Schema::create('sales_invoices', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->bigInteger('id')->nullable()->comment('Penjelasan kolom id.');
                    $table->integer('legacy_id')->nullable()->comment('Penjelasan kolom legacy_id.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('sales_order_uuid', 36)->nullable()->comment('Penjelasan kolom sales_order_uuid.');
                    $table->char('delivery_order_uuid', 36)->nullable()->comment('Penjelasan kolom delivery_order_uuid.');
                    $table->char('person_uuid', 36)->comment('Penjelasan kolom person_uuid.');
                    $table->string('invoice_no', 50)->comment('Penjelasan kolom invoice_no.');
                    $table->bigInteger('invoice_date')->comment('Tgl Faktur (Unix timestamp)');
                    $table->char('payment_term_uuid', 36)->comment('Penjelasan kolom payment_term_uuid.');
                    $table->char('location_uuid', 36)->comment('Penjelasan kolom location_uuid.');
                    $table->char('payment_account_1_uuid', 36)->nullable()->comment('Penjelasan kolom payment_account_1_id.');
                    $table->char('payment_account_2_uuid', 36)->nullable()->comment('Penjelasan kolom payment_account_2_id.');
                    $table->decimal('total_amount', 18, 2)->nullable()->comment('Penjelasan kolom total_amount.');
                    $table->string('status', 20)->nullable()->default('draft')->comment('Penjelasan kolom status.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_sales_invoices_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_si_company_deleted');
                    $table->index(['branch_uuid', 'deleted_at'], 'idx_si_branch_deleted');
                    $table->index('person_uuid', 'idx_si_person_uuid');
                    $table->index('sales_order_uuid', 'idx_si_so_uuid');
                    $table->index('delivery_order_uuid', 'idx_si_do_uuid');
                    $table->index('invoice_date', 'idx_si_date');
                    $table->index('status', 'idx_si_status');
                    $table->unique(['branch_uuid', 'invoice_no', 'deleted_at'], 'idx_si_no_unique_soft');
                    $table->index(['company_uuid', 'branch_uuid', 'deleted_at'], 'idx_sales_invoices_company_branch_deleted');
        });
            }
            // Tabel detail faktur penjualan
            if (!Schema::hasTable('sales_invoice_details')) {
                Schema::create('sales_invoice_details', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->char('sales_invoice_uuid', 36)->comment('Penjelasan kolom sales_invoice_uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('item_uuid', 36)->comment('Penjelasan kolom item_uuid.');
                    $table->decimal('qty', 12, 4)->comment('Penjelasan kolom qty.');
                    $table->decimal('price', 18, 2)->comment('Penjelasan kolom price.');
                    $table->decimal('discount', 18, 2)->nullable()->default(0)->comment('Penjelasan kolom discount.');
                    $table->decimal('total', 18, 2)->comment('Penjelasan kolom total.');
                    $table->char('income_account_uuid', 36)->nullable()->comment('Penjelasan kolom income_account_uuid.');
                    $table->decimal('hpp', 18, 2)->nullable()->default(0)->comment('Penjelasan kolom hpp.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_sales_invoice_details_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_si_details_company_deleted');
                    $table->index('sales_invoice_uuid', 'idx_si_details_si_uuid');
                    $table->index('item_uuid', 'idx_si_details_item_uuid');
                });
            }
            // Tabel aging piutang penjualan
            if (!Schema::hasTable('sales_invoice_agings')) {
                Schema::create('sales_invoice_agings', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->char('sales_invoice_uuid', 36)->comment('Penjelasan kolom sales_invoice_uuid.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->bigInteger('due_date')->comment('Tgl Jatuh Tempo (Unix timestamp)');
                    $table->decimal('amount', 18, 2)->comment('Penjelasan kolom amount.');
                    $table->decimal('remaining', 18, 2)->comment('Penjelasan kolom remaining.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_sales_invoice_agings_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_si_agings_company_deleted');
                    $table->index('sales_invoice_uuid', 'idx_si_agings_si_uuid');
                    $table->index('due_date', 'idx_si_agings_due_date');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('sales_invoice_agings');
            Schema::dropIfExists('sales_invoice_details');
            Schema::dropIfExists('sales_invoices');
            Schema::dropIfExists('delivery_order_details');
            Schema::dropIfExists('delivery_orders');
            Schema::dropIfExists('sales_order_details');
            Schema::dropIfExists('sales_orders');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};




