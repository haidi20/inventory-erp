import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
import { ref } from 'vue';

export function createTaxUIState() {
    const datatableRef = ref<any>();

    return { datatableRef };
}
﻿// ============================================================
// tax.state.ts
// State types, interfaces, dan constants untuk TaxVM
// ============================================================

export interface TaxModel {
    uuid: string;
    tax_code: string;
    percentage: number;
    is_active: boolean;
    is_primary: boolean;
    is_pph: boolean;
    branch_uuid?: string;
    company_uuid?: string;
    created_at?: string;
    updated_at?: string;
}

export interface TaxFormData {
    uuid?: string;
    tax_code: string;
    percentage: number;
    is_active: boolean;
    is_primary: boolean;
    is_pph: boolean;
}

export interface TaxFilters {
    search?: string;
    per_page?: number;
    page?: number;
}

export interface TaxMeta {
    total: number;
    current_page: number;
    per_page: number;
    from?: number;
    to?: number;
    last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const TAX_PER_PAGE_OPTIONS = [
    { id: 10,  text: '10' },
    { id: 15,  text: '15' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const TAX_DEFAULT_FILTERS: Required<TaxFilters> = {
    search: '',
    per_page: 15,
    page: 1,
};

export const TAX_COLUMNS: DataTableColumn[] = [
    { key: "tax_code",   label: "Kode Pajak",    sortable: true },
    { key: "percentage", label: "Persentase (%)", width: "140px", align: "right", sortable: true },
    { key: "is_pph",     label: "PPh",            width: "80px",  align: "center" },
    { key: "is_primary", label: "Default",        width: "90px",  align: "center" },
    { key: "is_active",  label: "Aktif",          width: "90px",  align: "center" },
    { key: "actions",     label: "Aksi",      width: "120px", align: "center" },
];
