<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel giro/cek (master keuangan).
 * Mencakup tabel cheques sebagai register giro masuk (chequein) dan keluar (chequeout).
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel register giro/cek
            if (!Schema::hasTable('cheques')) {
                Schema::create('cheques', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('id')->nullable()->comment('ID legacy numeric dari aplikasi lain');
                    $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik giro');
                    $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik giro');
                    $table->string('document_no', 40)->comment('Nomor dokumen giro');
                    $table->enum('direction', ['in', 'out'])->default('in')->comment('Arah giro: in (chequein) / out (chequeout)');
                    $table->string('cheque_no', 50)->nullable()->comment('Nomor seri giro/cek');
                    $table->char('bank_account_uuid', 36)->nullable()->comment('UUID akun bank penerbit/penyetor');
                    $table->char('bank_account_dest_uuid', 36)->nullable()->comment('UUID akun bank tujuan');
                    $table->char('person_uuid', 36)->nullable()->comment('UUID customer/supplier terkait giro');
                    $table->bigInteger('document_date')->nullable()->comment('Tanggal dokumen giro (Unix)');
                    $table->bigInteger('due_date')->nullable()->comment('Tanggal jatuh tempo giro (Unix)');
                    $table->bigInteger('reject_date')->nullable()->comment('Tanggal giro ditolak (Unix)');
                    $table->decimal('total_amount', 17, 5)->default(0)->comment('Nilai giro');
                    $table->decimal('kurs', 17, 5)->nullable()->comment('Kurs saat transaksi');
                    $table->enum('status', ['pending', 'cleared', 'rejected', 'void'])->default('pending')->comment('Status giro: menunggu/cair/tolak/batal');
                    $table->enum('status_flag', ['Y', 'N'])->default('N')->comment('Penanda status cair (legacy STATUS_FLAG)');
                    $table->integer('period_name')->nullable()->comment('Periode akuntansi terdampak YYYYMM');
                    $table->string('document_class_code', 40)->nullable()->comment('Kode kelas dokumen (in/out)');
                    $table->char('receipt_uuid', 36)->nullable()->comment('UUID penerimaan kas terkait');
                    $table->char('payment_uuid', 36)->nullable()->comment('UUID pengeluaran kas terkait');
                    $table->text('note')->nullable()->comment('Catatan giro');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['document_no', 'deleted_at'], 'idx_cheques_document_no_unique_soft');
                    $table->index('company_uuid', 'idx_cheques_company_uuid');
                    $table->index('branch_uuid', 'idx_cheques_branch_uuid');
                    $table->index('bank_account_uuid', 'idx_cheques_bank_account_uuid');
                    $table->index('person_uuid', 'idx_cheques_person_uuid');
                    $table->index('status', 'idx_cheques_status');
                    $table->index('period_name', 'idx_cheques_period_name');
                    $table->index('deleted_at', 'idx_cheques_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('cheques');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};

