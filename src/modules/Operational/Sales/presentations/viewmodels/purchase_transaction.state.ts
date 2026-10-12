import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
export interface PurchaseTransactionFilters {
    search?: string;
    search_column?: string;
    sort_by?: string;
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
    date_from?: string;
    date_to?: string;
    status?: string;
}

export interface PurchaseTransactionMeta {
    total: number; current_page: number; per_page: number;
    from?: number; to?: number; last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export const PURCHASE_TRANSACTION_PER_PAGE_OPTIONS = [
    { id: 10, text: '10' }, { id: 25, text: '25' },
    { id: 50, text: '50' }, { id: 100, text: '100' },
] as const;

export const PURCHASE_TRANSACTION_DEFAULT_FILTERS = {
    search: '', search_column: 'all', sort_by: 'created_at',
    sort_direction: 'desc' as const, per_page: 10, page: 1,
    date_from: '', date_to: '', status: '',
};

export const PURCHASE_TRANSACTION_COLUMNS: DataTableColumn[] = [
    { key: 'po_number', label: 'po_number' },
    { key: 'date', label: 'date' },
    { key: 'supplier_name', label: 'supplier_name' },
    { key: 'total', label: 'total' },
    { key: 'status', label: 'status' },
    { key: 'actions', label: 'Aksi', width: '100px', align: 'center' },
];
