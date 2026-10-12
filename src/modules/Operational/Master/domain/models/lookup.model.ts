export enum LookupSearchColumn {
    ALL = 'all',
    NAME = 'name',
    PROGRAM_CODE = 'program_code',
}

export interface LookupModel {
    uuid: string;
    id?: number | null;
    name: string;
    program_code: string;
    description?: string | null;
    user_can_edit_flag?: string;
}

export interface LookupFormData {
    uuid?: string;
    name: string;
    program_code?: string;
    description?: string | null;
    user_can_edit_flag?: string;
}

export interface LookupFilters {
    search?: string;
    search_column?: string;
    sort_by?: 'name' | 'program_code';
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export interface LookupSummary {
    total: number;
}