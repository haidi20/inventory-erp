<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel-tabel master data utama:
 * employees, patients, suppliers, products, services, dan service packages.
 *
 * Standarisasi: Menggunakan bigInteger untuk created_at, updated_at, deleted_at
 * untuk mendukung Unix Timestamps (Go Parity).
 */
return new class extends Migration {
    public function up(): void
    {
        try {
            Schema::create('employees', function (Blueprint $table) {
                $table->uuid('uuid')->primary();
                $table->uuid('branch_uuid')->comment('UUID Cabang tempat bekerja');
                $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik data karyawan');
                $table->uuid('user_uuid')->nullable()->comment('UUID User jika memiliki akses login');
                $table->string('nik', 16)->nullable()->comment('Nomor Induk Kependudukan sebagai identitas unik');
                $table->unique(['nik', 'deleted_at'], 'idx_employees_nik_deleted');
                $table->string('name')->index()->comment('Nama lengkap karyawan/staff');
                $table->string('sip_number')->nullable()->comment('Nomor Surat Izin Praktik (SIP)');
                $table->string('str_number')->nullable()->comment('Nomor Surat Tanda Registrasi (STR)');
                $table->string('ihs_practitioner_id')->nullable()->comment('Practitioner ID dari IHS SATUSEHAT');
                $table->string('specialization')->nullable()->comment('Spesialisasi dokter');
                $table->string('role')->nullable()->index()->comment('Peran fungsional: dokter, kasir, admin, dll');
                $table->decimal('fee_default', 12, 2)->default(0)->comment('Jasa/fee default tenaga medis');
                $table->bigInteger('satusehat_last_sync')->nullable()->comment('Terakhir sinkronisasi dengan SATUSEHAT (Unix Timestamp)');
                $table->boolean('is_active')->default(true)->index()->comment('Status aktif karyawan');

                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();

                $table->index('deleted_at', 'idx_employees_deleted_at');
                $table->index(['company_uuid', 'deleted_at'], 'idx_employees_company_deleted');
            $table->index(['company_uuid', 'branch_uuid', 'deleted_at'], 'idx_employees_company_branch_deleted');
            });

            Schema::create('patients', function (Blueprint $table) {
                $table->uuid('uuid')->primary();
                $table->uuid('branch_uuid')->comment('UUID Cabang pendaftaran awal');
                $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik data pasien');
                $table->string('medical_record_no')->comment('Nomor Rekam Medis unik per cabang');
                $table->string('nik', 16)->nullable()->comment('Nomor Induk Kependudukan');
                $table->string('ihs_number')->nullable()->comment('Patient ID dari IHS SATUSEHAT');
                $table->string('name')->index()->comment('Nama lengkap pasien');
                $table->bigInteger('birth_date')->nullable()->comment('Tanggal lahir pasien (Unix)');
                $table->string('gender', 10)->nullable()->comment('Jenis kelamin');
                $table->string('phone')->nullable()->index()->comment('Nomor telepon/WA');
                $table->string('email')->nullable()->comment('Alamat email');
                $table->text('address')->nullable()->comment('Alamat tempat tinggal');
                $table->string('bpjs_number')->nullable()->comment('Nomor kartu BPJS Kesehatan');
                $table->bigInteger('satusehat_last_sync')->nullable()->comment('Terakhir sinkronisasi dengan SATUSEHAT (Unix Timestamp)');
                $table->boolean('is_active')->default(true)->index()->comment('Status aktif pasien');

                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();

                $table->unique(['branch_uuid', 'medical_record_no', 'deleted_at'], 'idx_patients_mr_unique_soft');
                $table->unique(['nik', 'deleted_at'], 'idx_patients_nik_unique_soft');
                $table->index('deleted_at', 'idx_patients_deleted_at');
                $table->index(['company_uuid', 'deleted_at'], 'idx_patients_company_deleted');
                $table->index(['company_uuid', 'branch_uuid', 'deleted_at'], 'idx_patients_company_branch_deleted');
        });

            Schema::create('suppliers', function (Blueprint $table) {
                $table->uuid('uuid')->primary();
                $table->uuid('branch_uuid')->comment('UUID Cabang relasi');
                $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik data supplier');
                $table->string('name')->index()->comment('Nama perusahaan supplier');
                $table->string('phone')->nullable()->comment('Nomor telepon supplier');
                $table->string('email')->nullable()->comment('Email supplier');
                $table->text('address')->nullable()->comment('Alamat supplier');
                $table->integer('term_days')->default(0)->comment('Termin pembayaran default (hari)');
                $table->boolean('is_active')->default(true)->index()->comment('Status aktif supplier');

                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();

                $table->unique(['branch_uuid', 'name', 'deleted_at'], 'idx_suppliers_name_unique_soft');
                $table->index('deleted_at', 'idx_suppliers_deleted_at');
                $table->index(['company_uuid', 'deleted_at'], 'idx_suppliers_company_deleted');
            $table->index(['company_uuid', 'branch_uuid', 'deleted_at'], 'idx_suppliers_company_branch_deleted');
            });

            Schema::create('items', function (Blueprint $table) {
                $table->uuid('uuid')->primary()->comment('UUID item internal');
                $table->unsignedBigInteger('id')->nullable()->comment('ID legacy numeric');
                $table->char('branch_uuid', 36)->comment('UUID cabang pemilik item');
                $table->char('company_uuid', 36)->comment('UUID perusahaan pemilik item');
                $table->char('product_uuid', 36)->nullable()->comment('UUID kategori atau keluarga produk');
                $table->string('code', 255)->comment('Kode SKU item');
                $table->string('name', 255)->comment('Nama item');
                $table->text('description')->nullable()->comment('Deskripsi item');
                $table->text('note')->nullable()->comment('Catatan internal item');
                $table->string('barcode', 255)->nullable()->comment('Barcode item');
                $table->string('item_type', 50)->default('goods')->comment('Tipe item seperti goods atau service');
                $table->char('base_unit_uuid', 36)->nullable()->comment('UUID satuan dasar item');
                $table->char('default_location_uuid', 36)->nullable()->comment('UUID gudang default item');
                $table->string('storage_bin_code', 100)->nullable()->comment('Kode lokasi rak/bin default');
                $table->char('item_category_lookup_uuid', 36)->nullable()->comment('UUID lookup kategori item');
                $table->char('item_group_lookup_uuid', 36)->nullable()->comment('UUID lookup kelompok item');
                $table->char('item_type_lookup_uuid', 36)->nullable()->comment('UUID lookup tipe item');
                $table->char('brand_lookup_uuid', 36)->nullable()->comment('UUID lookup merek item');
                $table->char('origin_lookup_uuid', 36)->nullable()->comment('UUID lookup negara asal item');
                $table->char('grade_lookup_uuid', 36)->nullable()->comment('UUID lookup grade item');
                $table->decimal('price_buy', 12, 2)->default(0)->comment('Harga beli format legacy');
                $table->decimal('price_sell', 12, 2)->default(0)->comment('Harga jual format legacy');
                $table->string('unit', 255)->nullable()->comment('Nama satuan format legacy');
                $table->string('category', 255)->nullable()->comment('Nama kategori format legacy');
                $table->string('location', 255)->nullable()->comment('Lokasi penyimpanan format legacy');
                $table->text('remarks')->nullable()->comment('Catatan tambahan format legacy');
                $table->string('kind', 255)->nullable()->comment('Jenis item format legacy');
                $table->string('brand', 255)->nullable()->comment('Merek item format legacy');
                $table->string('origin', 255)->nullable()->comment('Asal item format legacy');
                $table->string('grade', 255)->nullable()->comment('Grade item format legacy');
                $table->decimal('latest_buy_price', 12, 2)->default(0)->comment('Harga beli terakhir');
                $table->decimal('latest_sell_price', 12, 2)->default(0)->comment('Harga jual terakhir');
                $table->decimal('base_price_buy', 12, 2)->default(0)->comment('Harga beli dasar');
                $table->decimal('base_price_sell', 12, 2)->default(0)->comment('Harga jual dasar');
                $table->decimal('standard_cost', 12, 2)->default(0)->comment('Biaya standar item');
                $table->decimal('min_stock', 12, 2)->default(0)->comment('Batas minimum stok');
                $table->decimal('max_stock', 12, 2)->default(0)->comment('Batas maksimum stok');
                $table->decimal('min_order', 12, 2)->default(0)->comment('Batas minimum order');
                $table->decimal('max_order', 12, 2)->default(0)->comment('Batas maksimum order');
                $table->decimal('length_cm', 12, 2)->default(0)->comment('Panjang item (cm)');
                $table->decimal('width_cm', 12, 2)->default(0)->comment('Lebar item (cm)');
                $table->decimal('height_cm', 12, 2)->default(0)->comment('Tinggi item (cm)');
                $table->boolean('is_bom_enabled')->default(false)->comment('Penanda item mendukung BOM/rakitan');
                $table->char('coa_inventory_uuid', 36)->nullable()->comment('UUID akun persediaan item');
                $table->char('coa_hpp_uuid', 36)->nullable()->comment('UUID akun HPP item');
                $table->char('coa_sales_uuid', 36)->nullable()->comment('UUID akun penjualan item');
                $table->char('coa_suspend_uuid', 36)->nullable()->comment('UUID akun suspend item');
                $table->char('coa_sales_return_uuid', 36)->nullable()->comment('UUID akun retur jual');
                $table->char('coa_purchase_return_uuid', 36)->nullable()->comment('UUID akun retur beli');
                $table->char('coa_sales_discount_uuid', 36)->nullable()->comment('UUID akun diskon penjualan');
                $table->string('image', 100)->nullable()->comment('Path file gambar item');
                $table->string('item_name_alt', 255)->nullable()->comment('Nama alternatif item');
                $table->decimal('opening_cost', 17, 5)->default(0)->comment('Harga pokok awal item');
                $table->char('active_flag', 1)->default('Y')->comment('Status aktif format legacy Y/N');
                $table->boolean('is_active')->default(true)->comment('Status aktif item');

                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();

                $table->unique(['branch_uuid', 'code', 'deleted_at'], 'idx_branch_item_code');
                $table->index('company_uuid');
                $table->index('deleted_at', 'idx_items_deleted_at');
                $table->index(['company_uuid', 'branch_uuid', 'deleted_at'], 'idx_items_company_branch_deleted');
        });

            Schema::create('item_custom_fields', function (Blueprint $table) {
                $table->uuid('uuid')->primary()->comment('UUID custom field item');
                $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik data');
                $table->char('company_uuid', 36)->nullable()->comment('UUID perusahaan pemilik data');
                $table->char('item_uuid', 36)->comment('UUID item induk');
                $table->string('field_key', 100)->comment('Kunci custom field');
                $table->text('field_value')->nullable()->comment('Nilai custom field');

                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();

                $table->index('item_uuid');
                $table->index('deleted_at', 'idx_item_custom_fields_deleted_at');
            });

            Schema::create('item_discounts', function (Blueprint $table) {
                $table->uuid('uuid')->primary()->comment('UUID diskon item');
                $table->unsignedBigInteger('id')->nullable()->comment('ID legacy numeric');
                $table->char('branch_uuid', 36)->nullable()->comment('UUID cabang pemilik data');
                $table->char('company_uuid', 36)->nullable()->comment('UUID perusahaan pemilik data');
                $table->char('item_uuid', 36)->comment('UUID item yang diberi diskon');
                $table->string('uom_code', 20)->nullable()->comment('Kode satuan diskon');
                $table->decimal('qty', 12, 2)->default(1)->comment('Kuantitas minimum diskon');
                $table->string('discount_percen', 255)->nullable()->comment('Format persen diskon, contoh 10+5');

                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();

                $table->index('item_uuid');
                $table->index('deleted_at', 'idx_item_discounts_deleted_at');
            });

            Schema::create('services', function (Blueprint $table) {
                $table->uuid('uuid')->primary();
                $table->uuid('branch_uuid')->comment('UUID Cabang layanan');
                $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik layanan');
                $table->string('code')->comment('Kode tindakan/layanan');
                $table->string('name')->index()->comment('Nama tindakan/layanan');
                $table->integer('duration_minutes')->default(0)->comment('Estimasi durasi layanan dalam menit');
                $table->decimal('price', 12, 2)->default(0)->comment('Tarif layanan');
                $table->decimal('doctor_fee', 12, 2)->default(0)->comment('Porsi jasa untuk dokter/pelaksana');
                $table->boolean('is_active')->default(true)->index()->comment('Status aktif layanan');

                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();

                $table->unique(['branch_uuid', 'code', 'deleted_at'], 'idx_services_code_unique_soft');
                $table->index('deleted_at', 'idx_services_deleted_at');
                $table->index(['company_uuid', 'deleted_at'], 'idx_services_company_deleted');
            });

            Schema::create('service_packages', function (Blueprint $table) {
                $table->uuid('uuid')->primary();
                $table->uuid('branch_uuid')->comment('UUID Cabang paket');
                $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik paket layanan');
                $table->string('code')->comment('Kode paket layanan');
                $table->string('name')->comment('Nama paket layanan');
                $table->decimal('price', 12, 2)->default(0)->comment('Harga total paket');
                $table->boolean('is_active')->default(true)->comment('Status aktif paket');

                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();

                $table->unique(['branch_uuid', 'code', 'deleted_at'], 'idx_service_packages_code_unique_soft');
                $table->index('deleted_at', 'idx_service_packages_deleted_at');
                $table->index(['company_uuid', 'deleted_at'], 'idx_service_packages_company_deleted');
            });

            Schema::create('service_package_items', function (Blueprint $table) {
                $table->uuid('uuid')->primary();
                $table->uuid('branch_uuid')->comment('UUID Cabang');
                $table->uuid('company_uuid')->nullable()->comment('UUID perusahaan pemilik detail paket');
                $table->uuid('service_package_uuid')->comment('UUID Induk paket');
                $table->enum('item_type', ['service', 'product'])->comment('Tipe item: layanan atau produk');
                $table->uuid('item_uuid')->comment('UUID referensi ke tabel services atau products');
                $table->integer('qty')->default(1)->comment('Jumlah item dalam paket');

                $table->bigInteger('created_at')->nullable();
                $table->bigInteger('updated_at')->nullable();
                $table->bigInteger('deleted_at')->unsigned()->default(0);
                $table->uuid('created_by')->nullable();
                $table->uuid('updated_by')->nullable();
                $table->uuid('deleted_by')->nullable();
                $table->index(['item_type', 'item_uuid']);
                $table->index(['company_uuid', 'deleted_at'], 'idx_service_package_items_company_deleted');
            });

        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_package_items');
        Schema::dropIfExists('service_packages');
        Schema::dropIfExists('services');
        Schema::dropIfExists('item_discounts');
        Schema::dropIfExists('item_custom_fields');
        Schema::dropIfExists('items');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('employees');
    }
};
