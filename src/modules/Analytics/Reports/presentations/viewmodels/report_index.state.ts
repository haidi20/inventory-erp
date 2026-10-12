export interface ReportMenuItem {
    key: string;
    label: string;
    caption: string;
    icon: string;
    url: string;
}

export interface ReportTab {
    key: string;
    label: string;
    icon: string;
    items: ReportMenuItem[];
}

export const REPORT_TABS: ReportTab[] = [
    {
        key: 'keuangan',
        label: 'Keuangan',
        icon: 'fa-money-bill-trend-up',
        items: [
            { key: 'arus-kas',          label: 'Arus Kas',            caption: 'Laporan arus kas masuk dan keluar',          icon: 'fa-water',               url: '/reports/cash-flow' },
            { key: 'neraca',            label: 'Neraca',              caption: 'Laporan posisi keuangan',                    icon: 'fa-scale-balanced',      url: '/reports/balance-sheet' },
            { key: 'laba-rugi',         label: 'Laba Rugi',           caption: 'Laporan pendapatan dan beban',               icon: 'fa-chart-line',          url: '/reports/income-statement' },
            { key: 'buku-besar',        label: 'Buku Besar',          caption: 'Rincian transaksi per akun',                 icon: 'fa-book',                url: '/reports/general-ledger' },
            { key: 'neraca-saldo',      label: 'Neraca Saldo',        caption: 'Ringkasan saldo debit dan kredit',           icon: 'fa-table-list',          url: '/reports/trial-balance' },
            { key: 'biaya-operasional', label: 'Biaya Operasional',   caption: 'Rincian pengeluaran operasional',            icon: 'fa-file-invoice-dollar', url: '/reports/operational-expenses' },
        ],
    },
    {
        key: 'penjualan',
        label: 'Penjualan',
        icon: 'fa-cart-shopping',
        items: [
            { key: 'penjualan-pos',     label: 'Penjualan POS',       caption: 'Laporan penjualan point of sale',            icon: 'fa-cash-register',       url: '/reports/pos-sales' },
            { key: 'faktur-penjualan',  label: 'Faktur Penjualan',    caption: 'Daftar faktur penjualan',                    icon: 'fa-file-invoice',        url: '/reports/sales-invoice' },
            { key: 'retur-penjualan',   label: 'Retur Penjualan',     caption: 'Laporan barang retur dari pelanggan',        icon: 'fa-rotate-right',        url: '/reports/sales-return' },
            { key: 'piutang',           label: 'Piutang',             caption: 'Laporan piutang pelanggan',                  icon: 'fa-hand-holding-dollar', url: '/reports/receivables' },
            { key: 'obat-vs-jasa',      label: 'Obat vs Jasa',        caption: 'Perbandingan pendapatan obat dan jasa',      icon: 'fa-chart-pie',           url: '/reports/product-vs-service' },
        ],
    },
    {
        key: 'pembelian',
        label: 'Pembelian',
        icon: 'fa-truck',
        items: [
            { key: 'pembelian',         label: 'Pembelian',           caption: 'Laporan transaksi pembelian',                icon: 'fa-bag-shopping',        url: '/reports/purchase' },
            { key: 'hutang',            label: 'Hutang Supplier',     caption: 'Laporan hutang ke pemasok',                  icon: 'fa-file-circle-minus',   url: '/reports/payables' },
            { key: 'retur-pembelian',   label: 'Retur Pembelian',     caption: 'Laporan barang retur ke supplier',           icon: 'fa-rotate-left',         url: '/reports/purchase-return' },
        ],
    },
    {
        key: 'klinis',
        label: 'Klinis',
        icon: 'fa-stethoscope',
        items: [
            { key: 'pemeriksaan',       label: 'Pemeriksaan',         caption: 'Laporan hasil pemeriksaan pasien',           icon: 'fa-clipboard-list',      url: '/reports/examinations' },
            { key: 'pendapatan-dokter', label: 'Pendapatan Dokter',   caption: 'Laporan pendapatan per dokter',              icon: 'fa-user-doctor',         url: '/reports/doctor-income' },
            { key: 'kunjungan-pasien',  label: 'Kunjungan Pasien',    caption: 'Statistik kunjungan pasien',                 icon: 'fa-hospital-user',       url: '/reports/patient-visits' },
        ],
    },
];

export const REPORT_VALID_KEYS = REPORT_TABS.map((t) => t.key);
export const REPORT_DEFAULT_TAB = 'keuangan';
