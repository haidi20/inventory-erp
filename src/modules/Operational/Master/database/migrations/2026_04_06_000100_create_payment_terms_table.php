<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel master jangka waktu pembayaran
            if (!Schema::hasTable('payment_terms')) {
                Schema::create('payment_terms', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->unsignedBigInteger('id')->nullable()->comment('ID legacy dari aplikasi lain bila tersedia');
                    $table->integer('legacy_id')->nullable()->comment('ID unik termin dari sistem legacy');
                    $table->string('name', 40)->default('')->comment('Nama termin (contoh: T7, T14)');
                    $table->string('description', 80)->nullable()->comment('Penjelasan detail mengenai termin');
                    $table->integer('due_days')->nullable()->default(0)->comment('Jumlah hari jatuh tempo');
                    $table->boolean('is_active')->nullable()->default(true)->comment('Status aktif');
                    $table->boolean('is_primary')->nullable()->default(false)->comment('Penanda utama');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('branch_uuid', 36)->nullable()->comment('Penjelasan kolom branch_uuid.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->index('deleted_at', 'idx_payment_terms_deleted_at');
                    $table->index(['company_uuid', 'branch_uuid', 'deleted_at'], 'idx_payment_terms_company_branch_deleted');
        });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('payment_terms');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};


