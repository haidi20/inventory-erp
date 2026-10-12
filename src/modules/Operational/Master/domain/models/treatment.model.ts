export interface TreatmentModel {
    uuid: string;
    code?: string | null;
    name: string;
    duration_minutes?: number;
    price?: number;
    doctor_fee?: number;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface TreatmentFormData {
    uuid?: string;
    code?: string;
    name: string;
    duration_minutes?: number;
    price?: number;
    doctor_fee?: number;
    is_active: boolean;
}

export interface TreatmentFilters {
    search?: string;
    search_column?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export const TREATMENT_SEARCH_COLUMNS = [
    { id: 'all',  text: 'Semua Kolom' },
    { id: 'code', text: 'Kode' },
    { id: 'name', text: 'Nama' },
] as const;

export const TREATMENT_PER_PAGE_OPTIONS = [
    { id: 5,   text: '5' },
    { id: 10,  text: '10' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const TREATMENT_DEFAULT_FILTERS: Required<TreatmentFilters> = {
    search: '',
    search_column: 'all',
    sort_by: 'name',
    sort_direction: 'asc',
    per_page: 10,
    page: 1,
};
