export interface ItemConversion {
    uuid: string;
    item_uuid: string | null;
    item_name?: string;
    from_unit_uuid: string;
    from_unit_name?: string;
    to_unit_uuid: string;
    to_unit_name?: string;
    conv_ratio: number;
    is_active: boolean;
    updated_at?: string;
}

export interface ItemConversionFilters {
    search?: string;
    sort_by?: string;
    sort_dir?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export interface ItemConversionSummary {
    items: ItemConversion[];
    total: number;
    current_page: number;
    per_page: number;
}
