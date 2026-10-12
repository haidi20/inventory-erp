import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { PayableTransactionFilters, PayableTransactionMeta } from './payable_transaction.state';
import { PAYABLE_TRANSACTION_DEFAULT_FILTERS, PAYABLE_TRANSACTION_PER_PAGE_OPTIONS } from './payable_transaction.state';
import apiService from '@modules/Core/shared/core/api_service';

export const usePayableTransactionVmVM = defineStore('payabletransaction-vm', () => {
    const items       = ref<any[]>([]);
    const meta        = ref<PayableTransactionMeta>({} as PayableTransactionMeta);
    const isLoading   = ref(false);
    const search      = ref(PAYABLE_TRANSACTION_DEFAULT_FILTERS.search);
    const perPage     = ref(PAYABLE_TRANSACTION_DEFAULT_FILTERS.per_page);
    const currentPage = ref(PAYABLE_TRANSACTION_DEFAULT_FILTERS.page);
    const perPageOptions = PAYABLE_TRANSACTION_PER_PAGE_OPTIONS;

    async function fetchItems(filters: PayableTransactionFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/payables', {
                params: { search: search.value, per_page: perPage.value, page: currentPage.value, ...filters },
            });
            items.value = res.data.data ?? [];
            meta.value  = res.data.meta ?? {};
        } catch(e) { console.error(e); }
        finally { isLoading.value = false; }
    }

    function initFilters(filters: PayableTransactionFilters = {}) {
        search.value      = filters.search   ?? PAYABLE_TRANSACTION_DEFAULT_FILTERS.search;
        perPage.value     = filters.per_page ?? PAYABLE_TRANSACTION_DEFAULT_FILTERS.per_page;
        currentPage.value = filters.page     ?? PAYABLE_TRANSACTION_DEFAULT_FILTERS.page;
        fetchItems();
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p; fetchItems({ page: p });
    }

    return { items, meta, isLoading, search, perPage, perPageOptions, currentPage, fetchItems, initFilters, changePage };
});
