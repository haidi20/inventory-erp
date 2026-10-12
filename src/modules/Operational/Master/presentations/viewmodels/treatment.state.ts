import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
export interface TreatmentModel {
    uuid: string;
    code?: string | null;
    name: string;
    duration_minutes?: number;
    price?: number;
    doctor_fee?: number;
    is_active: boolean;
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

export interface TreatmentMeta {
    total: number; current_page: number; per_page: number;
    from?: number; to?: number; last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const TREATMENT_SEARCH_COLUMNS = [
    { id: 'all',  text: 'Semua Kolom' },
    { id: 'code', text: 'Kode' },
    { id: 'name', text: 'Nama' },
] as const;

export const TREATMENT_PER_PAGE_OPTIONS = [
    { id: 5, text: '5' }, { id: 10, text: '10' }, { id: 25, text: '25' },
    { id: 50, text: '50' }, { id: 100, text: '100' },
] as const;

export const TREATMENT_DEFAULT_FILTERS = {
    search: '', search_column: 'all', sort_by: 'name',
    sort_direction: 'asc' as const, per_page: 10, page: 1,
};

export const TREATMENT_COLUMNS: DataTableColumn[] = [
    { key: 'code',             label: 'Kode',          width: '120px', sortable: true },
    { key: 'name',             label: 'Nama',          sortable: true },
    { key: 'duration_minutes', label: 'Durasi (mnt)',  width: '120px', align: 'right' },
    { key: 'price',            label: 'Harga',         width: '130px', align: 'right' },
    { key: 'doctor_fee',       label: 'Fee Dokter',    width: '130px', align: 'right' },
    { key: 'is_active',        label: 'Aktif',         width: '90px',  align: 'center' },
    { key: 'actions',          label: 'Aksi',          width: '100px', align: 'center' },
];
