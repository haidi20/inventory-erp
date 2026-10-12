import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
export interface ExaminationTransactionFilters {
    search?: string;
    search_column?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
    date_from?: string;
    date_to?: string;
    status?: string;
}

export interface ExaminationTransactionMeta {
    total: number; current_page: number; per_page: number;
    from?: number; to?: number; last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const EXAMINATION_TRANSACTION_PER_PAGE_OPTIONS = [
    { id: 10, text: '10' }, { id: 25, text: '25' },
    { id: 50, text: '50' }, { id: 100, text: '100' },
] as const;

export const EXAMINATION_TRANSACTION_DEFAULT_FILTERS = {
    search: '', search_column: 'all', sort_by: 'created_at',
    sort_direction: 'desc' as const, per_page: 10, page: 1,
    date_from: '', date_to: '', status: '',
};

export const EXAMINATION_TRANSACTION_COLUMNS: DataTableColumn[] = [
    { key: 'reg_number', label: 'reg_number' },
    { key: 'date', label: 'date' },
    { key: 'patient_name', label: 'patient_name' },
    { key: 'doctor_name', label: 'doctor_name' },
    { key: 'status', label: 'status' },
    { key: 'actions', label: 'Aksi', width: '100px', align: 'center' },
];
