import { defineStore } from 'pinia';
import { ref } from 'vue';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import type { DashboardSummary, InventoryModel } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const useFarmacyDashboardVM = defineStore('farmacy-dashboard-vm', () => {
    const { getDashboardSummaryUseCase, getInventoryUseCase } = farmacyDependencies;
    
    const summary = ref<DashboardSummary>({ income: 0, expense: 0, profit: 0, transactionsToday: 0, expiringSoon: 0, lowStock: 0 });
    const topProducts = ref<InventoryModel[]>([]);
    const isLoading = ref(false);

    const refresh = async () => {
        isLoading.value = true;
        const [sumResult, inventoryResult] = await Promise.all([
            getDashboardSummaryUseCase.execute(),
            getInventoryUseCase.execute()
        ]);
        
        sumResult.fold(() => {}, (data) => { summary.value = data; });
        inventoryResult.fold(() => {}, (data) => { topProducts.value = data.slice(0, 3); });
        
        isLoading.value = false;
    };

    const formatIDR = (value: number) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);

    return { summary, topProducts, isLoading, refresh, formatIDR };
});
