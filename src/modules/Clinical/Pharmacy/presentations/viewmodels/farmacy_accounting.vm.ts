import { defineStore } from 'pinia';
import { ref } from 'vue';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import type { AccountingAccountModel } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const useFarmacyAccountingVM = defineStore('farmacy-accounting-vm', () => {
    const { getAccountsUseCase } = farmacyDependencies;
    const accounts = ref<AccountingAccountModel[]>([]);
    const isLoading = ref(false);

    const fetch = async () => {
        isLoading.value = true;
        const result = await getAccountsUseCase.execute();
        result.fold(() => {}, (data) => { accounts.value = data; });
        isLoading.value = false;
    };

    const formatIDR = (value: number) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);

    return { accounts, isLoading, fetch, formatIDR };
});
