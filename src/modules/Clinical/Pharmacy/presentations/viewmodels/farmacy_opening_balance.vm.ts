import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import type {
    FarmacyOpeningBalanceFormState,
    FarmacyOpeningBalanceRow,
    FarmacyStockAdjustmentOption,
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

function toSelectOptions(options: FarmacyStockAdjustmentOption[]) {
    return options.map((option) => ({ id: option.value, text: option.label }));
}

export const useFarmacyOpeningBalanceVM = defineStore('farmacy-opening-balance-vm', () => {
    const { getAdjustmentOptionsUseCase } = farmacyDependencies;
    
    // State untuk Opsi
    const businessUnitOptions = ref<FarmacyStockAdjustmentOption[]>([]);
    const warehouseOptions = ref<FarmacyStockAdjustmentOption[]>([]);
    const productOptions = ref<FarmacyStockAdjustmentOption[]>([]);

    const businessUnitSelectOptions = computed(() => toSelectOptions(businessUnitOptions.value));
    const warehouseSelectOptions = computed(() => toSelectOptions(warehouseOptions.value));
    const productSelectOptions = computed(() => toSelectOptions(productOptions.value));

    const search = ref('');
    const startDate = ref('2025-01-01');
    const endDate = ref('2026-01-31');
    const perPage = ref(10);
    const currentPage = ref(1);
    const isLoading = ref(false);
    const showSaveMenu = ref(false);
    const activeActionUuid = ref<string | null>(null);
    
    const form = ref<FarmacyOpeningBalanceFormState>({
        businessUnit: 'Klinik Utama',
        productId: '',
        warehouseId: '',
        expiryDate: '',
        batchNumber: '',
        purchasePrice: 0,
        totalStock: 0,
    });

    const rows = ref<FarmacyOpeningBalanceRow[]>([
        {
            uuid: 'ob-001',
            productName: 'PAKET INJEKSI RANITIDINE',
            productCode: '5242296',
            businessUnit: 'Klinik Utama',
            warehouse: 'Gudang Utama samarinda Kode : 99',
            totalStock: 0,
            totalAsset: 0,
            date: '2025-01-10',
        },
        {
            uuid: 'ob-002',
            productName: 'SYMBICORT RAPIHALER 160/4.5',
            productCode: '456573',
            businessUnit: 'Klinik Utama',
            warehouse: 'Gudang Utama samarinda Kode : 99',
            totalStock: 1,
            totalAsset: 783000,
            date: '2025-01-15',
        },
        {
            uuid: 'ob-003',
            productName: 'YUSIMOX 125 MG/5ML SYR',
            productCode: '100293',
            businessUnit: 'Klinik Utama',
            warehouse: 'Gudang Utama samarinda Kode : 99',
            totalStock: 2,
            totalAsset: 16206,
            date: '2025-01-20',
        },
        {
            uuid: 'ob-004',
            productName: 'HOTIN CREAM 120 G',
            productCode: '88231',
            businessUnit: 'Klinik Utama',
            warehouse: 'Gudang Utama samarinda Kode : 99',
            totalStock: 2,
            totalAsset: 56610,
            date: '2025-01-25',
        },
    ]);

    const fetchOptions = async () => {
        const result = await getAdjustmentOptionsUseCase.execute();
        result.fold(
            (failure) => console.error(failure.message),
            (data) => {
                businessUnitOptions.value = data.businessUnits;
                warehouseOptions.value = data.warehouses;
                productOptions.value = data.products;
            }
        );
    };

    const fetch = async () => {
        isLoading.value = true;
        if (businessUnitOptions.value.length === 0) {
            await fetchOptions();
        }
        // In real app, fetch rows from API here
        isLoading.value = false;
    };

    const initForm = async (mode: string, uuid?: string) => {
        if (mode === 'edit' && uuid) {
            const row = rows.value.find(r => r.uuid === uuid);
            if (row) {
                form.value = {
                    businessUnit: row.businessUnit,
                    productId: row.uuid, // Mocking: in real case this would be productId
                    warehouseId: 'WH-001', // Mocking
                    expiryDate: '2027-12-31',
                    batchNumber: 'BATCH-MOCK',
                    purchasePrice: row.totalAsset / (row.totalStock || 1),
                    totalStock: row.totalStock,
                };
            }
        } else {
            // Reset to default
            form.value = {
                businessUnit: 'Klinik Utama',
                productId: '',
                warehouseId: '',
                expiryDate: '',
                batchNumber: '',
                purchasePrice: 0,
                totalStock: 0,
            };
        }
    };

    const filteredRows = computed(() => {
        const keyword = search.value.trim().toLowerCase();

        return rows.value.filter((row) => {
            const matchesSearch = !keyword
                || row.productName.toLowerCase().includes(keyword)
                || row.productCode.toLowerCase().includes(keyword)
                || row.businessUnit.toLowerCase().includes(keyword)
                || row.warehouse.toLowerCase().includes(keyword);

            return matchesSearch;
        });
    });

    watch([search, perPage], () => {
        currentPage.value = 1;
    });

    const totalPages = computed(() => Math.max(1, Math.ceil(filteredRows.value.length / perPage.value)));
    const from = computed(() => filteredRows.value.length === 0 ? 0 : ((currentPage.value - 1) * perPage.value) + 1);
    const to = computed(() => Math.min(currentPage.value * perPage.value, filteredRows.value.length));
    
    const paginationLinks = computed(() => {
        const links = [];
        links.push({
            url: currentPage.value > 1 ? String(currentPage.value - 1) : null,
            label: '&laquo; Previous',
            active: false,
        });

        for (let page = 1; page <= totalPages.value; page += 1) {
            links.push({
                url: String(page),
                label: String(page),
                active: page === currentPage.value,
            });
        }

        links.push({
            url: currentPage.value < totalPages.value ? String(currentPage.value + 1) : null,
            label: 'Next &raquo;',
            active: false,
        });

        return links;
    });
    
    const visibleRows = computed(() => {
        const start = (currentPage.value - 1) * perPage.value;
        return filteredRows.value.slice(start, start + perPage.value);
    });
    
    const totalAsset = computed(() => form.value.purchasePrice * form.value.totalStock);

    const formatCurrency = (value: number): string => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(value);
    };

    const changePage = (pageUrl: string) => {
        const nextPage = Number(pageUrl);
        if (Number.isNaN(nextPage)) return;
        currentPage.value = Math.min(Math.max(nextPage, 1), totalPages.value);
    };

    const toggleSaveMenu = () => {
        showSaveMenu.value = !showSaveMenu.value;
    };

    const closeSaveMenu = () => {
        showSaveMenu.value = false;
    };

    const submitForm = async () => {
        isLoading.value = true;
        // Mock save
        setTimeout(async () => {
            isLoading.value = false;
            await Swal.fire({
                title: 'Berhasil',
                text: 'Data saldo awal berhasil disimpan.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
            });
        }, 1000);
    };

    const closeActionMenu = () => {
        activeActionUuid.value = null;
    };

    const toggleActionMenu = (uuid: string) => {
        activeActionUuid.value = activeActionUuid.value === uuid ? null : uuid;
    };

    const confirmDelete = async (row: FarmacyOpeningBalanceRow) => {
        closeActionMenu();

        const confirmResult = await Swal.fire({
            title: 'Hapus data?',
            text: `Data saldo awal untuk ${row.productName} akan dihapus.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
        });

        if (!confirmResult.isConfirmed) {
            return;
        }

        rows.value = rows.value.filter((item) => item.uuid !== row.uuid);

        await Swal.fire({
            title: 'Terhapus',
            text: `Data ${row.productName} berhasil dihapus.`,
            icon: 'success',
            timer: 1600,
            showConfirmButton: false,
        });
    };

    const printOpeningBalance = (row: FarmacyOpeningBalanceRow) => {
        closeActionMenu();
        // Implementasi print dummy
        console.log('Printing opening balance for:', row.uuid);
        void Swal.fire({
            title: 'Mencetak...',
            text: `Mencetak data saldo awal ${row.productName}`,
            icon: 'info',
            timer: 1000,
            showConfirmButton: false,
        });
    };

    const cancelForm = () => {
        router.visit('/farmasi/inventory/opening-balance');
    };

    return {
        businessUnitOptions,
        warehouseOptions,
        productOptions,
        businessUnitSelectOptions,
        warehouseSelectOptions,
        productSelectOptions,
        search,
        startDate,
        endDate,
        perPage,
        currentPage,
        isLoading,
        showSaveMenu,
        activeActionUuid,
        form,
        rows,
        filteredRows,
        totalPages,
        from,
        to,
        paginationLinks,
        visibleRows,
        totalAsset,
        fetch,
        initForm,
        formatCurrency,
        changePage,
        toggleSaveMenu,
        closeSaveMenu,
        submitForm,
        cancelForm,
        closeActionMenu,
        toggleActionMenu,
        confirmDelete,
        printOpeningBalance,
    };
});
