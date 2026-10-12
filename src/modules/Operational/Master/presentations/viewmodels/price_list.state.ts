import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
export interface PriceListFilters {
    search?: string;
    tab?: string;
    page?: number;
    per_page?: number;
}

export const PRICE_LIST_PER_PAGE_OPTIONS = [
    { id: 10, text: '10' }, { id: 25, text: '25' },
    { id: 50, text: '50' }, { id: 100, text: '100' },
] as const;

export const PRICE_LIST_DEFAULT_FILTERS = {
    search: '', tab: '', per_page: 10, page: 1,
};

export const PRICE_LIST_COLUMNS: DataTableColumn[] = [
    { key: 'code',       label: 'Kode',        width: '120px', sortable: true },
    { key: 'name',       label: 'Nama Produk',  sortable: true },
    { key: 'unit_name',  label: 'Satuan',       width: '100px' },
    { key: 'price_sell', label: 'Harga Jual',   width: '140px', align: 'right' },
    { key: 'price_buy',  label: 'Harga Beli',   width: '140px', align: 'right' },
    { key: 'stock',      label: 'Stok',         width: '90px',  align: 'right' },
    { key: 'is_active',  label: 'Aktif',        width: '90px',  align: 'center' },
];
