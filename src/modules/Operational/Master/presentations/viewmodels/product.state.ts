// product.state.ts — state types dan constants untuk ProductVM
export type {
    ProductModel,
    ProductFormData,
    ProductFilters,
    ProductMeta,
    ProductSummary,
    ProductFilterOptions,
    ProductUnitRow,
    ProductOpeningBalanceRow,
    ProductPriceRow,
    ProductDiscountRow,
    ProductAccountRow,
} from '@modules/Operational/Master/domain/models/product.model';

export {
    PRODUCT_DEFAULT_FILTERS,
    PRODUCT_PER_PAGE_OPTIONS,
    PRODUCT_SEARCH_COLUMNS,
} from '@modules/Operational/Master/domain/models/product.model';

import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export const PRODUCT_MAX_IMAGE_ENTRIES = 10;

export const PRODUCT_MAX_FILE_SIZE = 2 * 1024 * 1024;

export const PRODUCT_ALLOWED_FILE_TYPES = new Set(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);

export const PRODUCT_ALLOWED_FILE_EXTENSIONS = new Set(['jpg', 'jpeg', 'png', 'webp', 'pdf']);

export const PRODUCT_TABLE_COLUMNS: DataTableColumn[] = [
    { key: 'code', label: 'Kode Produk', width: '170px', sortable: true },
    { key: 'name', label: 'Nama Produk', sortable: true },
    { key: 'unit_name', label: 'Satuan', width: '110px' },
    { key: 'latest_buy_price', label: 'Harga Beli Terakhir', width: '170px', align: 'right' },
    { key: 'latest_sell_price', label: 'Harga Jual Terakhir', width: '170px', align: 'right' },
    { key: 'base_price_buy', label: 'Harga Beli', width: '140px', align: 'right' },
    { key: 'base_price_sell', label: 'Harga Jual', width: '140px', align: 'right' },
    { key: 'is_active', label: 'Aktif', width: '90px', align: 'center' },
    { key: 'actions', label: 'Aksi', width: '120px', align: 'center' },
];
export interface ProductFormOptions {
    units: { uuid: string; label: string }[];
    kinds: { uuid: string; label: string }[];
    groups: { uuid: string; label: string }[];
    types: { uuid: string; label: string }[];
    brands: { uuid: string; label: string }[];
    origins: { uuid: string; label: string }[];
    grades: { uuid: string; label: string }[];
    warehouses?: { uuid: string; label: string }[];
    priceLevels?: { uuid: string; label: string }[];
    accounts?: { uuid: string; label: string; code: string; name: string }[];
}

export const PRODUCT_ACCOUNT_MAPPINGS = [
    { role: 'sales', label: 'ACC.PENJUALAN' },
    { role: 'inventory', label: 'ACC.PERSEDIAAN' },
    { role: 'sales_discount', label: 'ACC.DISC.PENJUALAN' },
    { role: 'hpp', label: 'ACC.HPP' },
    { role: 'sales_return', label: 'ACC.RET.PENJUALAN' },
    { role: 'purchase_return', label: 'ACC.RET.PEMBELIAN' },
    { role: 'suspend', label: 'ACC.HUTANG SUSPEND' },
] as const;
