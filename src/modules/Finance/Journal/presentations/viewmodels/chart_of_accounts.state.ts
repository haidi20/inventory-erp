import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export type AccountType = 'asset' | 'liability' | 'equity' | 'revenue' | 'expense';

export interface ChartOfAccountsModel {
    uuid: string;
    code: string;
    name: string;
    type: AccountType;
    parent_uuid?: string | null;
    is_active: boolean;
}

export interface ChartOfAccountsFormData {
    code: string;
    name: string;
    type: AccountType;
    is_active: boolean;
}

export const ACCOUNT_TYPE_OPTIONS = [
    { value: 'asset', label: 'Aset' },
    { value: 'liability', label: 'Kewajiban' },
    { value: 'equity', label: 'Ekuitas' },
    { value: 'revenue', label: 'Pendapatan' },
    { value: 'expense', label: 'Beban' },
] as const;

export const CHART_OF_ACCOUNTS_COLUMNS: DataTableColumn[] = [
    { key: 'code',      label: 'Kode',      width: '140px', sortable: true },
    { key: 'name',      label: 'Nama Akun', sortable: true },
    { key: 'type',      label: 'Tipe',      width: '160px', sortable: true },
    { key: 'is_active', label: 'Status',    width: '120px', align: 'center', sortable: true },
    { key: 'actions',   label: 'Aksi',      width: '110px', align: 'center' },
];
