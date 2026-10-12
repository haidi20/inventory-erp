import { defineStore, acceptHMRUpdate } from 'pinia';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    REPORT_TABS,
    REPORT_VALID_KEYS,
    REPORT_DEFAULT_TAB,
} from '@modules/Analytics/Reports/presentations/viewmodels/report_index.state';
import type { ReportMenuItem } from '@modules/Analytics/Reports/presentations/viewmodels/report_index.state';

export type { ReportMenuItem, ReportTab } from '@modules/Analytics/Reports/presentations/viewmodels/report_index.state';

export const useReportIndexVM = defineStore('report-index-vm', () => {
    const activeTab = ref<string>(REPORT_DEFAULT_TAB);

    const activeMenuItems = computed<ReportMenuItem[]>(
        () => REPORT_TABS.find((t) => t.key === activeTab.value)?.items ?? [],
    );

    const initTab = (queryTab?: string) => {
        if (queryTab && REPORT_VALID_KEYS.includes(queryTab)) {
            activeTab.value = queryTab;
        }
    };

    const setActiveTab = (tabKey: string) => {
        activeTab.value = tabKey;
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabKey);
        router.visit(url.toString(), { replace: true });
    };

    const initFromQuery = () => {
        const query = new URLSearchParams(window.location.search);
        initTab(query.get('tab') ?? undefined);
    };

    return {
        tabs: REPORT_TABS,
        activeTab,
        activeMenuItems,
        initTab,
        initFromQuery,
        setActiveTab,
    };
});

if (import.meta.hot) {
    import.meta.hot.accept(acceptHMRUpdate(useReportIndexVM, import.meta.hot));
}
