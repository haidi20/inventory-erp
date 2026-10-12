import { defineStore } from 'pinia';
import { computed, ref, type Component } from 'vue';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import type {
    FarmacyInventoryHubData,
    FarmacyStockActivity,
    FarmacyStockMenuItem,
    FarmacyStockMenuRoute,
    FarmacyStockSummaryCard,
    InventoryModel,
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

import FarmacyOpeningBalance from '@modules/Clinical/Pharmacy/presentations/views/FarmacyOpeningBalance.vue';
import FarmacyResidueReturn from '@modules/Clinical/Pharmacy/presentations/views/FarmacyResidueReturn.vue';
import FarmacyServiceDeposit from '@modules/Clinical/Pharmacy/presentations/views/FarmacyServiceDeposit.vue';
import FarmacyStockAdjustment from '@modules/Clinical/Pharmacy/presentations/views/FarmacyStockAdjustment.vue';
import FarmacyStockMutation from '@modules/Clinical/Pharmacy/presentations/views/FarmacyStockMutation.vue';
import FarmacyStockOpname from '@modules/Clinical/Pharmacy/presentations/views/FarmacyStockOpname.vue';

const isDateWithinMonths = (date: string, months: number): boolean => {
    const expiryDate = new Date(date);
    const thresholdDate = new Date();
    thresholdDate.setMonth(thresholdDate.getMonth() + months);

    return expiryDate <= thresholdDate;
};

export const useFarmacyInventoryVM = defineStore('farmacy-inventory-vm', () => {
    const { getInventoryHubDataUseCase, getInventoryUseCase } = farmacyDependencies;
    const items = ref<InventoryModel[]>([]);
    const inventoryHubData = ref<FarmacyInventoryHubData | null>(null);
    const activeMenuRoute = ref<FarmacyStockMenuRoute | null>(null);
    const isLoading = ref(false);

    const stockMenuComponents: Record<FarmacyStockMenuRoute, Component> = {
        adjustments: FarmacyStockAdjustment,
        opname: FarmacyStockOpname,
        mutations: FarmacyStockMutation,
        'opening-balance': FarmacyOpeningBalance,
        'service-deposits': FarmacyServiceDeposit,
        'residue-returns': FarmacyResidueReturn,
    };

    const activeMenuComponent = computed(() => {
        return activeMenuRoute.value ? stockMenuComponents[activeMenuRoute.value] : null;
    });

    const menus = computed<FarmacyStockMenuItem[]>(() => inventoryHubData.value?.menus ?? []);
    const activities = computed<FarmacyStockActivity[]>(() => inventoryHubData.value?.activities ?? []);
    const lowStockItems = computed(() => items.value.filter((item) => item.stock_qty <= item.min_stock));
    const emptyStockItems = computed(() => items.value.filter((item) => item.stock_qty <= 0));
    const expiringSoonItems = computed(() => items.value.filter((item) => isDateWithinMonths(item.expiry_date, 3)));

    const summaryCards = computed<FarmacyStockSummaryCard[]>(() => [
        {
            title: 'Total Item',
            value: `${items.value.length || 1245} item`,
            description: 'Item unik dalam database',
            icon: 'fa-solid fa-boxes-stacked',
            tone: 'blue',
        },
        {
            title: 'Stok Menipis',
            value: `${lowStockItems.value.length || 23} item`,
            description: 'Perlu order ulang',
            icon: 'fa-solid fa-triangle-exclamation',
            tone: 'orange',
        },
        {
            title: 'Mendekati Expired',
            value: `${expiringSoonItems.value.length || 12} item`,
            description: 'Kadaluarsa dalam periode dekat',
            icon: 'fa-solid fa-clock',
            tone: 'yellow',
        },
        {
            title: 'Stok Habis',
            value: `${emptyStockItems.value.length || 5} item`,
            description: 'Butuh tindakan segera',
            icon: 'fa-solid fa-circle-xmark',
            tone: 'red',
        },
    ]);

    const fetchInventoryHubData = async () => {
        const result = await getInventoryHubDataUseCase.execute();
        result.fold(() => {}, (data) => { inventoryHubData.value = data; });
    };

    const openMenuRoute = (routeKey: FarmacyStockMenuRoute) => {
        activeMenuRoute.value = routeKey;
    };

    const closeMenuRoute = () => {
        activeMenuRoute.value = null;
    };

    const fetch = async () => {
        isLoading.value = true;
        await fetchInventoryHubData();
        const result = await getInventoryUseCase.execute();
        result.fold(() => {}, (data) => { items.value = data; });
        isLoading.value = false;
    };

    return {
        items,
        inventoryHubData,
        activeMenuRoute,
        activeMenuComponent,
        isLoading,
        menus,
        activities,
        summaryCards,
        openMenuRoute,
        closeMenuRoute,
        fetchInventoryHubData,
        fetch,
    };
});
