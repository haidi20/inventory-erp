import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import apiService from '@modules/Core/shared/core/api_service';
import { DATATABLE_INITIAL_STATE } from './DataTable.state';
import type {
    DataTableColumn,
    DataTableConfig,
    DataTableState,
} from './DataTable-types';

interface DataTableStoreDefaults {
    defaultSortBy?: string;
    defaultSortDirection?: 'asc' | 'desc';
    defaultPerPage?: number;
}

export function createDataTableVMStore(
    storeId: string,
    defaults: DataTableStoreDefaults = {},
) {
    return defineStore(storeId, () => {
        const initialState: DataTableState = {
            ...DATATABLE_INITIAL_STATE,
            sortBy: defaults.defaultSortBy ?? DATATABLE_INITIAL_STATE.sortBy,
            sortDirection: defaults.defaultSortDirection ?? DATATABLE_INITIAL_STATE.sortDirection,
            perPage: defaults.defaultPerPage ?? DATATABLE_INITIAL_STATE.perPage,
        };

        const search = ref(initialState.search);
        const searchColumn = ref(initialState.searchColumn);
        const sortBy = ref(initialState.sortBy);
        const sortDirection = ref<'asc' | 'desc'>(initialState.sortDirection);
        const perPage = ref(initialState.perPage);
        const page = ref(initialState.page);
        const data = ref<any[]>(initialState.data);
        const total = ref(initialState.total);
        const lastPage = ref(initialState.lastPage);
        const isLoading = ref(initialState.isLoading);
        const config = ref<DataTableConfig>({});

        let serverSearchTimer: ReturnType<typeof setTimeout> | null = null;
        let clientSearchTimer: ReturnType<typeof setTimeout> | null = null;
        let url = '';
        let extraParams: Record<string, any> = {};

        const isServerSide = computed(() => Boolean(config.value.url));
        const inputItems = computed(() => config.value.items ?? []);
        const inputSearchColumns = computed(() => config.value.searchColumns ?? []);

        const filteredItems = computed(() => {
            if (isServerSide.value) return [];

            const query = search.value.toLowerCase();
            let items = [...inputItems.value];

            if (query) {
                items = items.filter((row) => Object.values(row)
                    .some((value) => String(value).toLowerCase().includes(query)));
            }

            if (sortBy.value) {
                items.sort((a, b) => {
                    const firstValue = String(a[sortBy.value] ?? '').toLowerCase();
                    const secondValue = String(b[sortBy.value] ?? '').toLowerCase();

                    return sortDirection.value === 'asc'
                        ? firstValue.localeCompare(secondValue)
                        : secondValue.localeCompare(firstValue);
                });
            }

            return items;
        });

        const clientTotal = computed(() => filteredItems.value.length);
        const clientLastPage = computed(() => Math.max(1, Math.ceil(clientTotal.value / perPage.value)));
        const clientPagedItems = computed(() => {
            const start = (page.value - 1) * perPage.value;

            return filteredItems.value.slice(start, start + perPage.value);
        });

        const pagedItems = computed(() => isServerSide.value ? data.value : clientPagedItems.value);
        const totalItems = computed(() => isServerSide.value ? total.value : clientTotal.value);
        const currentLastPage = computed(() => isServerSide.value ? lastPage.value : clientLastPage.value);
        const displayStart = computed(() => totalItems.value === 0 ? 0 : (page.value - 1) * perPage.value + 1);
        const displayEnd = computed(() => Math.min(page.value * perPage.value, totalItems.value));
        const pageNumbers = computed(() => {
            const start = Math.max(1, page.value - 2);
            const end = Math.min(currentLastPage.value, page.value + 2);
            const pages: number[] = [];

            for (let pageNumber = start; pageNumber <= end; pageNumber += 1) {
                pages.push(pageNumber);
            }

            return pages;
        });

        const hasSearchColumns = computed(() => inputSearchColumns.value.length > 0);
        const searchColumnOptions = computed(() => [...inputSearchColumns.value]);
        const perPageOptions = computed(() => config.value.perPageOptions ?? []);

        const currentSearch = computed({
            get: () => search.value,
            set: (value: string) => { search.value = value; },
        });

        const currentSearchColumn = computed({
            get: () => isServerSide.value ? searchColumn.value : 'all',
            set: (value: string) => {
                if (!isServerSide.value) return;

                searchColumn.value = value;
                page.value = 1;
                fetchData();
            },
        });

        const currentPerPage = computed({
            get: () => perPage.value,
            set: (value: number) => { perPage.value = value; },
        });

        function syncConfig(nextConfig: DataTableConfig) {
            config.value = { ...nextConfig };
            url = nextConfig.url ?? '';
            extraParams = { ...(nextConfig.queryParams ?? {}) };
            page.value = 1;

            if (isServerSide.value) fetchData();
        }

        async function fetchData() {
            if (!url) return;

            isLoading.value = true;

            try {
                const params: Record<string, any> = {
                    ...extraParams,
                    page: page.value,
                    per_page: perPage.value,
                };

                if (search.value) params.search = search.value;
                if (searchColumn.value) params.search_column = searchColumn.value;
                if (sortBy.value) {
                    params.sort_by = sortBy.value;
                    params.sort_direction = sortDirection.value;
                }

                const response = await apiService.get(url, {
                    params,
                    headers: { 'Cache-Control': 'no-cache' },
                });

                data.value = response.data.data ?? [];
                total.value = response.data.total ?? response.data.meta?.total ?? 0;
                lastPage.value = response.data.last_page ?? response.data.meta?.last_page ?? 1;
            } catch {
                data.value = [];
                total.value = 0;
            } finally {
                isLoading.value = false;
            }
        }

        function toggleSort(column: DataTableColumn) {
            if (!column.sortable) return;

            if (sortBy.value === column.key) {
                sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy.value = column.key;
                sortDirection.value = 'asc';
            }

            page.value = 1;

            if (isServerSide.value) fetchData();
        }

        function onSearchInput() {
            if (serverSearchTimer) clearTimeout(serverSearchTimer);
            if (clientSearchTimer) clearTimeout(clientSearchTimer);

            if (isServerSide.value) {
                serverSearchTimer = setTimeout(() => {
                    page.value = 1;
                    fetchData();
                }, 350);
                return;
            }

            clientSearchTimer = setTimeout(() => { page.value = 1; }, 350);
        }

        function onPerPageChange() {
            page.value = 1;

            if (isServerSide.value) fetchData();
        }

        function goToPage(pageNumber: number) {
            if (pageNumber < 1 || pageNumber > currentLastPage.value) return;

            page.value = pageNumber;

            if (isServerSide.value) fetchData();
        }

        function reload() {
            page.value = 1;

            if (isServerSide.value) fetchData();
        }

        return {
            search,
            searchColumn,
            sortBy,
            sortDirection,
            perPage,
            page,
            data,
            total,
            lastPage,
            isLoading,
            isServerSide,
            filteredItems,
            pagedItems,
            totalItems,
            currentLastPage,
            displayStart,
            displayEnd,
            pageNumbers,
            hasSearchColumns,
            searchColumnOptions,
            perPageOptions,
            currentSearch,
            currentSearchColumn,
            currentPerPage,
            syncConfig,
            fetchData,
            toggleSort,
            onSearchInput,
            onPerPageChange,
            goToPage,
            reload,
        };
    });
}