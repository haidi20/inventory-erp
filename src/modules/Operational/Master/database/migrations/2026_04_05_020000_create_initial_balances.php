<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel saldo awal stok barang
            if (!Schema::hasTable('item_balances')) {
                Schema::create('item_balances', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->bigInteger('id')->nullable()->comment('Penjelasan kolom id.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->integer('period_name')->comment('Format YYYYMM');
                    $table->bigInteger('stock_date')->nullable()->comment('Penjelasan kolom stock_date.');
                    $table->decimal('qty_awal', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom qty_awal.');
                    $table->decimal('qty_masuk', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom qty_masuk.');
                    $table->decimal('qty_keluar', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom qty_keluar.');
                    $table->char('item_uuid', 36)->comment('Penjelasan kolom item_uuid.');
                    $table->char('location_uuid', 36)->comment('Penjelasan kolom location_uuid.');
                    $table->decimal('hpp', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom hpp.');
                    $table->text('note')->nullable()->comment('Penjelasan kolom note.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_item_balances_deleted_at');
                });
            }
            // Tabel rincian saldo awal person
            if (!Schema::hasTable('person_saldo_details')) {
                Schema::create('person_saldo_details', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->bigInteger('id')->nullable()->comment('Penjelasan kolom id.');
                    $table->char('branch_uuid', 36)->comment('Penjelasan kolom branch_uuid.');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('person_uuid', 36)->comment('Penjelasan kolom person_uuid.');
                    $table->string('document_no', 40)->comment('Penjelasan kolom document_no.');
                    $table->decimal('total_amount', 20, 5)->nullable()->default(0)->comment('Penjelasan kolom total_amount.');
                    $table->decimal('rate_acc', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom rate_acc.');
                    $table->decimal('rate_tax', 17, 5)->nullable()->default(0)->comment('Penjelasan kolom rate_tax.');
                    $table->integer('period_name')->comment('Penjelasan kolom period_name.');
                    $table->bigInteger('document_date')->comment('Penjelasan kolom document_date.');
                    $table->bigInteger('due_date')->comment('Penjelasan kolom due_date.');
                    $table->char('location_uuid', 36)->nullable()->comment('Penjelasan kolom location_uuid.');
                    $table->decimal('total_paid', 20, 5)->nullable()->default(0)->comment('Penjelasan kolom total_paid.');
                    $table->decimal('balance', 20, 5)->nullable()->default(0)->comment('Penjelasan kolom balance.');
                    $table->integer('mata_uang_uuid')->nullable()->comment('Penjelasan kolom mata_uang_uuid.');
                    $table->char('currency_uuid', 36)->nullable()->comment('Penjelasan kolom currency_uuid.');
                    $table->string('note', 240)->nullable()->comment('Penjelasan kolom note.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_person_saldo_details_deleted_at');
                });
            }

            // Tambahan kolom kompatibilitas legacy pada tabel person_balances.
            if (Schema::hasTable('person_balances')) {
                Schema::table('person_balances', function (Blueprint $table): void {
                    if (!Schema::hasColumn('person_balances', 'id')) {
                        $table->unsignedBigInteger('id')->nullable()->unique()->comment('ID legacy saldo person');
                    }
                    if (!Schema::hasColumn('person_balances', 'saldo_hutang')) {
                        $table->decimal('saldo_hutang', 17, 5)->default(0)->comment('Nilai saldo hutang awal legacy');
                    }
                    if (!Schema::hasColumn('person_balances', 'location_uuid')) {
                        $table->char('location_uuid', 36)->nullable()->index()->comment('ID gudang terkait saldo');
                    }
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('item_balances');
            Schema::dropIfExists('person_saldo_details');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};



