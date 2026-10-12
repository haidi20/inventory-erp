<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel-tabel akuntansi:
 * accounts, account_mappings, tax_settings, closing_periods, journals, journal_lines, 
 * account_balances, recurring_journals, dan journal_attachments.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('code')->comment('Kode akun/COA (e.g. 1101)');
            $table->string('name')->index()->comment('Nama akun (e.g. Kas Besar)');
            $table->uuid('branch_uuid')->nullable()->comment('UUID cabang pemilik akun');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik akun');
            $table->unsignedTinyInteger('level')->default(3)->comment('Level hirarki COA (1-3)');
            $table->boolean('is_header')->default(false)->comment('Flag jika akun adalah header/induk');
            $table->string('type')->index()->comment('Tipe akun: asset, liability, equity, revenue, expense');
            $table->enum('type_saldo', ['debit', 'credit'])->default('debit')->comment('Saldo normal akun');
            $table->enum('financial_statement', ['balance_sheet', 'income_statement'])->nullable()->comment('Jenis laporan keuangan');
            $table->uuid('parent_uuid')->nullable()->comment('UUID Induk akun');
            $table->foreign('parent_uuid')->references('uuid')->on('accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index()->comment('Status aktif akun');
            $table->boolean('is_cash_bank')->default(false)->comment('Penanda akun kas/bank/giro');
            $table->boolean('is_giro')->default(false)->comment('Penanda jenis bank merupakan giro');
            $table->char('currency_uuid', 36)->nullable()->comment('UUID mata uang akun');
            $table->decimal('exchange_rate', 18, 6)->nullable()->default(1)->comment('Kurs akun terhadap mata uang dasar');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index('branch_uuid');
            $table->index('company_uuid');
            $table->unique(['branch_uuid', 'code', 'deleted_at'], 'idx_accounts_code_unique_soft');
            $table->index('is_cash_bank', 'idx_accounts_is_cash_bank');
            $table->index('currency_uuid', 'idx_accounts_currency_uuid');
        });

        Schema::create('account_mappings', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('key')->unique()->comment('Kunci pemetaan sistem (e.g. kasir.kas)');
            $table->uuid('branch_uuid')->nullable()->comment('UUID cabang pemilik pemetaan akun');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik pemetaan akun');
            $table->string('menu_name')->nullable()->comment('Nama menu terkait pemetaan');
            $table->uuid('account_uuid')->comment('UUID Akun yang dipetakan');
            $table->foreign('account_uuid')->references('uuid')->on('accounts')->cascadeOnDelete();
            $table->enum('type_saldo', ['debit', 'credit'])->default('debit')->comment('Posisi normal akun dalam pemetaan');
            $table->text('note')->nullable()->comment('Catatan penggunaan pemetaan');
            $table->string('value_source')->nullable()->comment('Sumber nilai transaksi');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index('branch_uuid');
            $table->index('company_uuid');
        });

        Schema::create('tax_settings', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->string('name')->unique()->comment('Nama pajak (e.g. PPN)');
            $table->decimal('percentage', 5, 2)->default(0)->comment('Persentase pajak');
            $table->uuid('account_uuid')->comment('UUID Akun penampung pajak');
            $table->foreign('account_uuid')->references('uuid')->on('accounts')->cascadeOnDelete();
            $table->boolean('is_active')->default(true)->comment('Status aktif pajak');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
        });

        Schema::create('closing_periods', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik periode tutup buku');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->string('period')->comment('Periode akuntansi (YYYY-MM)');
            $table->string('name')->nullable()->comment('Nama periode (e.g. Maret 2026)');
            $table->bigInteger('start_date')->comment('Tanggal awal periode (Unix)');
            $table->bigInteger('end_date')->comment('Tanggal akhir periode (Unix)');
            $table->bigInteger('closed_at')->nullable()->comment('Waktu penutupan periode (Unix)');
            $table->string('status')->default('open')->comment('Status periode: open, closed');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->unique(['branch_uuid', 'period']);
            $table->index(['company_uuid', 'deleted_at'], 'idx_closing_periods_company_deleted');
        });

        Schema::create('journals', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang transaksi');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik jurnal');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->string('type')->nullable()->index()->comment('Tipe jurnal (GJ, ADJ, REV)');
            $table->string('transaction_code')->comment('Nomor dokumen jurnal sistem');
            $table->string('reference_no')->comment('Nomor referensi eksternal/manual');
            $table->string('reference_model')->nullable()->index()->comment('Model sumber transaksi');
            $table->string('reference_type')->nullable()->comment('Tipe sumber transaksi (context)');
            $table->uuid('reference_uuid')->nullable()->comment('UUID baris asal');
            $table->uuid('closing_period_uuid')->nullable()->comment('UUID Periode penutupan terkait');
            $table->foreign('closing_period_uuid')->references('uuid')->on('closing_periods')->nullOnDelete();
            $table->bigInteger('journal_date')->index()->comment('Tanggal efektif jurnal secara akuntansi (Unix)');
            $table->bigInteger('scheduled_at')->nullable()->comment('Waktu posting otomatis yang dijadwalkan (Unix)');
            $table->string('description')->nullable()->comment('Keterangan narasi jurnal');
            $table->string('status')->default('posted')->index()->comment('Status: draft, posted');
            $table->string('reversal_status')->nullable()->comment('Status pembalikan: pending_reversal, reversed');
            $table->bigInteger('reversal_date')->nullable()->comment('Tanggal pembalikan jurnal (Unix)');
            $table->uuid('reversal_journal_uuid')->nullable()->comment('UUID Jurnal pembalik');
            $table->string('workflow_status')->nullable()->default('draft')->comment('Status persetujuan: draft, pending, approved');
            $table->integer('version')->default(1)->comment('Versi record untuk optimistic locking');
            $table->boolean('is_fiscal')->default(false)->comment('Flag jika jurnal adalah koreksi fiskal');
            $table->string('fiscal_type')->nullable()->comment('Tipe fiskal: positive, negative');
            $table->boolean('is_system')->default(true)->comment('Flag jika jurnal dibuat otomatis oleh sistem');
            $table->boolean('is_adjusting')->default(false)->comment('Flag jika jurnal adalah jurnal penyesuaian');
            $table->boolean('is_processed')->default(false)->comment('Flag jika jurnal sudah masuk ke buku besar');
            $table->char('created_by', 36)->nullable()->comment('UUID pembuat record');
            $table->char('updated_by', 36)->nullable()->comment('UUID pengubah record');
            $table->char('deleted_by', 36)->nullable()->comment('UUID penghapus record');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);

            $table->unique(['branch_uuid', 'transaction_code', 'deleted_at'], 'idx_journals_code_unique_soft');
            $table->unique(['branch_uuid', 'reference_no', 'deleted_at'], 'idx_journals_ref_unique_soft');
            $table->index(['reference_type', 'reference_uuid']);
            $table->index('deleted_at', 'idx_journals_deleted_at');
            $table->index(['company_uuid', 'deleted_at'], 'idx_journals_company_deleted');
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik detail jurnal');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('journal_uuid')->comment('UUID Jurnal induk');
            $table->foreign('journal_uuid')->references('uuid')->on('journals')->cascadeOnDelete();
            $table->uuid('account_uuid')->comment('UUID Akun yang dipengaruhi');
            $table->foreign('account_uuid')->references('uuid')->on('accounts')->cascadeOnDelete();
            $table->decimal('debit', 18, 2)->default(0)->comment('Nilai debit (Rp)');
            $table->decimal('credit', 18, 2)->default(0)->comment('Nilai kredit (Rp)');
            $table->boolean('taxable')->default(false)->comment('Flag jika mengandung unsur pajak');
            $table->decimal('tax_rate', 5, 2)->nullable()->comment('Tarif pajak yang dikenakan');
            $table->string('tax_type')->nullable()->comment('Jenis pajak: ppn_input, ppn_output, pph_23');
            $table->string('foreign_currency', 3)->nullable()->comment('Kode mata uang asing');
            $table->decimal('foreign_amount', 18, 4)->nullable()->comment('Nilai dalam mata uang asing');
            $table->decimal('exchange_rate', 18, 4)->nullable()->comment('Kurs konversi saat transaksi');
            $table->boolean('is_fiscal')->default(false)->comment('Flag jika baris jurnal adalah koreksi fiskal');
            $table->string('fiscal_type')->nullable()->comment('Tipe fiskal: positive, negative');
            $table->uuid('contact_uuid')->nullable()->comment('UUID Supplier/Vendor/Pasien (Sub-Ledger)');
            $table->string('description')->nullable()->comment('Keterangan baris jurnal');
            $table->boolean('is_reconciled')->default(false)->index()->comment('Flag jika baris sudah direkonsiliasi');
            $table->boolean('is_processed')->default(false)->index()->comment('Flag jika baris sudah terposting ke ledger');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->unique(['journal_uuid', 'account_uuid', 'uuid', 'deleted_at'], 'idx_journal_lines_unique_soft');
            $table->index('deleted_at', 'idx_journal_lines_deleted_at');
            $table->index(['company_uuid', 'deleted_at'], 'idx_journal_lines_company_deleted');
        });

        Schema::create('account_balances', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang');
            $table->uuid('company_uuid')->nullable()->comment('UUID Perusahaan');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->uuid('account_uuid')->comment('UUID Akun');
            $table->foreign('account_uuid')->references('uuid')->on('accounts')->cascadeOnDelete();
            $table->string('period')->index()->comment('Periode saldo (YYYY-MM)');
            $table->decimal('opening_balance', 18, 2)->default(0)->comment('Saldo awal periode');
            $table->decimal('total_debit', 18, 2)->default(0)->comment('Total mutasi debit');
            $table->decimal('total_credit', 18, 2)->default(0)->comment('Total mutasi kredit');
            $table->decimal('closing_balance', 18, 2)->default(0)->comment('Saldo akhir periode (snapshot)');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();

            $table->unique(['branch_uuid', 'account_uuid', 'period']);
        });

        Schema::create('recurring_journals', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->comment('UUID Cabang');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik template jurnal berkala');
            $table->foreign('branch_uuid')->references('uuid')->on('branches')->cascadeOnDelete();
            $table->string('template_name')->comment('Nama template jurnal berkala');
            $table->string('frequency')->comment('Frekuensi eksekusi: monthly, weekly, daily');
            $table->bigInteger('next_run_date')->comment('Tanggal eksekusi berikutnya (Unix)');
            $table->bigInteger('last_run_date')->nullable()->comment('Tanggal eksekusi terakhir (Unix)');
            $table->bigInteger('end_date')->nullable()->comment('Tanggal berakhirnya pengulangan (Unix)');
            $table->json('journal_data')->comment('Payload data jurnal yang akan dibuat');
            $table->string('status')->default('active')->comment('Status template: active, inactive');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_recurring_journals_company_deleted');
        });

        Schema::create('recurring_journal_lines', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('recurring_journal_uuid')->comment('UUID header recurring journal');
            $table->uuid('branch_uuid')->comment('UUID cabang pemilik data');
            $table->uuid('company_uuid')->comment('UUID perusahaan pemilik data');
            $table->uuid('account_uuid')->comment('UUID akun jurnal');
            $table->decimal('debit', 18, 2)->default(0)->comment('Nominal debit');
            $table->decimal('credit', 18, 2)->default(0)->comment('Nominal kredit');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable()->comment('UUID pengguna pembuat data');
            $table->uuid('updated_by')->nullable()->comment('UUID pengguna pengubah terakhir');
            $table->uuid('deleted_by')->nullable()->comment('UUID pengguna penghapus data');
            $table->index('recurring_journal_uuid');
            $table->index('company_uuid');
            $table->index('deleted_at', 'idx_recurring_journal_lines_deleted_at');
        });

        Schema::create('journal_attachments', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('branch_uuid')->nullable()->comment('UUID cabang pemilik lampiran jurnal');
            $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik lampiran jurnal');
            $table->uuid('journal_uuid')->comment('UUID Jurnal terkait');
            $table->foreign('journal_uuid')->references('uuid')->on('journals')->cascadeOnDelete();
            $table->string('file_name')->comment('Nama file asli');
            $table->string('file_path')->comment('Lokasi penyimpanan file');
            $table->string('file_type')->comment('Mime type file (e.g. image/jpeg, pdf)');
            $table->integer('file_size')->comment('Ukuran file dalam bytes');
            $table->bigInteger('created_at')->nullable();
            $table->bigInteger('updated_at')->nullable();
            $table->bigInteger('deleted_at')->unsigned()->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->index(['company_uuid', 'deleted_at'], 'idx_journal_attachments_company_deleted');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_attachments');
        Schema::dropIfExists('recurring_journal_lines');
        Schema::dropIfExists('recurring_journals');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journals');
        Schema::dropIfExists('closing_periods');
        Schema::dropIfExists('account_balances');
        Schema::dropIfExists('tax_settings');
        Schema::dropIfExists('account_mappings');
        Schema::dropIfExists('accounts');
    }
};

