import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { ExaminationTransactionFilters, ExaminationTransactionMeta } from './examination_transaction.state';
import { EXAMINATION_TRANSACTION_DEFAULT_FILTERS, EXAMINATION_TRANSACTION_PER_PAGE_OPTIONS } from './examination_transaction.state';
import apiService from '@modules/Core/shared/core/api_service';

export const useExaminationTransactionVmVM = defineStore('examinationtransaction-vm', () => {
    const items       = ref<any[]>([]);
    const meta        = ref<ExaminationTransactionMeta>({} as ExaminationTransactionMeta);
    const isLoading   = ref(false);
    const search      = ref(EXAMINATION_TRANSACTION_DEFAULT_FILTERS.search);
    const perPage     = ref(EXAMINATION_TRANSACTION_DEFAULT_FILTERS.per_page);
    const currentPage = ref(EXAMINATION_TRANSACTION_DEFAULT_FILTERS.page);
    const perPageOptions = EXAMINATION_TRANSACTION_PER_PAGE_OPTIONS;

    async function fetchItems(filters: ExaminationTransactionFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/examinations', {
                params: { search: search.value, per_page: perPage.value, page: currentPage.value, ...filters },
            });
            items.value = res.data.data ?? [];
            meta.value  = res.data.meta ?? {};
        } catch(e) { console.error(e); }
        finally { isLoading.value = false; }
    }

    function initFilters(filters: ExaminationTransactionFilters = {}) {
        search.value      = filters.search   ?? EXAMINATION_TRANSACTION_DEFAULT_FILTERS.search;
        perPage.value     = filters.per_page ?? EXAMINATION_TRANSACTION_DEFAULT_FILTERS.per_page;
        currentPage.value = filters.page     ?? EXAMINATION_TRANSACTION_DEFAULT_FILTERS.page;
        fetchItems();
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p; fetchItems({ page: p });
    }

    return { items, meta, isLoading, search, perPage, perPageOptions, currentPage, fetchItems, initFilters, changePage };
});
