// farmacy_management.state.ts � state types untuk FarmacyManagementVM
export interface ManagementContactModel {
    uuid: string;
    name: string;
    phone?: string;
    email?: string;
    type: string;
}

export interface FarmacyManagementState {
    isLoading: boolean;
}

export const FARMACY_MASTER_MENUS = [
    { title: 'Kontak', desc: 'Kelola supplier, customer, karyawan', icon: 'fa-address-book', color: '#3b82f6', href: '/farmasi/manajemen' },
    { title: 'Produk', desc: 'Kelola produk/obat (nama, kode, batch)', icon: 'fa-capsules', color: '#10b981', href: '/masters/products' },
    { title: 'Harga Produk', desc: 'Atur harga per produk, histori', icon: 'fa-tags', color: '#f59e0b', href: '/masters/product-prices' },
    { title: 'Grup Produk', desc: 'Kelompokkan produk (Antibiotik, dll)', icon: 'fa-layer-group', color: '#8b5cf6', href: '/masters/product-groups' },
    { title: 'Kategori Produk', desc: 'Klasifikasi produk (Tablet, Sirup)', icon: 'fa-folder-open', color: '#ec4899', href: '/masters/product-categories' },
    { title: 'Merek', desc: 'Kelola merek produk', icon: 'fa-copyright', color: '#f97316', href: '/masters/brands' },
    { title: 'Satuan', desc: 'Satuan produk (pcs, box, strip)', icon: 'fa-ruler-combined', color: '#06b6d4', href: '/masters/units' },
    { title: 'Unit Usaha', desc: 'Cabang/unit bisnis (multi-tenant)', icon: 'fa-building', color: '#6366f1', href: '/masters/business-units' },
    { title: 'Gudang', desc: 'Lokasi penyimpanan stok', icon: 'fa-warehouse', color: '#64748b', href: '/masters/warehouses' },
];

export const FARMACY_ACCOUNTING_MENUS = [
    { title: 'Grup Akun', desc: 'Kelompokkan akun (Aset, Liabilitas)', icon: 'fa-folder-tree', color: '#475569', href: '/masters/account-groups' },
    { title: 'Akun', desc: 'Daftar akun (Chart of Accounts)', icon: 'fa-list-check', color: '#0f172a', href: '/farmasi/akuntansi' },
    { title: 'Jenis Pembayaran', desc: 'Metode (Tunai, Transfer, QRIS)', icon: 'fa-credit-card', color: '#059669', href: '/masters/payment-methods' },
    { title: 'Klasifikasi Uang Muka', desc: 'Jenis uang muka (DP, deposit)', icon: 'fa-money-bill-wave', color: '#d97706', href: '/masters/advance-classifications' },
    { title: 'Kategori Harta Tetap', desc: 'Klasifikasi aset (Bangunan, Kendaraan)', icon: 'fa-monument', color: '#7c3aed', href: '/masters/fixed-asset-categories' },
];

export const FARMACY_SETTING_MENUS = [
    { title: 'Data Farmasi', desc: 'Pengaturan apoteker, margin obat, dll', icon: 'fa-prescription-bottle-medical', color: '#be123c', href: '/masters/pharmacy-settings' },
];