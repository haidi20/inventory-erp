import { defineStore } from 'pinia';
import { ref } from 'vue';
import { ITEM_CONVERSION_FILTERS_INITIAL_STATE } from './item_conversion_filters.state';
import type { ItemConversionFiltersState } from './item_conversion_filters.state';

export const useItemConversionFiltersVM = defineStore('item-conversion-filters-vm', () => {
    const initialState: ItemConversionFiltersState = { ...ITEM_CONVERSION_FILTERS_INITIAL_STATE };
    const query = ref(initialState.query);

    function search(): string {
        return query.value;
    }

    return { query, search };
});