import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
import { ref } from 'vue';
// company.state.ts � state types untuk CompanyVM
export interface CompanyModel {
    uuid: string;
    name: string;
    phone?: string;
    email?: string;
    address?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface CompanyFormData {
    uuid?: string;
    name: string;
    phone: string;
    email: string;
    address: string;
    is_active: boolean;
}

export interface CompanyFilters {
    search?: string;
    per_page?: number;
    page?: number;
}

export interface CompanyMeta {
    total: number;
    current_page: number;
    per_page: number;
    last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const COMPANY_DEFAULT_FILTERS: Required<CompanyFilters> = {
    search: '',
    per_page: 15,
    page: 1,
};

export const COMPANY_COLUMNS: DataTableColumn[] = [
    { key: "name",      label: "Nama Perusahaan", sortable: true },
    { key: "phone",     label: "Telepon" },
    { key: "email",     label: "Email" },
    { key: "address",   label: "Alamat" },
    { key: "is_active", label: "Aktif",           width: "90px", align: "center" },
    { key: "actions",     label: "Aksi",      width: "120px", align: "center" },
];

export function createCompanyUIState() {
    const datatableRef = ref<any>();

    return { datatableRef };
}
