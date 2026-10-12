import { defineStore } from 'pinia';
import { ref } from 'vue';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import type { ReportItem } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const useFarmacyReportVM = defineStore('farmacy-report-vm', () => {
    const { getReportsUseCase } = farmacyDependencies;
    const reports = ref<ReportItem[]>([]);
    const isLoading = ref(false);

    const fetch = async () => {
        isLoading.value = true;
        const result = await getReportsUseCase.execute();
        result.fold(() => {}, (data) => { reports.value = data; });
        isLoading.value = false;
    };

    return { reports, isLoading, fetch };
});
