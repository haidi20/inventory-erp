import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { ReceivableTransactionFilters, ReceivableTransactionMeta } from './receivable_transaction.state';
import { RECEIVABLE_TRANSACTION_DEFAULT_FILTERS, RECEIVABLE_TRANSACTION_PER_PAGE_OPTIONS } from './receivable_transaction.state';
import apiService from '@modules/Core/shared/core/api_service';

export const useReceivableTransactionVmVM = defineStore('receivabletransaction-vm', () => {
    const items       = ref<any[]>([]);
    const meta        = ref<ReceivableTransactionMeta>({} as ReceivableTransactionMeta);
    const isLoading   = ref(false);
    const search      = ref(RECEIVABLE_TRANSACTION_DEFAULT_FILTERS.search);
    const perPage     = ref(RECEIVABLE_TRANSACTION_DEFAULT_FILTERS.per_page);
    const currentPage = ref(RECEIVABLE_TRANSACTION_DEFAULT_FILTERS.page);
    const perPageOptions = RECEIVABLE_TRANSACTION_PER_PAGE_OPTIONS;

    async function fetchItems(filters: ReceivableTransactionFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/receivables', {
                params: { search: search.value, per_page: perPage.value, page: currentPage.value, ...filters },
            });
            items.value = res.data.data ?? [];
            meta.value  = res.data.meta ?? {};
        } catch(e) { console.error(e); }
        finally { isLoading.value = false; }
    }

    function initFilters(filters: ReceivableTransactionFilters = {}) {
        search.value      = filters.search   ?? RECEIVABLE_TRANSACTION_DEFAULT_FILTERS.search;
        perPage.value     = filters.per_page ?? RECEIVABLE_TRANSACTION_DEFAULT_FILTERS.per_page;
        currentPage.value = filters.page     ?? RECEIVABLE_TRANSACTION_DEFAULT_FILTERS.page;
        fetchItems();
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p; fetchItems({ page: p });
    }

    return { items, meta, isLoading, search, perPage, perPageOptions, currentPage, fetchItems, initFilters, changePage };
});
