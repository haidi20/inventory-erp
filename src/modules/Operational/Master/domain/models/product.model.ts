export interface ProductModel {
    uuid: string;
    code: string;
    name: string;
    barcode?: string | null;
    item_type?: string | null;
    item_name_alt?: string | null;
    category_name?: string | null;
    unit_name?: string | null;
    group_uuid?: string | null;
    type_uuid?: string | null;
    brand_uuid?: string | null;
    origin_uuid?: string | null;
    grade_uuid?: string | null;
    kind?: string | null;
    location?: string | null;
    remarks?: string | null;
    opening_cost?: number | null;
    coa_sales_uuid?: string | null;
    coa_inventory_uuid?: string | null;
    coa_sales_discount_uuid?: string | null;
    coa_hpp_uuid?: string | null;
    coa_sales_return_uuid?: string | null;
    coa_purchase_return_uuid?: string | null;
    coa_suspend_uuid?: string | null;
    latest_buy_price?: number | string | null;
    latest_sell_price?: number | string | null;
    description?: string | null;
    group_name?: string | null;
    type_name?: string | null;
    brand_name?: string | null;
    origin_name?: string | null;
    grade_name?: string | null;
    created_by_name?: string | null;
    updated_by_name?: string | null;
    image?: string | null;
    images?: string[];
    created_at?: string | null;
    updated_at?: string | null;
    base_unit_uuid?: string | null;
    price_buy?: number;
    price_sell?: number;
    min_stock?: number;
    max_stock?: number;
    max_order?: number;
    stock?: number;
    is_active: boolean;
    is_assembly?: boolean;
    brand?: string | null;
    units?: ProductUnitRow[];
    opening_balances?: ProductOpeningBalanceRow[];
    prices?: ProductPriceRow[];
    discounts?: ProductDiscountRow[];
    accounts?: ProductAccountRow[];
}

export interface ProductUnitRow {
    uuid?: string;
    unit_uuid?: string;
    unit_name?: string;
    conversion_ratio: number;
    note?: string;
    is_default: boolean;
    is_active: boolean;
}

export interface ProductOpeningBalanceRow {
    uuid?: string;
    location_uuid?: string;
    location_name?: string;
    opening_qty: number;
    period?: string;
}

export interface ProductPriceRow {
    uuid?: string;
    price_level_uuid?: string;
    price_level_name?: string;
    price_buy: number;
    price_sell: number;
    discount_amount: number;
    discount_percent: number;
    base_qty: number;
    is_active: boolean;
}

export interface ProductDiscountRow {
    uuid?: string;
    quantity: number;
    unit_uuid?: string;
    unit_name?: string;
    discount_percent: number;
    discount_amount: number;
    base_qty: number;
}

export interface ProductAccountRow {
    uuid?: string;
    account_role: string;
    account_uuid?: string;
    account_code?: string;
    account_name?: string;
}

export interface ProductFormData {
    uuid?: string;
    code: string;
    name: string;
    barcode?: string;
    description?: string;
    remarks?: string;
    item_name_alt?: string;
    item_type?: string;
    group_uuid?: string;
    type_uuid?: string;
    brand_uuid?: string;
    origin_uuid?: string;
    grade_uuid?: string;
    kind?: string;
    location?: string;
    base_unit_uuid?: string;
    price_buy?: number;
    price_sell?: number;
    opening_cost?: number;
    min_stock?: number;
    max_stock?: number;
    max_order?: number;
    is_active: boolean;
    is_assembly?: boolean;
    coa_sales_uuid?: string;
    coa_inventory_uuid?: string;
    coa_sales_discount_uuid?: string;
    coa_hpp_uuid?: string;
    coa_sales_return_uuid?: string;
    coa_purchase_return_uuid?: string;
    coa_suspend_uuid?: string;
    image?: File | null;
    images?: File[];
    existing_image_paths?: string[];
    units?: ProductUnitRow[];
    opening_balances?: ProductOpeningBalanceRow[];
    prices?: ProductPriceRow[];
    discounts?: ProductDiscountRow[];
    accounts?: ProductAccountRow[];
}

export interface ProductFilters {
    filter_company_uuid?: string;
    search?: string;
    search_column?: string;
    filter_category?: string;
    filter_barcode?: string;
    filter_code?: string;
    filter_name?: string;
    filter_unit?: string;
    filter_group_uuid?: string;
    filter_keterangan?: string;
    filter_kind?: string;
    filter_type_uuid?: string;
    filter_brand_uuid?: string;
    filter_origin_uuid?: string;
    filter_grade_uuid?: string;
    filter_created_by?: string;
    filter_updated_by?: string;
    filter_created_from?: string;
    filter_created_to?: string;
    filter_updated_from?: string;
    filter_updated_to?: string;
    filter_periode?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export interface ProductMeta {
    total: number;
    current_page: number;
    per_page: number;
    from?: number;
    to?: number;
    last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export interface ProductSummary {
    total: number;
}

export const PRODUCT_SEARCH_COLUMNS = [
    { id: 'all',     text: 'Semua Kolom' },
    { id: 'code',    text: 'Kode' },
    { id: 'name',    text: 'Nama' },
    { id: 'barcode', text: 'Barcode' },
] as const;

export const PRODUCT_PER_PAGE_OPTIONS = [
    { id: 5,   text: '5' },
    { id: 10,  text: '10' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export interface ProductFilterOptions {
    units: string[];
    kinds: string[];
    groups: { uuid: string; label: string }[];
    types: { uuid: string; label: string }[];
    brands: { uuid: string; label: string }[];
    origins: { uuid: string; label: string }[];
    grades: { uuid: string; label: string }[];
    users: { uuid: string; name: string }[];
}

export type ProductDefaultFilters = Required<Pick<
    ProductFilters,
    'search' | 'search_column' | 'filter_category' | 'sort_by' | 'sort_direction' | 'per_page' | 'page'
>>;

export const PRODUCT_DEFAULT_FILTERS: ProductDefaultFilters = {
    search: '',
    search_column: 'all',
    filter_category: '',
    sort_by: 'code',
    sort_direction: 'asc',
    per_page: 10,
    page: 1,
};
