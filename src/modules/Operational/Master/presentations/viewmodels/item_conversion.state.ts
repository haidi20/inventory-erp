// ============================================================
// item_conversion.state.ts
// State types, interfaces, dan constants untuk ItemConversionVM
// ============================================================

export interface ItemConversionModel {
    uuid?: string;
    item_uuid: string;
    item_name?: string;
    item_code?: string;
    from_unit_uuid: string;
    from_unit_name?: string;
    from_unit_code?: string;
    to_unit_uuid: string;
    to_unit_name?: string;
    to_unit_code?: string;
    conversion_factor: number;
    created_at?: string;
    updated_at?: string;
}

export interface ItemConversionFormData {
    uuid?: string;
    item_uuid: string;
    from_unit_uuid: string;
    to_unit_uuid: string;
    conversion_factor: number;
}

export interface ItemConversionFilters {
    search?: string;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
}

export interface ItemConversionSummary {
    total: number;
    items: ItemConversionModel[];
}

export const ITEM_CONVERSION_PER_PAGE_OPTIONS = [
    { id: 5,   text: '5' },
    { id: 10,  text: '10' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
] as const;

export const ITEM_CONVERSION_DEFAULT_FILTERS: Required<ItemConversionFilters> = {
    search: '',
    sort_by: 'updated_at',
    sort_dir: 'desc',
    per_page: 5,
    page: 1,
};