export interface SupplierModel {
    uuid: string;
    name: string;
    phone?: string | null;
    email?: string | null;
    address?: string | null;
    term_days?: number;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface SupplierFormData {
    uuid?: string;
    name: string;
    phone?: string;
    email?: string;
    address?: string;
    term_days?: number;
    is_active: boolean;
}

export interface SupplierFilters {
    search?: string;
    search_column?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export interface SupplierMeta {
    total: number;
    current_page: number;
    per_page: number;
    from?: number;
    to?: number;
    last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const SUPPLIER_SEARCH_COLUMNS = [
    { id: 'all',   text: 'Semua Kolom' },
    { id: 'name',  text: 'Nama' },
    { id: 'email', text: 'Email' },
    { id: 'phone', text: 'Telepon' },
] as const;

export const SUPPLIER_PER_PAGE_OPTIONS = [
    { id: 5,   text: '5' },
    { id: 10,  text: '10' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const SUPPLIER_DEFAULT_FILTERS: Required<SupplierFilters> = {
    search: '',
    search_column: 'all',
    sort_by: 'name',
    sort_direction: 'asc',
    per_page: 10,
    page: 1,
};
