import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
export interface EmployeeModel {
    uuid: string;
    code?: string | null;
    name: string;
    position?: string | null;
    phone?: string | null;
    email?: string | null;
    is_active: boolean;
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

export interface EmployeeMeta {
    total: number; current_page: number; per_page: number;
    from?: number; to?: number; last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const EMPLOYEE_SEARCH_COLUMNS = [
    { id: 'all',   text: 'Semua Kolom' },
    { id: 'name',  text: 'Nama' },
    { id: 'code',  text: 'Kode' },
    { id: 'email', text: 'Email' },
] as const;

export const EMPLOYEE_PER_PAGE_OPTIONS = [
    { id: 5, text: '5' }, { id: 10, text: '10' }, { id: 25, text: '25' },
    { id: 50, text: '50' }, { id: 100, text: '100' },
] as const;

export const EMPLOYEE_DEFAULT_FILTERS = {
    search: '', search_column: 'all', sort_by: 'name',
    sort_direction: 'asc' as const, per_page: 10, page: 1,
};

export const EMPLOYEE_COLUMNS: DataTableColumn[] = [
    { key: 'code',      label: 'Kode',      width: '120px', sortable: true },
    { key: 'name',      label: 'Nama',      sortable: true },
    { key: 'position',  label: 'Jabatan',   width: '150px' },
    { key: 'phone',     label: 'Telepon',   width: '140px' },
    { key: 'email',     label: 'Email',     width: '180px' },
    { key: 'is_active', label: 'Aktif',     width: '90px', align: 'center' },
    { key: 'actions',   label: 'Aksi',      width: '100px', align: 'center' },
];
