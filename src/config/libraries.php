<?php

use App\Modules\Finance\Journal\Enums\JournalValueSource;

return [
    /*
    |--------------------------------------------------------------------------
    | Demo Data Libraries
    |--------------------------------------------------------------------------
    |
    | Data ini digunakan untuk Seeding (DemoSeeder) dan Testing.
    | Sentralisasi data ini memudahkan pemeliharaan integritas pengujian.
    |
    */

    'users' => [
        'admin@klinik.local' => ['name' => 'Administrator', 'role' => 'admin'],
        'owner@klinik.local' => ['name' => 'Owner Klinik', 'role' => 'owner'],
        'pendaftaran@klinik.local' => ['name' => 'Petugas Pendaftaran', 'role' => 'pendaftaran'],
        'perawat@klinik.local' => ['name' => 'Perawat Klinik', 'role' => 'perawat'],
        'apoteker@klinik.local' => ['name' => 'Apoteker Klinik', 'role' => 'apoteker'],
        'kasir@klinik.local' => ['name' => 'Kasir Klinik', 'role' => 'kasir'],
        'lab@klinik.local' => ['name' => 'Petugas Lab', 'role' => 'lab'],
        'dokter1@klinik.local' => ['name' => 'Denny Octavia', 'role' => 'dokter'],
        'dokter2@klinik.local' => ['name' => 'Eko Setiana', 'role' => 'dokter'],
    ],

    'employees' => [
        ['name' => 'Denny Octavia', 'role' => 'dokter', 'fee_default' => 20, 'email' => 'dokter1@klinik.local'],
        ['name' => 'Eko Setiana', 'role' => 'dokter', 'fee_default' => 20, 'email' => 'dokter2@klinik.local'],
        ['name' => 'Kasir Klinik', 'role' => 'kasir', 'fee_default' => 0, 'email' => 'kasir@klinik.local'],
    ],

    'patients' => [
        ['medical_record_no' => 'RM-20240001', 'name' => 'Pasien Demo Satu', 'gender' => 'M', 'phone' => '08000000001'],
        ['medical_record_no' => 'RM-20240002', 'name' => 'Pasien Demo Dua', 'gender' => 'F', 'phone' => '08000000002'],
        ['medical_record_no' => 'RM-20240003', 'name' => 'Pasien Demo Tiga', 'gender' => 'F', 'phone' => '08000000003'],
        ['medical_record_no' => 'RM-20240004', 'name' => 'Pasien Demo Empat', 'gender' => 'F', 'phone' => '08000000004'],
    ],

    'suppliers' => [
        ['name' => 'PT Global Health Setiawan', 'phone' => '021-552022'],
        ['name' => 'PT Global Health Yulianto', 'phone' => '021-558223'],
        ['name' => 'PT Lestari Medika Pradana', 'phone' => '021-551544'],
        ['name' => 'PT Pharma Utama Rojak', 'phone' => '021-558350'],
        ['name' => 'PT Sentosa Farma Astuti', 'phone' => '021-559512'],
    ],

    'products' => [
        ['code' => 'AMX-500', 'name' => 'Amoxicillin 500mg', 'unit' => 'kapsul', 'price_buy' => 11538, 'price_sell' => 15000, 'min_stock' => 2],
        ['code' => 'ANT-SYR', 'name' => 'Antasida Sirup', 'unit' => 'botol', 'price_buy' => 6153, 'price_sell' => 8000, 'min_stock' => 2],
        ['code' => 'DEX-05', 'name' => 'Dexamethasone 0.5mg', 'unit' => 'tablet', 'price_buy' => 3846, 'price_sell' => 5000, 'min_stock' => 2],
        ['code' => 'BDP-01', 'name' => 'Betadine Solution 30ml', 'unit' => 'botol', 'price_buy' => 13846, 'price_sell' => 18000, 'min_stock' => 2],
    ],

    'services' => [
        ['code' => 'SV-001', 'name' => 'Konsultasi Umum', 'duration_minutes' => 20, 'price' => 75000, 'doctor_fee' => 15000],
        ['code' => 'SV-002', 'name' => 'Cek Kolesterol', 'duration_minutes' => 30, 'price' => 150000, 'doctor_fee' => 25000],
        ['code' => 'SV-003', 'name' => 'Cek Gula Darah', 'duration_minutes' => 25, 'price' => 120000, 'doctor_fee' => 20000],
    ],

    'accounts' => [
        // LEVEL 1: CATEGORY (Header)
        ['code' => '1000', 'name' => 'HARTA (ASSETS)', 'type' => 'asset', 'level' => 1, 'is_header' => true, 'type_saldo' => 'debit', 'parent_code' => null],
        ['code' => '2000', 'name' => 'KEWAJIBAN (LIABILITIES)', 'type' => 'liability', 'level' => 1, 'is_header' => true, 'type_saldo' => 'credit', 'parent_code' => null],
        ['code' => '3000', 'name' => 'MODAL (EQUITY)', 'type' => 'equity', 'level' => 1, 'is_header' => true, 'type_saldo' => 'credit', 'parent_code' => null],
        ['code' => '4000', 'name' => 'PENDAPATAN (REVENUE)', 'type' => 'revenue', 'level' => 1, 'is_header' => true, 'type_saldo' => 'credit', 'parent_code' => null],
        ['code' => '5000', 'name' => 'BEBAN (EXPENSES)', 'type' => 'expense', 'level' => 1, 'is_header' => true, 'type_saldo' => 'debit', 'parent_code' => null],

        // LEVEL 2: SUB-CATEGORY (Header)
        ['code' => '1100', 'name' => 'Kas & Bank', 'type' => 'asset', 'level' => 2, 'is_header' => true, 'type_saldo' => 'debit', 'parent_code' => '1000'],
        ['code' => '1200', 'name' => 'Piutang', 'type' => 'asset', 'level' => 2, 'is_header' => true, 'type_saldo' => 'debit', 'parent_code' => '1000'],
        ['code' => '1300', 'name' => 'Persediaan', 'type' => 'asset', 'level' => 2, 'is_header' => true, 'type_saldo' => 'debit', 'parent_code' => '1000'],
        ['code' => '2100', 'name' => 'Kewajiban Lancar', 'type' => 'liability', 'level' => 2, 'is_header' => true, 'type_saldo' => 'credit', 'parent_code' => '2000'],
        ['code' => '4100', 'name' => 'Pendapatan Operasional', 'type' => 'revenue', 'level' => 2, 'is_header' => true, 'type_saldo' => 'credit', 'parent_code' => '4000'],
        ['code' => '5100', 'name' => 'Harga Pokok Penjualan', 'type' => 'expense', 'level' => 2, 'is_header' => true, 'type_saldo' => 'debit', 'parent_code' => '5000'],
        ['code' => '5200', 'name' => 'Beban Operasional', 'type' => 'expense', 'level' => 2, 'is_header' => true, 'type_saldo' => 'debit', 'parent_code' => '5000'],

        // LEVEL 3: ACCOUNTS (Transactionable)
        ['code' => '1101', 'name' => 'Kas Besar', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '1100'],
        ['code' => '1102', 'name' => 'Kas Kecil (Petty Cash)', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '1100'],
        ['code' => '1103', 'name' => 'Bank BCA', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '1100'],
        ['code' => '1104', 'name' => 'Bank Mandiri', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '1100'],
        ['code' => '1201', 'name' => 'Piutang Usaha', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '1200'],
        ['code' => '1301', 'name' => 'Persediaan Obat/Alkes', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '1300'],
        ['code' => '1302', 'name' => 'Persediaan Alat Kesehatan', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '1300'],
        ['code' => '2101', 'name' => 'Hutang Usaha', 'type' => 'liability', 'level' => 3, 'is_header' => false, 'type_saldo' => 'credit', 'parent_code' => '2100'],
        ['code' => '2103', 'name' => 'Hutang Gaji', 'type' => 'liability', 'level' => 3, 'is_header' => false, 'type_saldo' => 'credit', 'parent_code' => '2100'],
        ['code' => '2106', 'name' => 'Hutang PPN (Keluaran)', 'type' => 'liability', 'level' => 3, 'is_header' => false, 'type_saldo' => 'credit', 'parent_code' => '2100'],
        ['code' => '3100', 'name' => 'Modal Pemilik', 'type' => 'equity', 'level' => 3, 'is_header' => false, 'type_saldo' => 'credit', 'parent_code' => '3000'],
        ['code' => '3300', 'name' => 'Laba Ditahan', 'type' => 'equity', 'level' => 3, 'is_header' => false, 'type_saldo' => 'credit', 'parent_code' => '3000'],
        ['code' => '4101', 'name' => 'Pendapatan Jasa Medis', 'type' => 'revenue', 'level' => 3, 'is_header' => false, 'type_saldo' => 'credit', 'parent_code' => '4100'],
        ['code' => '4102', 'name' => 'Pendapatan Penjualan Obat', 'type' => 'revenue', 'level' => 3, 'is_header' => false, 'type_saldo' => 'credit', 'parent_code' => '4100'],
        ['code' => '5101', 'name' => 'HPP Obat', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '5100'],
        ['code' => '5201', 'name' => 'Beban Gaji & Tunjangan', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '5200'],
        ['code' => '5204', 'name' => 'Beban Listrik', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '5200'],

        // NEW ACCOUNTS FOR LAB, RAD, ADJUSTMENT
        ['code' => '4103', 'name' => 'Pendapatan Layanan Lab', 'type' => 'revenue', 'level' => 3, 'is_header' => false, 'type_saldo' => 'credit', 'parent_code' => '4100'],
        ['code' => '4104', 'name' => 'Pendapatan Layanan Radiologi', 'type' => 'revenue', 'level' => 3, 'is_header' => false, 'type_saldo' => 'credit', 'parent_code' => '4100'],
        ['code' => '5901', 'name' => 'Beban Selisih Persediaan', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '5000'],
        ['code' => '5205', 'name' => 'Beban Air (PDAM)', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '5200'],
        ['code' => '5211', 'name' => 'Beban Operasional Lainnya', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'type_saldo' => 'debit', 'parent_code' => '5200'],
    ],

    'account_mappings' => [
        // --- 1. MODUL PENJUALAN OBAT (POS) ---
        ['key' => 'penjualan.kas',         'menu_name' => 'Penjualan Obat', 'code' => '1101', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::TOTAL_PAID->value, 'note' => 'Penerimaan tunai dari penjualan obat'],
        ['key' => 'penjualan.piutang',     'menu_name' => 'Penjualan Obat', 'code' => '1201', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::TOTAL_RECEIVABLE->value, 'note' => 'Piutang jika pembayaran dilakukan tempo'],
        ['key' => 'penjualan.pendapatan',  'menu_name' => 'Penjualan Obat', 'code' => '4102', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::TOTAL_NET->value,   'note' => 'Pendapatan bruto (sebelum PPN)'],
        ['key' => 'penjualan.persediaan',  'menu_name' => 'Penjualan Obat', 'code' => '1301', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::TOTAL_COST->value,  'note' => 'Pengurangan nilai stok obat'],
        ['key' => 'penjualan.hpp',         'menu_name' => 'Penjualan Obat', 'code' => '5101', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::TOTAL_COST->value,  'note' => 'Beban pokok (harga beli) obat'],
        ['key' => 'penjualan.ppn',         'menu_name' => 'Penjualan Obat', 'code' => '2106', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::TOTAL_TAX->value,   'note' => 'Alokasi PPN 11%'],

        // --- 2. MODUL PEMERIKSAAN (REKAM MEDIS) ---
        ['key' => 'pemeriksaan.kas',             'menu_name' => 'Pemeriksaan (Rekam Medis)', 'code' => '1101', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::TOTAL_PAID->value],
        ['key' => 'pemeriksaan.piutang',         'menu_name' => 'Pemeriksaan (Rekam Medis)', 'code' => '1201', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::TOTAL_RECEIVABLE->value],
        ['key' => 'pemeriksaan.pendapatan_jasa', 'menu_name' => 'Pemeriksaan (Rekam Medis)', 'code' => '4101', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::TOTAL_SERVICE->value, 'note' => 'Pendapatan jasa konsultasi/tindakan'],
        ['key' => 'pemeriksaan.penjualan_obat',  'menu_name' => 'Pemeriksaan (Rekam Medis)', 'code' => '4102', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::TOTAL_NET_PRODUCT->value, 'note' => 'Pendapatan dari obat di rekam medis'],
        ['key' => 'pemeriksaan.persediaan_obat', 'menu_name' => 'Pemeriksaan (Rekam Medis)', 'code' => '1301', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::TOTAL_COST_PRODUCT->value],
        ['key' => 'pemeriksaan.hpp_obat',        'menu_name' => 'Pemeriksaan (Rekam Medis)', 'code' => '5101', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::TOTAL_COST_PRODUCT->value],

        // --- 3. MODUL PEMBELIAN ---
        ['key' => 'pembelian.kas',        'menu_name' => 'Pembelian', 'code' => '1101', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::TOTAL_PAID->value],
        ['key' => 'pembelian.hutang',     'menu_name' => 'Pembelian', 'code' => '2101', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::TOTAL_RECEIVABLE->value],
        ['key' => 'pembelian.persediaan', 'menu_name' => 'Pembelian', 'code' => '1301', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::TOTAL_GROSS->value, 'note' => 'Penambahan nilai stok Gudang'],

        // --- 4. MODUL HUTANG SUPPLIER ---
        ['key' => 'hutang.kas',          'menu_name' => 'Hutang Supplier', 'code' => '1101', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::AMOUNT_PAID->value],
        ['key' => 'hutang.hutang_usaha', 'menu_name' => 'Hutang Supplier', 'code' => '2101', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::AMOUNT_PAID->value, 'note' => 'Pelunasan hutang obat/alkes'],

        // --- 5. MODUL PIUTANG PASIEN & BPJS ---
        ['key' => 'piutang.kas',           'menu_name' => 'Piutang Pasien & BPJS', 'code' => '1101', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::AMOUNT_PAID->value],
        ['key' => 'piutang.piutang_usaha', 'menu_name' => 'Piutang Pasien & BPJS', 'code' => '1201', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::AMOUNT_PAID->value],

        // --- 6. MODUL BIAYA OPERASIONAL ---
        ['key' => 'biaya.kas_gaji',      'menu_name' => 'Biaya Gaji',        'code' => '1101', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::AMOUNT->value],
        ['key' => 'biaya.beban_gaji',    'menu_name' => 'Biaya Gaji',        'code' => '5201', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::AMOUNT->value],

        ['key' => 'biaya.kas_listrik',   'menu_name' => 'Biaya Listrik',     'code' => '1101', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::AMOUNT->value],
        ['key' => 'biaya.beban_listrik', 'menu_name' => 'Biaya Listrik',     'code' => '5204', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::AMOUNT->value],

        ['key' => 'biaya.kas_air',       'menu_name' => 'Biaya Air',         'code' => '1101', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::AMOUNT->value],
        ['key' => 'biaya.beban_air',     'menu_name' => 'Biaya Air',         'code' => '5205', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::AMOUNT->value],

        ['key' => 'biaya.kas_umum',      'menu_name' => 'Biaya Umum',        'code' => '1101', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::AMOUNT->value],
        ['key' => 'biaya.beban_umum',    'menu_name' => 'Biaya Umum',        'code' => '5211', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::AMOUNT->value],

        // --- 7. MODUL RETUR SUPPLIER ---
        ['key' => 'retur_supplier.hutang',  'menu_name' => 'Retur Supplier', 'code' => '2101', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::AMOUNT_PAID->value, 'note' => 'Pengurangan hutang ke supplier'],
        ['key' => 'retur_supplier.stok',    'menu_name' => 'Retur Supplier', 'code' => '1301', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::AMOUNT_PAID->value, 'note' => 'Pengurangan nilai stok barang'],

        // --- 8. MODUL PENYESUAIAN STOK ---
        ['key' => 'stok_adjustment.beban',  'menu_name' => 'Penyesuaian Stok', 'code' => '5901', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::AMOUNT->value],
        ['key' => 'stok_adjustment.stok',   'menu_name' => 'Penyesuaian Stok', 'code' => '1301', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::AMOUNT->value],

        // --- 9. MODUL LABORATORIUM ---
        ['key' => 'laboratorium.kas',        'menu_name' => 'Laboratorium', 'code' => '1101', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::TOTAL_PAID->value],
        ['key' => 'laboratorium.pendapatan', 'menu_name' => 'Laboratorium', 'code' => '4103', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::TOTAL_PAID->value],

        // --- 10. MODUL RADIOLOGI ---
        ['key' => 'radiologi.kas',        'menu_name' => 'Radiologi', 'code' => '1101', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::TOTAL_PAID->value],
        ['key' => 'radiologi.pendapatan', 'menu_name' => 'Radiologi', 'code' => '4104', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::TOTAL_PAID->value],

        // --- 11. MODUL PENDAFTARAN ---
        ['key' => 'pendaftaran.kas',        'menu_name' => 'Pendaftaran Pasien', 'code' => '1101', 'type_saldo' => 'debit',  'value_source' => JournalValueSource::AMOUNT->value],
        ['key' => 'pendaftaran.pendapatan', 'menu_name' => 'Pendaftaran Pasien', 'code' => '4101', 'type_saldo' => 'credit', 'value_source' => JournalValueSource::AMOUNT->value],
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Expectations
    |--------------------------------------------------------------------------
    |
    | Digunakan oleh AccountSettingTest untuk verifikasi data seeder.
    |
    */

    'key_accounts' => [
        '1000' => 'HARTA (ASSETS)',
        '1101' => 'Kas Besar',
        '2101' => 'Hutang Usaha',
        '4101' => 'Pendapatan Jasa Medis',
        '5101' => 'HPP Obat',
    ],

    'key_mappings' => [
        'penjualan.kas' => '1101',
        'pembelian.hutang' => '2101',
        'biaya.beban_listrik' => '5204',
    ],

    'balances' => [
        '1101' => 100000000,
        '1301' => 50000000,
        '3100' => 150000000,
    ],
];
