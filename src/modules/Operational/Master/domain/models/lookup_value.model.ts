export interface LookupValueModel {
    uuid: string;
    lookup_set_id?: number | string;
    lookup_set_uuid?: string;
    program_code?: string;
    display_name: string;
    description?: string;
    seq?: number;
    active_flag?: string;
    primary_flag?: string;
    start_date?: number | null;
    end_date?: number | null;
    max_stock?: number | null;
    price_up_beli?: number;
    diskon_pilih?: number;
    persen1?: number;
    persen2?: number;
    details?: Array<{ index: number; value: string }>;
}

export interface LookupValueFilters {
    search?: string;
    page?: number;
    per_page?: number;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
}

export interface LookupValueFormData {
    uuid?: string;
    lookup_set_uuid?: string;
    display_name: string;
    program_code?: string;
    description?: string;
    seq?: number;
    active_flag?: string;
    primary_flag?: string;
    start_date?: string | null;
    end_date?: string | null;
    max_stock?: number | null;
}

export interface LookupValueSummary {
    total: number;
}