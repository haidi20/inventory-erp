export interface LovSetModel {
    uuid: string;
    id?: number;
    name: string;
    program_code?: string | null;
    description?: string | null;
    user_can_edit_flag?: string;
}

export interface LovSetFormData {
    uuid?: string;
    name: string;
    program_code?: string;
    description?: string;
}

export interface LovSetFilters {
    search?: string;
    search_column?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export interface LovSetMeta {
    total: number; current_page: number; per_page: number;
    from?: number; to?: number; last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const LOV_SET_PER_PAGE_OPTIONS = [
    { id: 5, text: '5' }, { id: 10, text: '10' }, { id: 25, text: '25' },
    { id: 50, text: '50' }, { id: 100, text: '100' },
] as const;

export const LOV_SET_DEFAULT_FILTERS = {
    search: '', search_column: 'all', sort_by: 'name',
    sort_direction: 'asc' as const, per_page: 10, page: 1,
};
