import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
import { ref } from 'vue';

export function createLookupUIState() {
    const datatableRef = ref<any>();

    return { datatableRef };
}
// ============================================================
// lookup.state.ts
// State types, interfaces, dan constants untuk LookupVM
// ============================================================

export interface LookupModel {
    uuid: string;
    name: string;
    program_code: string;
    description?: string;
    is_active: boolean;
    values_count?: number;
    created_at?: string;
    updated_at?: string;
}

export interface LookupFormData {
    uuid?: string;
    name: string;
    program_code: string;
    description: string;
    is_active: boolean;
}

export interface LookupFilters {
    search?: string;
    search_column?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
}

export interface LookupMeta {
    total: number;
    current_page: number;
    per_page: number;
    from?: number;
    to?: number;
    last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const LOOKUP_SEARCH_COLUMNS = [
    { id: 'all',          text: 'Semua Kolom' },
    { id: 'name',         text: 'Nama' },
    { id: 'program_code', text: 'Kode Program' },
] as const;

export const LOOKUP_PER_PAGE_OPTIONS = [
    { id: 10,  text: '10' },
    { id: 15,  text: '15' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const LOOKUP_DEFAULT_FILTERS: Required<LookupFilters> = {
    search: '',
    search_column: 'all',
    sort_by: 'name',
    sort_direction: 'asc',
    per_page: 15,
    page: 1,
};

export interface LookupPageProps extends Record<string, unknown> {
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

export const LOOKUP_COLUMNS: DataTableColumn[] = [
    { key: "set_code",    label: "Kode Set",   width: "150px", sortable: true },
    { key: "set_name",    label: "Nama Set",   sortable: true },
    { key: "description", label: "Keterangan" },
    { key: "scope",       label: "Scope",      width: "110px", align: "center" },
    { key: "is_active",   label: "Aktif",      width: "90px",  align: "center" },
    { key: "actions",     label: "Aksi",      width: "120px", align: "center" },
];
