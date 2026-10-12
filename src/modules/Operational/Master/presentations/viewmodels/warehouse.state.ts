import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
import { ref } from 'vue';

export function createWarehouseUIState() {
    const datatableRef = ref<any>();

    return { datatableRef };
}
﻿// ============================================================
// warehouse.state.ts
// State types, interfaces, dan constants untuk WarehouseVM
// ============================================================

export interface WarehouseModel {
    uuid: string;
    code: string;
    name: string;
    description?: string;
    is_active: boolean;
    can_sell: boolean;
    is_primary: boolean;
    branch_uuid?: string;
    company_uuid?: string;
    created_at?: string;
    updated_at?: string;
}

export interface WarehouseFormData {
    uuid?: string;
    code: string;
    name: string;
    description: string;
    is_active: boolean;
    can_sell: boolean;
    is_primary: boolean;
}

export interface WarehouseFilters {
    search?: string;
    search_column?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
}

export interface WarehouseMeta {
    total: number;
    current_page: number;
    per_page: number;
    from?: number;
    to?: number;
    last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const WAREHOUSE_SEARCH_COLUMNS = [
    { id: 'all',  text: 'Semua Kolom' },
    { id: 'code', text: 'Kode' },
    { id: 'name', text: 'Nama' },
] as const;

export const WAREHOUSE_PER_PAGE_OPTIONS = [
    { id: 10,  text: '10' },
    { id: 15,  text: '15' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const WAREHOUSE_DEFAULT_FILTERS: Required<WarehouseFilters> = {
    search: '',
    search_column: 'all',
    sort_by: 'code',
    sort_direction: 'asc',
    per_page: 15,
    page: 1,
};

export const WAREHOUSE_COLUMNS: DataTableColumn[] = [
    { key: "code",        label: "Kode",       width: "200px", sortable: true },
    { key: "name",        label: "Nama",        sortable: true },
    { key: "description", label: "Keterangan" },
    { key: "is_primary",  label: "Default",    width: "90px", align: "center" },
    { key: "can_sell",    label: "Bisa Jual",  width: "90px", align: "center" },
    { key: "is_active",   label: "Aktif",      width: "90px", align: "center" },
    { key: "actions",     label: "Aksi",      width: "120px", align: "center" },
];
