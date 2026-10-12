<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Loader Per Modul
|--------------------------------------------------------------------------
| File ini hanya loader. Semua route didefinisikan di masing-masing modul.
|
| === CORE ===
| app/Modules/Core/Identity/routes/web.php
|   - Login, logout, forgot password, reset password
|   - Settings: users (index, store, update, destroy, export, import)
|
| app/Modules/Core/Authorization/routes/web.php
|   - Settings: roles (index, create, store, edit, update, destroy)
|
| app/Modules/Core/Platform/routes/web.php
|   - Himbauan
|   - Settings: backup (index, export, download, restore, history)
|   - Settings: icd10 (index, store, destroy)
|   - Redirect: /settings/database, /settings/bpjs, /settings/satusehat
|
| === CLINICAL ===
| app/Modules/Clinical/Registration/routes/web.php
|   - Pendaftaran (index, store, updateStatus, destroy)
|   - Pasien (patientList, patientShow)
|
| app/Modules/Clinical/MedicalRecord/routes/web.php
|   - Transaksi: doctor-tasks, ancillary-requests, ancillary-results
|   - Lab (index, store, show, inputHasil, updateStatus, destroy)
|   - Radiologi (index, store, show, inputHasil, updateStatus, destroy)
|   - Complaints (index, store, update, destroy)
|
| app/Modules/Clinical/Pharmacy/routes/web.php
|   - Farmasi: index, resep, retur, stok-detail
|   - Farmacy: dashboard, resep, transaksi, stock-opname, stock-adjustments,
|              stock-mutations, saldo-awal, service-deposits, laporan, dll
|
| app/Modules/Clinical/Integration/routes/web.php
|   - BPJS: index, pengaturan
|   - Satusehat: index, sync, encounter, fhir, config, pengaturan
|   - Integrasi: bpjs (registrations, verification, referral, sep, claims)
|                satusehat (overview, sync, encounter, fhir, config)
|
| === OPERATIONAL ===
| app/Modules/Operational/Master/routes/web.php
|   - Master: produk, supplier, karyawan, tindakan, paket-tindakan,
|             gudang, satuan, lookup, layanan, paket, patients
|   - Customer-supplier: customers, suppliers
|   - Daftar harga
|
| app/Modules/Operational/Sales/routes/web.php
|   - Dashboard (/)
|   - Transaksi: penjualan, pembelian, examinations, piutang, hutang
|
| app/Modules/Operational/Inventory/routes/web.php
|   - Stok: value-list, items-and-services, bom-formula, satuan,
|            saldo-awal, item-conversions, gudang
|   - Cetak barcode
|
| === FINANCE ===
| app/Modules/Finance/Journal/routes/web.php
|   - Accounting: periode, akun induk, sub akun, COA, saldo awal,
|                 transaksi, jurnal, buku besar, neraca saldo,
|                 neraca, laba rugi, penyesuaian periode, cek saldo
|   - Kas & Bank
|
| app/Modules/Finance/Ledger/routes/web.php
|   - Keuangan: menu laporan keuangan
|
| app/Modules/Finance/Reports/routes/web.php
|   - Laporan: arus kas, examinations, penjualan POS, pendapatan dokter,
|              produk vs jasa, biaya operasional
|   - Arus kas detail: index, compare, export excel, export pdf
|
| === ANALYTICS ===
| app/Modules/Analytics/Dashboard/routes/web.php
|   - Analytics: overview, dokter, layanan, patients, margin produk,
|                layanan terlaris, patients profitable
|
*/

// Core
require __DIR__ . '/../modules/Core/Identity/routes/web.php';
require __DIR__ . '/../modules/Core/Organization/routes/web.php';
require __DIR__ . '/../modules/Core/Authorization/routes/web.php';
require __DIR__ . '/../modules/Core/Platform/routes/web.php';

// Clinical
require __DIR__ . '/../modules/Clinical/Registration/routes/web.php';
require __DIR__ . '/../modules/Clinical/MedicalRecord/routes/web.php';
require __DIR__ . '/../modules/Clinical/Pharmacy/routes/web.php';
require __DIR__ . '/../modules/Clinical/Integration/routes/web.php';

// Operational
require __DIR__ . '/../modules/Operational/Master/routes/web.php';
require __DIR__ . '/../modules/Operational/Sales/routes/web.php';
require __DIR__ . '/../modules/Operational/Inventory/routes/web.php';
require __DIR__ . '/../modules/Operational/Manufacturing/routes/web.php';
require __DIR__ . '/../modules/Operational/Procurement/routes/web.php';

// Finance
require __DIR__ . '/../modules/Finance/Journal/routes/web.php';
require __DIR__ . '/../modules/Finance/Ledger/routes/web.php';
require __DIR__ . '/../modules/Analytics/Reports/routes/web.php';

// Analytics
require __DIR__ . '/../modules/Analytics/Dashboard/routes/web.php';

