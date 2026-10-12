<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel header pindah antar gudang
            if (!Schema::hasTable('stock_transfers')) {
                Schema::create('stock_transfers', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID');
                    $table->char('branch_uuid', 36)->comment('UUID Cabang');
                    $table->char('company_uuid', 36)->comment('UUID Perusahaan');
                    $table->string('no_pag', 50)->comment('Nomor PAG');
                    $table->bigInteger('date')->comment('Tanggal perpindahan (Unix Timestamp)');
                    $table->char('source_location_uuid', 36)->comment('UUID Gudang Asal');
                    $table->char('destination_location_uuid', 36)->comment('UUID Gudang Tujuan');
                    $table->string('status', 20)->nullable()->default('draft')->comment('Status (draft, posted, cancelled)');
                    $table->text('notes')->nullable()->comment('Catatan');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus');
                    $table->index('branch_uuid', 'idx_stock_transfers_branch_uuid');
                    $table->index('deleted_at', 'idx_stock_transfers_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_st_company_deleted');
                    $table->index('source_location_uuid', 'idx_st_source_location');
                    $table->index('destination_location_uuid', 'idx_st_dest_location');
                });
            }
            // Tabel detail pindah antar gudang
            if (!Schema::hasTable('stock_transfer_items')) {
                Schema::create('stock_transfer_items', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID');
                    $table->char('branch_uuid', 36)->comment('UUID Cabang');
                    $table->char('company_uuid', 36)->comment('UUID Perusahaan');
                    $table->char('transfer_uuid', 36)->comment('UUID Header PAG');
                    $table->char('product_uuid', 36)->comment('UUID Produk');
                    $table->char('unit_uuid', 36)->comment('UUID Satuan');
                    $table->decimal('qty', 12, 4)->comment('Jumlah');
                    $table->text('note')->nullable()->comment('Catatan item transfer');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->index('branch_uuid', 'idx_stock_transfer_items_branch_uuid');
                    $table->index('deleted_at', 'idx_stock_transfer_items_deleted_at');
                    $table->index(['company_uuid', 'deleted_at'], 'idx_sti_company_deleted');
                    $table->index('transfer_uuid', 'idx_sti_transfer_uuid');
                    $table->index('product_uuid', 'idx_sti_product_uuid');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('stock_transfer_items');
            Schema::dropIfExists('stock_transfers');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};




