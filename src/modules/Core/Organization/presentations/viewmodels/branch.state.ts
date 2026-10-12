import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
import { ref } from 'vue';
// branch.state.ts � state types untuk BranchVM
export interface BranchModel {
    uuid: string;
    company_uuid: string;
    name: string;
    short_name: string;
    description?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface BranchFormData {
    uuid?: string;
    name: string;
    short_name: string;
    description: string;
    is_active: boolean;
}

export interface BranchFilters {
    search?: string;
    per_page?: number;
    page?: number;
}

export interface BranchMeta {
    total: number;
    current_page: number;
    per_page: number;
    last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const BRANCH_DEFAULT_FILTERS: Required<BranchFilters> = {
    search: '',
    per_page: 15,
    page: 1,
};

export const BRANCH_COLUMNS: DataTableColumn[] = [
    { key: "short_name",  label: "Kode",         width: "100px", sortable: true },
    { key: "name",        label: "Nama Cabang",   sortable: true },
    { key: "description", label: "Keterangan" },
    { key: "is_active",   label: "Aktif",         width: "90px", align: "center" },
    { key: "actions",     label: "Aksi",      width: "120px", align: "center" },
];

export function createBranchUIState() {
    const datatableRef = ref<any>();

    return { datatableRef };
}
