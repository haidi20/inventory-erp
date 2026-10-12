import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
export interface PriceLevelModel {
    uuid: string;
    code: string;
    name: string;
    description?: string | null;
    is_active: boolean;
    is_primary?: boolean;
}

export interface PriceLevelFormData {
    uuid?: string;
    code: string;
    name: string;
    description?: string;
    is_active: boolean;
}

export interface PriceLevelFilters {
    search?: string;
    page?: number;
    per_page?: number;
}

export interface PriceLevelMeta {
    total: number; current_page: number; per_page: number;
    from?: number; to?: number; last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const PRICE_LEVEL_PER_PAGE_OPTIONS = [
    { id: 5, text: '5' }, { id: 10, text: '10' }, { id: 25, text: '25' },
    { id: 50, text: '50' }, { id: 100, text: '100' },
] as const;

export const PRICE_LEVEL_DEFAULT_FILTERS = {
    search: '', per_page: 10, page: 1,
};

export const PRICE_LEVEL_COLUMNS: DataTableColumn[] = [
    { key: 'code',        label: 'Kode',       width: '120px', sortable: true },
    { key: 'name',        label: 'Nama',        sortable: true },
    { key: 'description', label: 'Keterangan' },
    { key: 'is_active',   label: 'Aktif',       width: '90px', align: 'center' },
    { key: 'actions',     label: 'Aksi',        width: '100px', align: 'center' },
];
