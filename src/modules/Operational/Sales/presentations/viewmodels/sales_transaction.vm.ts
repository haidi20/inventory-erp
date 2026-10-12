import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { SalesTransactionFilters, SalesTransactionMeta } from './sales_transaction.state';
import { SALES_TRANSACTION_DEFAULT_FILTERS, SALES_TRANSACTION_PER_PAGE_OPTIONS } from './sales_transaction.state';
import apiService from '@modules/Core/shared/core/api_service';

export const useSalesTransactionVmVM = defineStore('salestransaction-vm', () => {
    const items       = ref<any[]>([]);
    const meta        = ref<SalesTransactionMeta>({} as SalesTransactionMeta);
    const isLoading   = ref(false);
    const search      = ref(SALES_TRANSACTION_DEFAULT_FILTERS.search);
    const perPage     = ref(SALES_TRANSACTION_DEFAULT_FILTERS.per_page);
    const currentPage = ref(SALES_TRANSACTION_DEFAULT_FILTERS.page);
    const perPageOptions = SALES_TRANSACTION_PER_PAGE_OPTIONS;

    async function fetchItems(filters: SalesTransactionFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/sales-orders', {
                params: { search: search.value, per_page: perPage.value, page: currentPage.value, ...filters },
            });
            items.value = res.data.data ?? [];
            meta.value  = res.data.meta ?? {};
        } catch(e) { console.error(e); }
        finally { isLoading.value = false; }
    }

    function initFilters(filters: SalesTransactionFilters = {}) {
        search.value      = filters.search   ?? SALES_TRANSACTION_DEFAULT_FILTERS.search;
        perPage.value     = filters.per_page ?? SALES_TRANSACTION_DEFAULT_FILTERS.per_page;
        currentPage.value = filters.page     ?? SALES_TRANSACTION_DEFAULT_FILTERS.page;
        fetchItems();
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p; fetchItems({ page: p });
    }

    return { items, meta, isLoading, search, perPage, perPageOptions, currentPage, fetchItems, initFilters, changePage };
});
