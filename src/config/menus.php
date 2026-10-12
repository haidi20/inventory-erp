<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sidebar Navigation Menu
    |--------------------------------------------------------------------------
    |
    | Struktur menu sidebar yang mendukung pengecekan permission dan active state.
    |
    */
    'sidebar_menu' => [
        [
            'key' => 'dashboard',
            'label' => 'Dashboard',
            'icon' => 'fa-gauge-high',
            'url' => '/',
            'active_segment' => '/',
            'permission' => 'dashboard.view',
        ],
        [
            'key' => 'inventory',
            'label' => 'Inventory',
            'icon' => 'fa-boxes-stacked',
            'url' => '/inventory',
            'active_segment' => [
                'inventory*',
                'stock/*',
                'master/products*',
                'master/units*',
                'master/warehouses*',
                'master/payment-terms*',
                'master/taxes*',
                'master/customers*',
                'master/supplier*',
                'master/companies*',
                'master/branches*',
                'accounting*',
                'finance/menu*',
            ],
            'permission' => 'inventory.view',
        ],
        [
            'key' => 'laporan',
            'label' => 'Laporan',
            'icon' => 'fa-file-lines',
            'url' => '/laporan',
            'active_segment' => [
                'laporan*',
                'analytics/*',
                'reports/*',
            ],
            'permission' => 'laporan.view',
        ],
        
        // [
        //     'key' => 'farmasi',
        //     'label' => 'Farmasi',
        //     'icon' => 'fa-pills',
        //     'url' => '/farmasi/dashboard',
        //     'active_segment' => 'farmasi*',
        //     // 'permission' => 'farmasi.view',
        // ],
        // [
        //     'key' => 'himbauan',
        //     'label' => 'Himbauan',
        //     'icon' => 'fa-bullhorn',
        //     'url' => '/himbauan',
        //     'active_segment' => 'himbauan*',
        //     'permission' => 'himbauan.view',
        // ],
        // [
        //     'key' => 'master',
        //     'label' => 'Master Data',
        //     'icon' => 'fa-folder-open',
        //     'permission' => 'master.view', // General check for the group
        //     'items' => [
        //         ['label' => 'Daftar Nilai', 'url' => '/masters/lookups', 'active_segment' => 'masters/lookups*', 'permission' => 'master.lookup.view'],
        //         ['label' => 'Satuan', 'url' => '/masters/units', 'active_segment' => 'masters/units*', 'permission' => 'master.manage'],
        //         // konfersi item
        //         // ['label' => 'Data Obat/Alkes', 'url' => '/master/products', 'active_segment' => 'master/products*', 'permission' => 'master.produk.view'],
        //         // ['label' => 'Data Paket Tindakan', 'url' => '/master/packages', 'active_segment' => 'master/packages*', 'permission' => 'master.paket.view'],
        //         // ['label' => 'Data Tindakan', 'url' => '/master/services', 'active_segment' => 'master/services*', 'permission' => 'master.layanan.view'],
        //         // ['label' => 'Data Supplier', 'url' => '/master/suppliers', 'active_segment' => 'master/suppliers*', 'permission' => 'master.supplier.view'],
        //         // ['label' => 'Data Pasien', 'url' => '/master/patients', 'active_segment' => 'master/patients*', 'permission' => 'master.pasien.view'],
        //         // ['label' => 'Data Pegawai/Dokter', 'url' => '/master/employees', 'active_segment' => 'master/employees*', 'permission' => 'master.pegawai.view'],
        //     ],
        // ],
        // [
        //     'key' => 'transaksi',
        //     'label' => 'Transaksi',
        //     'icon' => 'fa-receipt',
        //     'permission' => 'transaksi.view',
        //     'items' => [
        //         ['label' => 'Pembelian', 'url' => '/transaksi/pembelian', 'active_segment' => 'transaksi/pembelian*', 'permission' => 'transaksi.pembelian.view'],
        //         ['label' => 'Hutang Supplier', 'url' => '/transaksi/hutang', 'active_segment' => 'transaksi/hutang*', 'permission' => 'transaksi.hutang.view'],
        //         ['label' => 'Pelayanan Poli / Dokter', 'url' => '/transaksi/pemeriksaan', 'active_segment' => 'transaksi/pemeriksaan*', 'permission' => 'transaksi.pemeriksaan.view'],
        //         ['label' => 'Monitoring Pelayanan Dokter', 'url' => '/transaksi/tugas-dokter', 'active_segment' => 'transaksi/tugas-dokter*', 'permission' => 'transaksi.tugas_dokter.view'],
        //         ['label' => 'Input Permintaan Lab / Radiologi', 'url' => '/transaksi/permintaan-penunjang', 'active_segment' => 'transaksi/permintaan-penunjang*', 'permission' => 'transaksi.permintaan_penunjang.view'],
        //         ['label' => 'Petugas Input Hasil Pemeriksaan ke RME', 'url' => '/transaksi/hasil-penunjang-rme', 'active_segment' => 'transaksi/hasil-penunjang-rme*', 'permission' => 'transaksi.hasil_penunjang.view'],
        //         ['label' => 'Penjualan Obat (POS)', 'url' => '/transaksi/penjualan', 'active_segment' => 'transaksi/penjualan*', 'permission' => 'transaksi.penjualan.view'],
        //         ['label' => 'Piutang Pasien & BPJS', 'url' => '/transaksi/piutang', 'active_segment' => 'transaksi/piutang*', 'permission' => 'transaksi.piutang.view'],
        //     ],
        // ],
        // [
        //     'key' => 'bpjs',
        //     'label' => 'Layanan BPJS',
        //     'icon' => 'fa-id-card',
        //     'permission' => 'bpjs.view',
        //     'items' => [
        //         ['label' => 'Pendaftaran Peserta', 'url' => '/bpjs/pendaftaran', 'active_segment' => 'bpjs/pendaftaran*', 'permission' => 'bpjs.pendaftaran.view'],
        //         ['label' => 'Verifikasi Kepesertaan', 'url' => '/bpjs/verifikasi', 'active_segment' => 'bpjs/verifikasi*', 'permission' => 'bpjs.verifikasi.view'],
        //         ['label' => 'Rujukan Online', 'url' => '/bpjs/rujukan', 'active_segment' => 'bpjs/rujukan*', 'permission' => 'bpjs.rujukan.view'],
        //         ['label' => 'SEP (Surat Eligibilitas)', 'url' => '/bpjs/sep', 'active_segment' => 'bpjs/sep*', 'permission' => 'bpjs.sep.view'],
        //         ['label' => 'Monitoring Klaim', 'url' => '/bpjs/monitoring-klaim', 'active_segment' => 'bpjs/monitoring-klaim*', 'permission' => 'bpjs.monitoring_klaim.view'],
        //         ['label' => 'Pengaturan API', 'url' => '/bpjs/pengaturan', 'active_segment' => 'bpjs/pengaturan*', 'permission' => 'settings.bpjs.manage'],
        //     ],
        // ],
        // [
        //     'key' => 'satusehat',
        //     'label' => 'Layanan SATUSEHAT',
        //     'icon' => 'fa-heart-pulse',
        //     'permission' => 'satusehat.view',
        //     'items' => [
        //         ['label' => 'Sinkronisasi Pasien', 'url' => '/satusehat/sync', 'active_segment' => 'satusehat/sync*', 'permission' => 'satusehat.sync.manage'],
        //         ['label' => 'Kirim Data Kunjungan', 'url' => '/satusehat/encounter', 'active_segment' => 'satusehat/encounter*', 'permission' => 'satusehat.encounter.view'],
        //         ['label' => 'Kirim Rekam Medis', 'url' => '/satusehat/fhir', 'active_segment' => 'satusehat/fhir*', 'permission' => 'satusehat.fhir.view'],
        //         ['label' => 'Pengaturan Platform', 'url' => '/satusehat/pengaturan', 'active_segment' => 'satusehat/pengaturan*', 'permission' => 'satusehat.config.manage'],
        //     ],
        // ],
        // [
        //     'key' => 'stok',
        //     'label' => 'Stok',
        //     'icon' => 'fa-boxes-stacked',
        //     'permission' => 'stok.view',
        //     'items' => [
        //         ['label' => 'Daftar Nilai', 'url' => '/stok/daftar-nilai', 'active_segment' => 'stok/daftar-nilai*', 'permission' => 'stok.daftar_nilai.view'],
        //         ['label' => 'Barang dan Jasa', 'url' => '/stok/barang-dan-jasa', 'active_segment' => 'stok/barang-dan-jasa*', 'permission' => 'stok.barang_jasa.view'],
        //         ['label' => 'Formula (BOM)', 'url' => '/stok/formula-bom', 'active_segment' => 'stok/formula-bom*', 'permission' => 'stok.formula_bom.view'],
        //         ['label' => 'Satuan', 'url' => '/stok/satuan', 'active_segment' => 'stok/satuan*', 'permission' => 'stok.satuan.view'],
        //         ['label' => 'Saldo Awal', 'url' => '/stok/saldo-awal', 'active_segment' => 'stok/saldo-awal*', 'permission' => 'stok.saldo_awal.view'],
        //         ['label' => 'Konversi Item', 'url' => '/stok/konversi-item', 'active_segment' => 'stok/konversi-item*', 'permission' => 'stok.konversi_item.view'],
        //         ['label' => 'Gudang', 'url' => '/stok/gudang', 'active_segment' => 'stok/gudang*', 'permission' => 'stok.gudang.view'],
        //     ],
        // ],
        // [
        //     'key' => 'harga',
        //     'label' => 'Daftar Harga',
        //     'icon' => 'fa-tags',
        //     'url' => '/daftar-harga',
        //     'active_segment' => 'daftar-harga*',
        //     'permission' => 'pricing.view',
        // ],
        // [
        //     'key' => 'analitik',
        //     'label' => 'Analitik Klinik',
        //     'icon' => 'fa-chart-line',
        //     'permission' => 'analitik.view',
        //     'items' => [
        //         ['label' => 'Overview Intelijen', 'url' => '/analytics/overview', 'active_segment' => 'analytics/overview*', 'permission' => 'analitik.overview.view'],
        //         ['label' => 'Profit per Dokter', 'url' => '/analytics/profit-dokter', 'active_segment' => 'analytics/profit-dokter*', 'permission' => 'analitik.profit_dokter.view'],
        //         ['label' => 'Profit per Jenis Tindakan', 'url' => '/analytics/profit-tindakan', 'active_segment' => 'analytics/profit-tindakan*', 'permission' => 'analitik.profit_tindakan.view'],
        //         ['label' => 'Profit per Pasien', 'url' => '/analytics/profit-pasien', 'active_segment' => 'analytics/profit-pasien*', 'permission' => 'analitik.profit_pasien.view'],
        //         ['label' => 'Margin Obat/Alkes', 'url' => '/analytics/margin-produk', 'active_segment' => 'analytics/margin-produk*', 'permission' => 'analitik.margin_produk.view'],
        //         ['label' => 'Tindakan Terlaris', 'url' => '/analytics/tindakan-terlaris', 'active_segment' => 'analytics/tindakan-terlaris*', 'permission' => 'analitik.tindakan_terlaris.view'],
        //         ['label' => 'Pasien Profitable', 'url' => '/analytics/pasien-profitable', 'active_segment' => 'analytics/pasien-profitable*', 'permission' => 'analitik.pasien_profitable.view'],
        //     ],
        // ],
        // [
        //     'key' => 'laporan',
        //     'label' => 'Laporan',
        //     'icon' => 'fa-file-lines',
        //     'permission' => 'laporan.view',
        //     'items' => [
        //         ['label' => 'Arus Kas', 'url' => '/laporan/arus-kas', 'active_segment' => 'laporan/arus-kas*', 'permission' => 'laporan.arus_kas.view'],
        //         ['label' => 'Laporan Pemeriksaan', 'url' => '/laporan/pemeriksaan', 'active_segment' => 'laporan/pemeriksaan*', 'permission' => 'laporan.pemeriksaan.view'],
        //         ['label' => 'Laporan Penjualan POS', 'url' => '/laporan/penjualan-pos', 'active_segment' => 'laporan/penjualan-pos*', 'permission' => 'laporan.penjualan_pos.view'],
        //         ['label' => 'Pendapatan Dokter', 'url' => '/laporan/pendapatan-dokter', 'active_segment' => 'laporan/pendapatan-dokter*', 'permission' => 'laporan.pendapatan_dokter.view'],
        //         ['label' => 'Obat vs Jasa', 'url' => '/laporan/obat-vs-jasa', 'active_segment' => 'laporan/obat-vs-jasa*', 'permission' => 'laporan.obat_vs_jasa.view'],
        //         ['label' => 'Biaya Operasional', 'url' => '/laporan/biaya-operasional', 'active_segment' => 'laporan/biaya-operasional*', 'permission' => 'laporan.biaya_operasional.view'],
        //     ],
        // ],
        // [
        //     'key' => 'complaints',
        //     'label' => 'Pengaduan & Saran',
        //     'icon' => 'fa-comments',
        //     'url' => '/operational/complaints',
        //     'active_segment' => 'operational/complaints*',
        //     'permission' => 'complaint.view',
        // ],
        [
            'key' => 'settings',
            'label' => 'Pengaturan',
            'icon' => 'fa-gear',
            'permission' => 'settings.view',
            'items' => [
                ['label' => 'Pengguna', 'url' => '/settings/users', 'active_segment' => 'settings/users*', 'permission' => 'settings.user.view'],
                ['label' => 'Role Permission', 'url' => '/settings/roles', 'active_segment' => 'settings/roles*', 'permission' => 'settings.role.view'],
                ['label' => 'Profil Klinik', 'url' => '/settings', 'active_segment' => 'settings', 'permission' => 'settings.view'],
                ['label' => 'Backup Data', 'url' => '/settings/backup', 'active_segment' => 'settings/backup*', 'permission' => 'settings.view'],
            ],
        ],
        [
            'key' => 'monitoring',
            'label' => 'Monitoring',
            'icon' => 'fa-chart-simple',
            'url' => '/pulse',
            'active_segment' => 'pulse*',
            'permission' => 'system.monitor.view',
        ],
    ],
];

