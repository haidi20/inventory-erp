export interface InventoryMenuItem {
    key: string;
    label: string;
    caption: string;
    icon: string;
    url: string;
    items?: InventoryMenuItem[];
}

export interface InventoryTab {
    key: string;
    label: string;
    icon: string;
    items: InventoryMenuItem[];
}

export const INVENTORY_TABS: InventoryTab[] = [
    {
        key: 'utama',
        label: 'Master',
        icon: 'fa-table-cells-large',
        items: [
            {
                key: 'stok',
                label: 'Stok',
                caption: 'Kelola data stok barang',
                icon: 'fa-boxes-stacked',
                url: '#',
                items: [
                    { key: 'daftar-nilai', label: 'Daftar Nilai', caption: 'Kelola daftar nilai lookup', icon: 'fa-list-check', url: '/inventory/master/daftar-nilai' },
                    { key: 'barang-jasa', label: 'Barang dan Jasa', caption: 'Data barang dan jasa', icon: 'fa-cubes', url: '/stock/items-and-services' },
                    { key: 'formula-bom', label: 'Formula (BOM)', caption: 'Kelola formula bill of materials', icon: 'fa-code-branch', url: '/stock/bom-formula' },
                    { key: 'satuan', label: 'Satuan', caption: 'Kelola satuan produk', icon: 'fa-ruler', url: '/master/units' },
                    { key: 'saldo-awal', label: 'Saldo Awal', caption: 'Set saldo awal stok', icon: 'fa-landmark', url: '/stock/opening-balance' },
                    { key: 'konversi-item', label: 'Konversi Item', caption: 'Kelola konversi satuan item', icon: 'fa-code', url: '/stock/item-conversions' },
                    { key: 'gudang', label: 'Gudang', caption: 'Kelola data gudang / lokasi stok', icon: 'fa-warehouse', url: '/master/warehouses' },
                ],
            },
            {
                key: 'customer-supplier',
                label: 'Customer/Supplier',
                caption: 'Data pelanggan dan pemasok',
                icon: 'fa-handshake',
                url: '#',
                items: [
                    { key: 'jangka-waktu-kredit', label: 'Jangka Waktu Kredit', caption: 'Kelola jangka waktu pembayaran', icon: 'fa-calendar-days', url: '/master/payment-terms' },
                    { key: 'pajak', label: 'Pajak', caption: 'Kelola kode pajak (PPN/PPh)', icon: 'fa-percent', url: '/master/taxes' },
                    { key: 'customer', label: 'Customer', caption: 'Data pelanggan', icon: 'fa-user-group', url: '/master/customers' },
                    { key: 'supplier', label: 'Supplier', caption: 'Data pemasok', icon: 'fa-truck-field', url: '/master/supplier' },
                ],
            },
            { key: 'pajak', label: 'Pajak', caption: 'Kelola kode pajak (PPN/PPh)', icon: 'fa-percent', url: '/master/taxes' },
            { key: 'payment-term', label: 'Termin Pembayaran', caption: 'Kelola jangka waktu pembayaran', icon: 'fa-calendar-days', url: '/master/payment-terms' },
            { key: 'perusahaan', label: 'Perusahaan', caption: 'Kelola data perusahaan/klinik', icon: 'fa-building', url: '/master/companies' },
            { key: 'cabang', label: 'Cabang', caption: 'Kelola data cabang operasional', icon: 'fa-code-branch', url: '/master/branches' },
            { key: 'alamat', label: 'Alamat', caption: 'Kelola data alamat', icon: 'fa-location-dot', url: '/inventory/alamat' },
            { key: 'sales', label: 'Sales', caption: 'Kelola penjualan barang', icon: 'fa-cart-shopping', url: '/inventory/sales' },
            { key: 'daftar-harga', label: 'Daftar Harga', caption: 'Kelola harga produk', icon: 'fa-tag', url: '/inventory/daftar-harga' },
            /*
                key: 'account',
                label: 'Account',
                caption: 'Kelola akun dan pembayaran',
                icon: 'fa-credit-card',
                url: '#',
                items: [
                    { key: 'asset', label: 'Asset', caption: 'Kelola data asset', icon: 'fa-sitemap', url: '#' },
                    { key: 'account', label: 'Account', caption: 'Kelola akun dan pembayaran', icon: 'fa-credit-card', url: '/inventory/account' },
                    { key: 'saldo-awal-coa', label: 'Saldo Awal COA', caption: 'Set saldo awal per akun', icon: 'fa-landmark', url: '/accounting/opening-balance' },
                    { key: 'setting-coa', label: 'Setting COA', caption: 'Struktur akun (COA)', icon: 'fa-list-ol', url: '/accounting/coa' },
                ],
            },
            */
            { key: 'user-group', label: 'User Group', caption: 'Kelola grup pengguna', icon: 'fa-users', url: '/inventory/user-group' },
            { key: 'setting-menu', label: 'Setting Menu', caption: 'Konfigurasi menu sistem', icon: 'fa-sliders', url: '/inventory/setting-menu' },
            { key: 'cetakan', label: 'Cetakan', caption: 'Template cetak dokumen', icon: 'fa-print', url: '/inventory/cetakan' },
            { key: 'list-laporan', label: 'List Laporan', caption: 'Daftar laporan tersedia', icon: 'fa-list', url: '/inventory/list-laporan' },
            { key: 'cetak-barcode', label: 'Cetak Barcode', caption: 'Cetak label barcode barang', icon: 'fa-barcode', url: '/inventory/cetak-barcode' },
            { key: 'info-whatsapp', label: 'Info Whatsapp', caption: 'Integrasi notifikasi WhatsApp', icon: 'fa-comment-dots', url: '/inventory/info-whatsapp' },
        ],
    },
    {
        key: 'pembelian',
        label: 'Pembelian',
        icon: 'fa-cart-plus',
        items: [
            { key: 'purchase-inquiry', label: 'Permintaan Pembelian', caption: 'Buat permintaan pengadaan barang', icon: 'fa-file-lines', url: '/inventory/purchase-inquiry' },
            { key: 'purchase-order', label: 'Order Pembelian', caption: 'Buat dan kelola purchase order', icon: 'fa-bag-shopping', url: '/inventory/purchase-order' },
            { key: 'purchase-invoice', label: 'Barang Datang', caption: 'Catat penerimaan barang dari supplier', icon: 'fa-boxes-packing', url: '/inventory/purchase-invoice' },
            { key: 'purchase-return', label: 'Retur Pembelian', caption: 'Kembalikan barang ke supplier', icon: 'fa-rotate-left', url: '/inventory/purchase-return' },
        ],
    },
    {
        key: 'penjualan',
        label: 'Penjualan',
        icon: 'fa-bag-shopping',
        items: [
            { key: 'sales-order', label: 'Sales Order', caption: 'Buat dan kelola order penjualan', icon: 'fa-receipt', url: '/inventory/sales-order' },
            { key: 'delivery-order', label: 'Surat Jalan', caption: 'Dokumen pengiriman barang ke pelanggan', icon: 'fa-truck', url: '/inventory/delivery-order' },
            { key: 'sales-invoice', label: 'Faktur Penjualan', caption: 'Tagihan resmi ke pelanggan', icon: 'fa-file-invoice-dollar', url: '/inventory/sales-invoice' },
            { key: 'sales-return', label: 'Retur Penjualan', caption: 'Terima barang retur dari pelanggan', icon: 'fa-rotate-right', url: '/inventory/sales-return' },
        ],
    },
    {
        key: 'akuntansi',
        label: 'Akuntansi',
        icon: 'fa-calculator',
        items: [
            {
                key: 'setup-akuntansi',
                label: 'Setup Akuntansi',
                caption: 'Konfigurasi dasar sistem akuntansi',
                icon: 'fa-sliders',
                url: '#',
                items: [
                    { key: 'periods', label: 'Periode Akuntansi', caption: 'Kelola periode pembukuan', icon: 'fa-calendar-days', url: '/accounting/periods' },
                    { key: 'parent-accounts', label: 'Akun Induk', caption: 'Kelola akun induk (parent)', icon: 'fa-folder-tree', url: '/accounting/parent-accounts' },
                    { key: 'sub-accounts', label: 'Sub Akun', caption: 'Kelola sub akun dari akun induk', icon: 'fa-sitemap', url: '/accounting/sub-accounts' },
                    { key: 'coa', label: 'Chart of Accounts', caption: 'Struktur akun (COA)', icon: 'fa-list-ol', url: '/accounting/coa' },
                    { key: 'opening-balance', label: 'Saldo Awal', caption: 'Set saldo awal per akun', icon: 'fa-landmark', url: '/accounting/opening-balance' },
                ],
            },
            { key: 'transactions', label: 'Transaksi Akuntansi', caption: 'Kelola jurnal transaksi manual', icon: 'fa-right-left', url: '/accounting/transactions' },
            { key: 'cash-bank', label: 'Kas & Bank', caption: 'Kelola rekening kas dan bank', icon: 'fa-money-bill-wave', url: '/accounting/cash-bank' },
            { key: 'period-adjustments', label: 'Penyesuaian Periode', caption: 'Jurnal penyesuaian akhir periode', icon: 'fa-calendar-check', url: '/accounting/period-adjustments' },
            { key: 'balance-check', label: 'Balance Check', caption: 'Verifikasi keseimbangan debit-kredit', icon: 'fa-scale-balanced', url: '/accounting/balance-check' },
            { key: 'journals', label: 'Daftar Jurnal', caption: 'Semua jurnal umum', icon: 'fa-book', url: '/accounting/journals' },
            { key: 'general-ledger', label: 'Buku Besar', caption: 'Buku besar per akun (GL)', icon: 'fa-book-open-reader', url: '/accounting/general-ledger' },
            { key: 'trial-balance', label: 'Neraca Saldo', caption: 'Neraca saldo (trial balance)', icon: 'fa-table-columns', url: '/accounting/trial-balance' },
            { key: 'balance-sheet', label: 'Neraca', caption: 'Laporan posisi keuangan', icon: 'fa-scale-balanced', url: '/accounting/balance-sheet' },
            { key: 'income-statement', label: 'Laba Rugi', caption: 'Laporan laba rugi (P&L)', icon: 'fa-chart-pie', url: '/accounting/income-statement' },
            { key: 'financial-menu', label: 'Menu Laporan Keuangan', caption: 'Pusat menu laporan keuangan', icon: 'fa-file-lines', url: '/finance/menu' },
        ],
    },
];

export const INVENTORY_VALID_KEYS = INVENTORY_TABS.map((t) => t.key);
export const INVENTORY_DEFAULT_TAB = 'utama';

export function searchMenuItems(items: InventoryMenuItem[], query: string): InventoryMenuItem[] {
    if (!query.trim()) return items;

    const q = query.toLowerCase();

    return items.map(item => ({
        ...item,
        items: item.items ? item.items.filter(sub =>
            sub.label.toLowerCase().includes(q) ||
            sub.caption.toLowerCase().includes(q)
        ) : undefined
    })).filter(item =>
        item.label.toLowerCase().includes(q) ||
        item.caption.toLowerCase().includes(q) ||
        (item.items && item.items.length > 0)
    );
}

export interface SearchResult {
    tabKey: string;
    tabLabel: string;
    item: InventoryMenuItem;
}
