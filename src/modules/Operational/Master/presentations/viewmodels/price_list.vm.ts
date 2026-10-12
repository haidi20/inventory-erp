import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { PriceListFilters } from './price_list.state';
import { PRICE_LIST_DEFAULT_FILTERS, PRICE_LIST_PER_PAGE_OPTIONS } from './price_list.state';

export const usePriceListVM = defineStore('price-list-vm', () => {
    const search      = ref(PRICE_LIST_DEFAULT_FILTERS.search);
    const perPage     = ref(PRICE_LIST_DEFAULT_FILTERS.per_page);
    const currentPage = ref(PRICE_LIST_DEFAULT_FILTERS.page);
    const perPageOptions = PRICE_LIST_PER_PAGE_OPTIONS;

    function initFilters(filters: PriceListFilters = {}) {
        search.value      = filters.search   ?? PRICE_LIST_DEFAULT_FILTERS.search;
        perPage.value     = filters.per_page ?? PRICE_LIST_DEFAULT_FILTERS.per_page;
        currentPage.value = filters.page     ?? PRICE_LIST_DEFAULT_FILTERS.page;
    }

    return { search, perPage, perPageOptions, currentPage, initFilters };
});
