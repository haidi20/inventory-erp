<?php

namespace Database\Migrations\v1;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        try {
            // Tabel master customer dan supplier
            if (!Schema::hasTable('people')) {
                Schema::create('people', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID internal record');
                    $table->bigInteger('legacy_id')->nullable()->comment('ID legacy dari sistem legacy');
                    $table->string('name', 255)->comment('Nama person (Customer/Supplier)');
                    $table->string('code', 100)->comment('Kode unik person');
                    $table->boolean('is_supplier')->nullable()->default(false)->comment('Penanda tipe Supplier');
                    $table->boolean('is_customer')->nullable()->default(false)->comment('Penanda tipe Customer');
                    $table->char('branch_uuid', 36)->nullable()->comment('ID Cabang (UUID)');
                    $table->char('company_uuid', 36)->comment('Penjelasan kolom company_uuid.');
                    $table->char('location_uuid', 36)->nullable()->comment('ID Gudang default (UUID)');
                    $table->text('description')->nullable()->comment('Keterangan singkat person');
                    $table->string('email', 255)->nullable()->comment('Alamat email person');
                    $table->string('phone', 50)->nullable()->comment('Nomor telepon person');
                    $table->text('address')->nullable()->comment('Alamat utama person');
                    $table->char('is_cash', 1)->nullable()->default('N')->comment('Penanda apakah pembayaran tunai (Y/N)');

                    $table->text('note')->nullable()->comment('Catatan tambahan internal');
                    $table->decimal('receivable_limit', 17, 5)->nullable()->default(0.00)->comment('Batas maksimal piutang (Customer)');
                    $table->decimal('payable_limit', 17, 5)->nullable()->default(0.00)->comment('Batas maksimal hutang (Supplier)');
                    $table->char('is_active', 1)->nullable()->default('Y')->comment('Status aktif (Y/N)');
                    $table->char('payment_term_uuid', 36)->nullable()->comment('ID Termin pembayaran default (UUID)');
                    $table->decimal('opening_balance', 17, 5)->nullable()->default(0.00)->comment('Saldo awal periode');
                    $table->char('employee_uuid', 36)->nullable()->comment('ID Karyawan yang terhubung (UUID)');
                    $table->string('region', 50)->nullable()->comment('Wilayah/Rayon pemasaran');
                    $table->string('npwp_no', 50)->nullable()->comment('Nomor NPWP');
                    $table->char('price_level_uuid', 36)->nullable()->comment('Level harga jual default (UUID)');
                    $table->string('type', 20)->nullable()->comment('Tipe person (legacy code)');
                    $table->char('payable_account_uuid', 36)->nullable()->comment('ID Akun Hutang (COA UUID)');
                    $table->char('receivable_account_uuid', 36)->nullable()->comment('ID Akun Piutang (COA UUID)');
                    $table->char('company_type_uuid', 36)->nullable()->comment('ID Tipe Perusahaan (UUID)');
                    $table->char('currency_uuid', 36)->nullable()->comment('ID Mata Uang (UUID)');
                    $table->char('customer_type_uuid', 36)->nullable()->comment('ID Tipe Customer (UUID)');
                    $table->string('alternate_name', 100)->nullable()->comment('Nama alias atau sebutan lain');
                    $table->char('salesman_uuid', 36)->nullable()->comment('ID Salesman/Petugas lapangan');
                    $table->boolean('is_approved')->nullable()->default(false)->comment('Status persetujuan internal');
                    $table->string('discount_percentage', 50)->nullable()->comment('Persentase diskon default (e.g. 10%+5%)');
                    $table->string('tax_code', 20)->nullable()->comment('Kode pajak (FK ke taxes)');
                    $table->decimal('discount_amount', 17, 5)->nullable()->default(0.00)->comment('Nilai diskon tunai fixed');
                    $table->decimal('loyalty_points', 17, 5)->nullable()->default(0.00)->comment('Poin loyalitas akumulasi');
                    $table->integer('created_by_legacy')->nullable()->comment('Penjelasan kolom created_by_legacy.');
                    $table->bigInteger('created_date')->nullable()->comment('Penjelasan kolom created_date.');
                    $table->integer('last_update_by')->nullable()->comment('Penjelasan kolom last_update_by.');
                    $table->bigInteger('last_update_date')->nullable()->comment('Penjelasan kolom last_update_date.');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['code', 'deleted_at'], 'idx_people_code');
                    $table->index('deleted_at', 'idx_people_deleted_at');
                });
            }
            // Tabel field kustom data person
            if (!Schema::hasTable('person_custom_fields')) {
                Schema::create('person_custom_fields', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Primary key UUID kustom field');
                    $table->char('person_uuid', 36)->comment('Relasi ke People UUID');
                    $table->string('field_key', 100)->comment('Nama/Kunci field kustom (e.g. Custom1, Hobby)');
                    $table->text('field_value')->nullable()->comment('Nilai dari field kustom');
                    $table->string('field_type', 50)->nullable()->default('string')->comment('Tipe data field (string, number, date)');
                    $table->bigInteger('created_at')->nullable()->comment('Waktu pembuatan awal data');
                    $table->bigInteger('updated_at')->nullable()->comment('Waktu terakhir pembaruan data');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Waktu penghapusan logis data');
                    $table->char('created_by', 36)->nullable()->comment('UUID pengguna pembuat data');
                    $table->char('updated_by', 36)->nullable()->comment('UUID pengguna pengubah terakhir');
                    $table->char('deleted_by', 36)->nullable()->comment('UUID pengguna penghapus data');
                    $table->unique(['person_uuid', 'field_key', 'deleted_at'], 'idx_person_custom_unique');
                    $table->index('deleted_at', 'idx_person_custom_fields_deleted_at');
                });
            }
            // Tabel alamat person
            if (!Schema::hasTable('person_addresses')) {
                Schema::create('person_addresses', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->char('person_uuid', 36)->comment('Penjelasan kolom person_uuid.');
                    $table->string('type', 50)->comment('Penjelasan kolom type.');
                    $table->text('address1')->comment('Penjelasan kolom address1.');
                    $table->text('address2')->nullable()->comment('Penjelasan kolom address2.');
                    $table->string('city', 100)->nullable()->comment('Penjelasan kolom city.');
                    $table->string('province', 100)->nullable()->comment('Penjelasan kolom province.');
                    $table->string('postal_code', 20)->nullable()->comment('Kode pos alamat.');
                    $table->string('country', 100)->nullable()->default('Indonesia')->comment('Negara alamat.');
                    $table->boolean('is_primary')->default(false)->comment('Penanda alamat utama.');
                    $table->string('phone', 50)->nullable()->comment('Penjelasan kolom phone.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->index('deleted_at', 'idx_person_addresses_deleted_at');
                });
            }
            // Tabel kontak person
            if (!Schema::hasTable('person_contacts')) {
                Schema::create('person_contacts', function (Blueprint $table): void {
                    $table->uuid('uuid')->primary()->comment('Penjelasan kolom uuid.');
                    $table->char('person_uuid', 36)->comment('Penjelasan kolom person_uuid.');
                    $table->string('name', 255)->comment('Penjelasan kolom name.');
                    $table->string('position', 100)->nullable()->comment('Penjelasan kolom position.');
                    $table->string('phone', 50)->nullable()->comment('Penjelasan kolom phone.');
                    $table->string('email', 255)->nullable()->comment('Penjelasan kolom email.');
                    $table->bigInteger('created_at')->nullable()->comment('Penjelasan kolom created_at.');
                    $table->bigInteger('updated_at')->nullable()->comment('Penjelasan kolom updated_at.');
                    $table->bigInteger('deleted_at')->unsigned()->default(0)->comment('Penjelasan kolom deleted_at.');
                    $table->char('created_by', 36)->nullable()->comment('Penjelasan kolom created_by.');
                    $table->char('updated_by', 36)->nullable()->comment('Penjelasan kolom updated_by.');
                    $table->char('deleted_by', 36)->nullable()->comment('Penjelasan kolom deleted_by.');
                    $table->index('deleted_at', 'idx_person_contacts_deleted_at');
                });
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        try {
            Schema::dropIfExists('person_contacts');
            Schema::dropIfExists('person_addresses');
            Schema::dropIfExists('person_custom_fields');
            Schema::dropIfExists('people');
        } catch (\Throwable $e) {
            throw $e;
        }
    }
};



