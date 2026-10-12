import { defineStore } from 'pinia';
import { ref } from 'vue';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import type { ManagementContactModel } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const useFarmacyManagementVM = defineStore('farmacy-management-vm', () => {
    const { getContactsUseCase } = farmacyDependencies;
    const contacts = ref<ManagementContactModel[]>([]);
    const isLoading = ref(false);

    const fetch = async () => {
        isLoading.value = true;
        const result = await getContactsUseCase.execute();
        result.fold(() => {}, (data) => { contacts.value = data; });
        isLoading.value = false;
    };

    return { contacts, isLoading, fetch };
});
