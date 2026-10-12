export interface DataTableColumn {
    key: string;
    label: string;
    align?: "left" | "right" | "center";
    width?: string;
    class?: string;
    sortable?: boolean;
}

export interface DataTableSearchColumn {
    id: string;
    text: string;
}

export interface DataTableProps {
    storeId?: string;
    columns: DataTableColumn[];
    url?: string;
    queryParams?: Record<string, any>;
    items?: any[];
    perPageOptions?: number[];
    defaultPerPage?: number;
    searchable?: boolean;
    searchPlaceholder?: string;
    searchTestId?: string;
    searchColumns?: readonly DataTableSearchColumn[];
    searchColumnTestId?: string;
    defaultSortBy?: string;
    defaultSortDirection?: 'asc' | 'desc';
}

export interface DataTableConfig {
    url?: string;
    queryParams?: Record<string, any>;
    items?: any[];
    perPageOptions?: number[];
    searchColumns?: readonly DataTableSearchColumn[];
}

export interface DataTableState {
    search: string;
    searchColumn: string;
    sortBy: string;
    sortDirection: 'asc' | 'desc';
    perPage: number;
    page: number;
    data: any[];
    total: number;
    lastPage: number;
    isLoading: boolean;
}
