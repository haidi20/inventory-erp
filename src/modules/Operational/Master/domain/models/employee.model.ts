export interface EmployeeModel {
    uuid: string;
    code?: string | null;
    name: string;
    position?: string | null;
    phone?: string | null;
    email?: string | null;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface EmployeeFormData {
    uuid?: string;
    code?: string;
    name: string;
    position?: string;
    phone?: string;
    email?: string;
    is_active: boolean;
}

export interface EmployeeFilters {
    search?: string;
    search_column?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export const EMPLOYEE_SEARCH_COLUMNS = [
    { id: 'all',   text: 'Semua Kolom' },
    { id: 'name',  text: 'Nama' },
    { id: 'code',  text: 'Kode' },
    { id: 'email', text: 'Email' },
] as const;

export const EMPLOYEE_PER_PAGE_OPTIONS = [
    { id: 5,   text: '5' },
    { id: 10,  text: '10' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const EMPLOYEE_DEFAULT_FILTERS: Required<EmployeeFilters> = {
    search: '',
    search_column: 'all',
    sort_by: 'name',
    sort_direction: 'asc',
    per_page: 10,
    page: 1,
};
