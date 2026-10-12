import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
import { ref } from 'vue';

export function createUnitUIState() {
    const datatableRef = ref<any>();

    return { datatableRef };
}
// ============================================================
// unit.state.ts
// State types, interfaces, dan constants untuk UnitVM
// ============================================================

export interface UnitModel {
    uuid: string;
    code: string;
    name: string;
    symbol?: string;
    description?: string;
    is_active: boolean;
    is_primary?: boolean;
    is_base_global?: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface UnitFormData {
    uuid?: string;
    code: string;
    name: string;
    symbol: string;
    description: string;
    is_active: boolean;
}

export interface UnitFilters {
    search?: string;
    search_column?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
}

export interface UnitSummary {
    total: number;
}

export interface UnitMeta {
    total: number;
    current_page: number;
    per_page: number;
    from?: number;
    to?: number;
    last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const UNIT_SEARCH_COLUMNS = [
    { id: 'all',  text: 'Semua Kolom' },
    { id: 'code', text: 'Kode' },
    { id: 'name', text: 'Nama' },
] as const;

export const UNIT_PER_PAGE_OPTIONS = [
    { id: 5,   text: '5' },
    { id: 10,  text: '10' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const UNIT_DEFAULT_FILTERS: Required<UnitFilters> = {
    search: '',
    search_column: 'all',
    sort_by: 'code',
    sort_direction: 'asc',
    per_page: 5,
    page: 1,
};

export interface UnitPageProps extends Record<string, unknown> {
    auth?: {
        api_token?: string;
        user?: {
            uuid?: string;
            name?: string;
            email?: string;
            roles?: string[];
            permissions?: string[];
        } | null;
    };
    flash?: {
        success?: string | null;
        error?: string | null;
    };
    errors?: Record<string, string | undefined>;
}

export const UNIT_COLUMNS: DataTableColumn[] = [
    { key: "code",        label: "Kode",      width: "120px", sortable: true },
    { key: "name",        label: "Nama",       sortable: true },
    { key: "symbol",      label: "Simbol",     width: "100px" },
    { key: "description", label: "Keterangan" },
    { key: "is_active",   label: "Aktif",      width: "90px", align: "center" },
    { key: "actions",     label: "Aksi",      width: "120px", align: "center" },
];
