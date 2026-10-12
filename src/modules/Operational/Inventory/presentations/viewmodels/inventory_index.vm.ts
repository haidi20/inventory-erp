import type { SearchResult } from './inventory_index.state';
import { defineStore, acceptHMRUpdate } from 'pinia';
import { ref, computed } from 'vue';
import {
    INVENTORY_TABS,
    INVENTORY_VALID_KEYS,
    INVENTORY_DEFAULT_TAB,
} from '@modules/Operational/Inventory/presentations/viewmodels/inventory_index.state';
import type { InventoryMenuItem } from '@modules/Operational/Inventory/presentations/viewmodels/inventory_index.state';

export type { InventoryMenuItem, InventoryTab } from '@modules/Operational/Inventory/presentations/viewmodels/inventory_index.state';

export const useInventoryIndexVM = defineStore('inventory-index-vm', () => {
    const activeTab = ref<string>(INVENTORY_DEFAULT_TAB);
    const searchQuery = ref<string>('');
    const expandedKeys = ref<Set<string>>(new Set());

    const activeMenuItems = computed<InventoryMenuItem[]>(
        () => INVENTORY_TABS.find((t) => t.key === activeTab.value)?.items ?? [],
    );

    const searchResults = computed<SearchResult[]>(() => {
        const q = searchQuery.value.trim().toLowerCase();
        if (!q) return [];

        const results: SearchResult[] = [];

        for (const tab of INVENTORY_TABS) {
            for (const item of tab.items) {
                if (item.label.toLowerCase().includes(q) || item.caption.toLowerCase().includes(q)) {
                    results.push({ tabKey: tab.key, tabLabel: tab.label, item });
                }
                if (item.items) {
                    for (const sub of item.items) {
                        if (sub.label.toLowerCase().includes(q) || sub.caption.toLowerCase().includes(q)) {
                            results.push({ tabKey: tab.key, tabLabel: tab.label, item: sub });
                        }
                    }
                }
            }
        }

        return results;
    });

    const isSearching = computed(() => searchQuery.value.trim().length > 0);

    const initTab = (queryTab?: string) => {
        if (queryTab && INVENTORY_VALID_KEYS.includes(queryTab)) {
            activeTab.value = queryTab;
        }
    };

    const setActiveTab = (tabKey: string) => {
        activeTab.value = tabKey;
        searchQuery.value = '';
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabKey);
        history.replaceState({}, '', url.toString());
    };

    const expandAll = () => {
        const items = INVENTORY_TABS.find((t) => t.key === activeTab.value)?.items ?? [];
        const keys = items.filter((item) => item.items && item.items.length > 0).map((item) => item.key);
        expandedKeys.value = new Set(keys);
    };

    const setSearch = (query: string) => {
        searchQuery.value = query;
    };

    const initFromQuery = () => {
        const query = new URLSearchParams(window.location.search);
        initTab(query.get('tab') ?? undefined);

        for (const tab of INVENTORY_TABS) {
            for (const item of tab.items) {
                if (item.items && item.items.length) {
                    expandedKeys.value.add(item.key);
                }
            }
        }

        expandedKeys.value = new Set(expandedKeys.value);
    };

    const toggleExpand = (key: string) => {
        if (expandedKeys.value.has(key)) {
            expandedKeys.value.delete(key);
        } else {
            expandedKeys.value.add(key);
        }

        expandedKeys.value = new Set(expandedKeys.value);
    };

    const clearExpanded = () => {
        expandedKeys.value = new Set();
    };

    return {
        tabs: INVENTORY_TABS,
        activeTab,
        expandedKeys,
        activeMenuItems,
        searchQuery,
        searchResults,
        isSearching,
        initTab,
        initFromQuery,
        setActiveTab,
        setSearch,
        toggleExpand,
        expandAll,
    };
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useInventoryIndexVM, import.meta.hot));
}
