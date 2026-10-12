import type { DataTableState } from './DataTable-types';

export const DATATABLE_PER_PAGE_OPTIONS = [5, 10, 25, 50, 100] as const;

export const DATATABLE_INITIAL_STATE: DataTableState = {
    search: '',
    searchColumn: 'all',
    sortBy: '',
    sortDirection: 'asc',
    perPage: 10,
    page: 1,
    data: [],
    total: 0,
    lastPage: 1,
    isLoading: false,
};
