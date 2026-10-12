<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel header bill of materials
            if (!Schema::hasTable('boms')) {
                Schema::create('boms', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy dari aplikasi lama');
                    $table->char('branch_uuid', 36)->comment('ID cabang pemilik formula');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->string('document_no', 255)->comment('Nomor referensi formula (No Formula)');
                    $table->char('item_uuid', 36)->comment('ID item hasil produksi (Finished Good)');
                    $table->char('unit_uuid', 36)->comment('ID satuan hasil produksi');
                    $table->decimal('quantity', 15, 4)->nullable()->default(1)->comment('Jumlah hasil produksi yang direncanakan');
                    $table->bigInteger('start_date')->nullable()->comment('Tanggal mulai berlaku formula (Unix timestamp)');
                    $table->bigInteger('end_date')->nullable()->comment('Tanggal berakhir berlaku formula (Unix timestamp)');
                    $table->string('reference_no', 255)->nullable()->comment('Nomor referensi dokumen luar (No Ref)');
                    $table->text('note')->nullable()->comment('Keterangan formula');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Status keaktifan formula');
                    $table->boolean('is_primary')->nullable()->default(false)->comment('Penanda jika ini adalah formula utama');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_boms_deleted_at');
                });
            }
            // Tabel detail bahan bill of materials
            if (!Schema::hasTable('bom_details')) {
                Schema::create('bom_details', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key detail record');
                    $table->char('bom_uuid', 36)->comment('ID header formula');
                    $table->char('item_uuid', 36)->comment('ID bahan baku');
                    $table->char('unit_uuid', 36)->comment('ID satuan bahan baku');
                    $table->decimal('quantity', 15, 4)->nullable()->default(0)->comment('Jumlah penggunaan bahan');
                    $table->decimal('ratio', 15, 4)->nullable()->default(0)->comment('Rasio penggunaan bahan');
                    $table->text('note')->nullable()->comment('Catatan per bahan');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_bom_details_deleted_at');
                });
            }
            // Tabel biaya tambahan bill of materials
            if (!Schema::hasTable('bom_costs')) {
                Schema::create('bom_costs', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key cost record');
                    $table->char('bom_uuid', 36)->comment('ID header formula');
                    $table->char('item_uuid', 36)->comment('ID item jasa/biaya');
                    $table->decimal('amount', 19, 4)->nullable()->default(0)->comment('Nilai biaya satuan');
                    $table->decimal('total', 19, 4)->nullable()->default(0)->comment('Total biaya');
                    $table->text('note')->nullable()->comment('Catatan biaya');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_bom_costs_deleted_at');
                });
            }
            // Tabel header transaksi perakitan
            if (!Schema::hasTable('assemblies')) {
                Schema::create('assemblies', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID transaksi perakitan');
                    $table->char('branch_uuid', 36)->comment('UUID cabang pemilik transaksi');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik transaksi');
                    $table->string('document_no', 50)->unique()->comment('Nomor dokumen perakitan');
                    $table->bigInteger('date')->comment('Tanggal transaksi perakitan (unix timestamp)');
                    $table->string('status', 20)->default('draft')->comment('Status transaksi: draft atau posted');
                    $table->char('item_uuid', 36)->comment('UUID item hasil rakitan');
                    $table->char('unit_uuid', 36)->comment('UUID satuan hasil rakitan');
                    $table->char('location_uuid', 36)->comment('UUID gudang tujuan hasil rakitan');
                    $table->decimal('quantity', 12, 4)->comment('Jumlah hasil rakitan');
                    $table->decimal('cost', 18, 2)->default(0)->comment('Total biaya produksi/HPP');
                    $table->text('notes')->nullable()->comment('Catatan transaksi perakitan');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('branch_uuid', 'idx_assemblies_branch_uuid');
                    $table->index('company_uuid', 'idx_assemblies_company_uuid');
                    $table->index('status', 'idx_assemblies_status');
                    $table->index('date', 'idx_assemblies_date');
                    $table->index('item_uuid', 'idx_assemblies_item_uuid');
                    $table->index('location_uuid', 'idx_assemblies_location_uuid');
                    $table->index('deleted_at', 'idx_assemblies_deleted_at');
                });
            }
            // Tabel detail bahan transaksi perakitan
            if (!Schema::hasTable('assembly_items')) {
                Schema::create('assembly_items', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID detail perakitan');
                    $table->char('assembly_uuid', 36)->comment('UUID header transaksi perakitan');
                    $table->char('item_uuid', 36)->comment('UUID item bahan baku');
                    $table->char('unit_uuid', 36)->comment('UUID satuan bahan baku');
                    $table->decimal('quantity', 12, 4)->comment('Jumlah bahan baku');
                    $table->decimal('cost', 18, 2)->default(0)->comment('Biaya satuan bahan baku');
                    $table->decimal('subtotal', 18, 2)->default(0)->comment('Subtotal biaya bahan baku');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik transaksi');
                    $table->char('company_uuid', 36)->nullable()->comment('UUID perusahaan pemilik transaksi');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('assembly_uuid', 'idx_assembly_items_assembly_uuid');
                    $table->index('item_uuid', 'idx_assembly_items_item_uuid');
                    $table->index('branch_uuid', 'idx_assembly_items_branch_uuid');
                    $table->index('company_uuid', 'idx_assembly_items_company_uuid');
                    $table->index('deleted_at', 'idx_assembly_items_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('assembly_items');
            Schema::dropIfExists('assemblies');
            Schema::dropIfExists('bom_costs');
            Schema::dropIfExists('bom_details');
            Schema::dropIfExists('boms');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};



