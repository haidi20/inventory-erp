import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
import { ref } from 'vue';

export function createPaymentTermUIState() {
    const datatableRef = ref<any>();

    return { datatableRef };
}
﻿// ============================================================
// payment_term.state.ts
// State types, interfaces, dan constants untuk PaymentTermVM
// ============================================================

export interface PaymentTermModel {
    uuid: string;
    name: string;
    description?: string;
    due_days: number;
    is_active: boolean;
    is_primary: boolean;
    branch_uuid?: string;
    company_uuid?: string;
    created_at?: string;
    updated_at?: string;
}

export interface PaymentTermFormData {
    uuid?: string;
    name: string;
    description: string;
    due_days: number;
    is_active: boolean;
    is_primary: boolean;
}

export interface PaymentTermFilters {
    search?: string;
    per_page?: number;
    page?: number;
}

export interface PaymentTermMeta {
    total: number;
    current_page: number;
    per_page: number;
    from?: number;
    to?: number;
    last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const PAYMENT_TERM_PER_PAGE_OPTIONS = [
    { id: 10,  text: '10' },
    { id: 15,  text: '15' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const PAYMENT_TERM_DEFAULT_FILTERS: Required<PaymentTermFilters> = {
    search: '',
    per_page: 15,
    page: 1,
};

export const PAYMENT_TERM_COLUMNS: DataTableColumn[] = [
    { key: "name",        label: "Nama / Kode",    sortable: true },
    { key: "description", label: "Keterangan" },
    { key: "due_days",    label: "Jangka (Hari)",  width: "130px", align: "right", sortable: true },
    { key: "is_primary",  label: "Default",        width: "90px",  align: "center" },
    { key: "is_active",   label: "Aktif",          width: "90px",  align: "center" },
    { key: "actions",     label: "Aksi",      width: "120px", align: "center" },
];
