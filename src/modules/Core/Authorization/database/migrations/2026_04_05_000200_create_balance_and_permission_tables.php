<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel izin gudang per pengguna
            if (!Schema::hasTable('user_warehouses')) {
                Schema::create('user_warehouses', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->char('user_uuid', 36)->comment('ID pengguna yang diberi izin');
                    $table->char('location_uuid', 36)->comment('ID gudang yang diizinkan');
                    $table->boolean('is_primary')->nullable()->default(false)->comment('Penanda apakah gudang utama pengguna');
                    $table->char('branch_uuid', 36)->comment('ID cabang pemilik record');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['user_uuid', 'location_uuid', 'deleted_at'], 'idx_user_location');
                    $table->index('deleted_at', 'idx_user_warehouses_deleted_at');
                });
            }
            // Tabel saldo akun akuntansi per periode
            if (!Schema::hasTable('account_balances')) {
                Schema::create('account_balances', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->char('account_uuid', 36)->comment('ID akun G/L');
                    $table->char('branch_uuid', 36)->comment('ID cabang pemilik saldo');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->string('period_month', 7)->comment('Periode saldo (YYYY-MM)');
                    $table->decimal('opening_debit', 20, 8)->nullable()->default(0)->comment('Saldo awal debet');
                    $table->decimal('opening_credit', 20, 8)->nullable()->default(0)->comment('Saldo awal kredit');
                    $table->decimal('total_debit', 20, 8)->nullable()->default(0)->comment('Total mutasi debet periode ini');
                    $table->decimal('total_credit', 20, 8)->nullable()->default(0)->comment('Total mutasi kredit periode ini');
                    $table->decimal('closing_debit', 20, 8)->nullable()->default(0)->comment('Saldo akhir debet');
                    $table->decimal('closing_credit', 20, 8)->nullable()->default(0)->comment('Saldo akhir kredit');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['account_uuid', 'branch_uuid', 'period_month'], 'idx_acc_bal');
                    $table->index('deleted_at', 'idx_account_balances_deleted_at');
                });
            }
            // Tabel saldo piutang/hutang per person
            if (!Schema::hasTable('person_balances')) {
                Schema::create('person_balances', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->char('person_uuid', 36)->comment('ID pelanggan atau pemasok');
                    $table->char('branch_uuid', 36)->comment('ID cabang pemilik saldo');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->string('period_month', 7)->comment('Periode saldo (YYYY-MM)');
                    $table->integer('period_name')->nullable()->comment('Format YYYYMM');
                    $table->decimal('opening_balance', 20, 8)->nullable()->default(0)->comment('Saldo awal AR/AP');
                    $table->decimal('total_invoice', 20, 8)->nullable()->default(0)->comment('Total tagihan baru periode ini');
                    $table->decimal('total_payment', 20, 8)->nullable()->default(0)->comment('Total pembayaran periode ini');
                    $table->decimal('closing_balance', 20, 8)->nullable()->default(0)->comment('Saldo akhir AR/AP');
                    $table->decimal('saldo_awal', 20, 8)->nullable()->default(0)->comment('Penjelasan kolom saldo_awal.');
                    $table->decimal('saldo_hutang', 20, 8)->nullable()->default(0)->comment('Penjelasan kolom saldo_hutang.');
                    $table->char('location_uuid', 36)->nullable()->comment('Penjelasan kolom location_uuid.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['person_uuid', 'branch_uuid', 'period_month'], 'idx_person_bal');
                    $table->index('deleted_at', 'idx_person_balances_deleted_at');
                });
            }
            // Tabel transaksi saldo awal
            if (!Schema::hasTable('opening_balance_records')) {
                Schema::create('opening_balance_records', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->char('branch_uuid', 36)->comment('ID cabang lokasi saldo awal');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->string('transaction_no', 50)->comment('Nomor dokumen saldo awal');
                    $table->bigInteger('transaction_date')->nullable()->comment('Tanggal pencatatan saldo awal');
                    $table->string('period_month', 7)->comment('Periode akuntansi terdampak YYYY-MM');
                    $table->string('type', 20)->comment('Jenis saldo awal (STOCK/ACCOUNT/PERSON)');
                    $table->text('note')->nullable()->comment('Catatan tambahan');
                    $table->boolean('is_posted')->nullable()->default(false)->comment('Status posting ke buku besar/kartu stok');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_opening_balance_records_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('opening_balance_records');
            Schema::dropIfExists('person_balances');
            Schema::dropIfExists('account_balances');
            Schema::dropIfExists('user_warehouses');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};



