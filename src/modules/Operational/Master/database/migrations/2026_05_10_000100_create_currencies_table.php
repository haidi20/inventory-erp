<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel mata uang dan kurs.
 * Mencakup tabel currencies (mata_uang legacy) dan currency_rates (kurs_detail legacy).
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel master mata uang
            if (!Schema::hasTable('currencies')) {
                Schema::create('currencies', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik mata uang');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik mata uang');
                    $table->string('code', 3)->comment('Kode ISO mata uang (e.g. IDR)');
                    $table->string('name', 100)->comment('Nama mata uang');
                    $table->string('symbol', 10)->nullable()->comment('Simbol mata uang');
                    $table->boolean('is_base')->default(false)->comment('Penanda mata uang dasar perusahaan');
                    $table->unsignedTinyInteger('decimal_places')->default(0)->comment('Jumlah desimal pecahan');
                    $table->boolean('is_active')->default(true)->comment('Status aktif mata uang');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['company_uuid', 'code', 'deleted_at'], 'idx_currencies_code_unique_soft');
                    $table->index('company_uuid', 'idx_currencies_company_uuid');
                    $table->index('branch_uuid', 'idx_currencies_branch_uuid');
                    $table->index('deleted_at', 'idx_currencies_deleted_at');
                });
            }
            // Tabel riwayat kurs mata uang
            if (!Schema::hasTable('currency_rates')) {
                Schema::create('currency_rates', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik kurs');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik kurs');
                    $table->char('currency_uuid', 36)->comment('UUID mata uang yang dikurskan');
                    $table->decimal('rate', 18, 6)->default(1)->comment('Nilai kurs terhadap mata uang dasar');
                    $table->bigInteger('effective_date')->comment('Tanggal efektif kurs (Unix)');
                    $table->string('source', 50)->nullable()->comment('Sumber pembaruan kurs (mis. manual, bi)');
                    $table->text('note')->nullable()->comment('Catatan kurs');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['currency_uuid', 'effective_date', 'deleted_at'], 'idx_currency_rates_cur_date_unique_soft');
                    $table->index('company_uuid', 'idx_currency_rates_company_uuid');
                    $table->index('currency_uuid', 'idx_currency_rates_currency_uuid');
                    $table->index('deleted_at', 'idx_currency_rates_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('currency_rates');
            Schema::dropIfExists('currencies');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};

