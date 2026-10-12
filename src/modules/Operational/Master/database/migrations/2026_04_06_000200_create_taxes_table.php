<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel master pajak PPN/PPH
            if (!Schema::hasTable('taxes')) {
                Schema::create('taxes', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->string('tax_code', 10)->default('')->comment('Kode unik pajak (contoh: PPN11, PPH23)');
                    $table->decimal('percentage', 5, 2)->nullable()->default(0.00)->comment('Persentase nilai pajak');
                    $table->integer('legacy_tax_type_uuid')->nullable()->default(0)->comment('ID tipe pajak dari sistem legacy');
                    $table->integer('legacy_sale_account_uuid')->nullable()->default(0)->comment('ID COA Penjualan (Hutang Pajak)');
                    $table->integer('legacy_purchase_account_uuid')->nullable()->default(0)->comment('ID COA Pembelian (Piutang Pajak)');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Status aktif');
                    $table->boolean('is_primary')->nullable()->default(false)->comment('Penanda pajak utama');
                    $table->boolean('is_pph')->nullable()->default(false)->comment('Penanda apakah ini PPH');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('branch_uuid', 36)->nullable()->comment('Penjelasan kolom branch_uuid.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    
                    $table->unique(['branch_uuid', 'tax_code', 'deleted_at'], 'idx_taxes_unique_soft');
                    $table->index('branch_uuid', 'idx_taxes_branch_uuid');
                    $table->index('company_uuid', 'idx_taxes_company_uuid');
                    $table->index('deleted_at', 'idx_taxes_deleted_at');
                    $table->index(['company_uuid', 'branch_uuid', 'deleted_at'], 'idx_taxes_company_branch_deleted');
        });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('taxes');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};


