import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import { printDataDocument } from '@modules/Core/shared/core/helpers/print_data_document';
import type {
    FarmacyStockAdjustmentBatchOption,
    FarmacyStockAdjustmentOption,
    FarmacyStockOpnameFormItem,
    FarmacyStockOpnameFormState,
    FarmacyStockOpnameRow,
    FarmacyStockOpnameStatus,
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

const createEmptyOpnameItem = (index: number): FarmacyStockOpnameFormItem => ({
    uuid: `stock-opname-item-${Date.now()}-${index}`,
    productId: '',
    batchId: '',
    bookStock: 0,
    physicalStock: 0,
});

const toTimestamp = (date: string): number => {
    const parsedDate = new Date(date);
    return Number.isNaN(parsedDate.getTime()) ? 0 : parsedDate.getTime();
};

function toSelectOptions(options: FarmacyStockAdjustmentOption[]) {
    return options.map((option) => ({ id: option.value, text: option.label }));
}

export const useFarmacyStockOpnameVM = defineStore('farmacy-stock-opname-vm', () => {
    const { getStockOpnamesUseCase, getAdjustmentOptionsUseCase } = farmacyDependencies;

    const rows = ref<FarmacyStockOpnameRow[]>([]);
    const isLoading = ref(false);
    const search = ref('');
    const startDate = ref('');
    const endDate = ref('');
    const currentPage = ref(1);
    const perPage = ref(5);
    const perPageOptions = [5, 10, 25, 50];

    const businessUnitOptions = ref<FarmacyStockAdjustmentOption[]>([]);
    const warehouseOptions = ref<FarmacyStockAdjustmentOption[]>([]);
    const productOptions = ref<FarmacyStockAdjustmentOption[]>([]);

    const businessUnitSelectOptions = computed(() => toSelectOptions(businessUnitOptions.value));
    const warehouseSelectOptions = computed(() => toSelectOptions(warehouseOptions.value));
    const productSelectOptions = computed(() => toSelectOptions(productOptions.value));
    const batchOptions = ref<FarmacyStockAdjustmentBatchOption[]>([]);

    const form = ref<FarmacyStockOpnameFormState>({
        businessUnit: '',
        warehouseId: '',
        transactionDate: '28-04-2026 09:27:11',
        referenceNo: 'ST000000001',
        description: '',
        items: [createEmptyOpnameItem(1)],
    });

    const showSaveMenu = ref(false);

    const statusClass = (status: FarmacyStockOpnameStatus): string => {
        if (status === 'Diposting') {
            return 'is-posted';
        }

        if (status === 'Dibatalkan') {
            return 'is-cancelled';
        }

        return 'is-draft';
    };

    const filteredRows = computed(() => {
        const keyword = search.value.trim().toLowerCase();

        return rows.value.filter((row) => {
            const matchesSearch = keyword.length === 0
                || row.referenceNo.toLowerCase().includes(keyword)
                || row.businessUnit.toLowerCase().includes(keyword)
                || row.warehouse.toLowerCase().includes(keyword)
                || row.description.toLowerCase().includes(keyword);

            const rowTimestamp = toTimestamp(row.date);
            const matchesStartDate = !startDate.value || rowTimestamp >= toTimestamp(startDate.value);
            const matchesEndDate = !endDate.value || rowTimestamp <= toTimestamp(endDate.value);

            return matchesSearch && matchesStartDate && matchesEndDate;
        });
    });

    const totalPages = computed(() => Math.max(Math.ceil(filteredRows.value.length / perPage.value), 1));
    const visibleRows = computed(() => {
        const start = (currentPage.value - 1) * perPage.value;
        return filteredRows.value.slice(start, start + perPage.value);
    });
    const from = computed(() => filteredRows.value.length === 0 ? 0 : ((currentPage.value - 1) * perPage.value) + 1);
    const to = computed(() => Math.min(currentPage.value * perPage.value, filteredRows.value.length));
    const paginationLinks = computed(() => {
        const links: { url: string | null; label: string; active: boolean }[] = [
            { url: currentPage.value > 1 ? String(currentPage.value - 1) : null, label: '&laquo;', active: false },
        ];

        for (let page = 1; page <= totalPages.value; page += 1) {
            links.push({ url: String(page), label: String(page), active: page === currentPage.value });
        }

        links.push({ url: currentPage.value < totalPages.value ? String(currentPage.value + 1) : null, label: '&raquo;', active: false });

        return links;
    });

    const batchOptionsForProduct = (productId: string) => {
        return batchOptions.value.filter((option) => option.productId === productId);
    };
    const batchSelectOptionsForProduct = (productId: string) => toSelectOptions(batchOptionsForProduct(productId));

    const stockDifference = (item: FarmacyStockOpnameFormItem): number => item.physicalStock - item.bookStock;
    const totalDifference = computed(() => form.value.items.reduce((total, item) => total + stockDifference(item), 0));

    const fetch = async () => {
        isLoading.value = true;
        const [rowResult, optionResult] = await Promise.all([
            getStockOpnamesUseCase.execute(),
            getAdjustmentOptionsUseCase.execute(),
        ]);

        rowResult.fold(() => { rows.value = []; }, (data) => { rows.value = data; });
        optionResult.fold(
            () => {
                businessUnitOptions.value = [];
                warehouseOptions.value = [];
                productOptions.value = [];
                batchOptions.value = [];
            },
            (options) => {
                businessUnitOptions.value = options.businessUnits;
                warehouseOptions.value = options.warehouses;
                productOptions.value = options.products;
                batchOptions.value = options.batches;
            },
        );
        isLoading.value = false;
    };

    const initForm = async (mode: string, uuid?: string) => {
        if (mode === 'edit' && uuid) {
            const row = rows.value.find(r => r.uuid === uuid);
            if (row) {
                form.value = {
                    businessUnit: row.businessUnit,
                    warehouseId: 'WH-001',
                    transactionDate: row.date,
                    referenceNo: row.referenceNo,
                    description: row.description,
                    items: [
                        {
                            uuid: 'stock-opname-item-mocked',
                            productId: 'PROD-001',
                            batchId: 'BATCH-001',
                            bookStock: 10,
                            physicalStock: 8,
                        }
                    ],
                };
            }
        } else {
            // Reset to default
            form.value = {
                businessUnit: '',
                warehouseId: '',
                transactionDate: new Date().toISOString().slice(0, 19).replace('T', ' '),
                referenceNo: 'ST' + Date.now().toString().slice(-9),
                description: '',
                items: [createEmptyOpnameItem(1)],
            };
        }
    };

    const changePage = (pageUrl: string) => {
        const page = Number(pageUrl);
        if (!Number.isNaN(page)) {
            currentPage.value = Math.min(Math.max(page, 1), totalPages.value);
        }
    };

    const addItem = () => {
        form.value.items.push(createEmptyOpnameItem(form.value.items.length + 1));
    };

    const removeItem = (uuid: string) => {
        if (form.value.items.length === 1) {
            return;
        }

        form.value.items = form.value.items.filter((item) => item.uuid !== uuid);
    };

    const handleProductChange = (item: FarmacyStockOpnameFormItem, productId: string | number | null) => {
        item.productId = productId === null ? '' : String(productId);
        item.batchId = '';
        item.bookStock = 0;
        item.physicalStock = 0;
    };

    const handleBatchChange = (item: FarmacyStockOpnameFormItem, batchId: string | number | null) => {
        item.batchId = batchId === null ? '' : String(batchId);
        const batch = batchOptions.value.find((option) => option.value === item.batchId);
        item.bookStock = batch?.stock ?? 0;
        item.physicalStock = batch?.stock ?? 0;
    };

    const toggleSaveMenu = () => {
        showSaveMenu.value = !showSaveMenu.value;
    };

    const closeSaveMenu = () => {
        showSaveMenu.value = false;
    };

    const confirmDelete = async (row: FarmacyStockOpnameRow) => {
        const result = await Swal.fire({
            title: 'Hapus stok opname?',
            text: `${row.referenceNo} akan dihapus dari daftar dummy.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
        });

        if (result.isConfirmed) {
            rows.value = rows.value.filter((item) => item.uuid !== row.uuid);
            await Swal.fire('Terhapus', 'Data stok opname berhasil dihapus.', 'success');
        }
    };

    const printOpname = (row: FarmacyStockOpnameRow) => {
        printDataDocument({
            title: 'Data Stok Opname',
            subtitle: row.referenceNo,
            fields: [
                { label: 'Tanggal', value: row.date },
                { label: 'Nomor Opname', value: row.referenceNo },
                { label: 'Unit Usaha', value: row.businessUnit },
                { label: 'Gudang', value: row.warehouse },
                { label: 'Deskripsi', value: row.description },
                { label: 'Status', value: row.status },
            ],
        });
    };

    const submitForm = async () => {
        isLoading.value = true;
        setTimeout(async () => {
            isLoading.value = false;
            await Swal.fire({
                title: 'Berhasil',
                text: 'Stok opname berhasil disimpan.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
            });
            router.visit('/farmasi/inventory/opname');
        }, 1000);
    };

    watch([search, startDate, endDate, perPage], () => {
        currentPage.value = 1;
    });

    const backToList = () => {
        router.visit('/farmasi/inventory/opname');
    };

    return {
        rows,
        isLoading,
        search,
        startDate,
        endDate,
        currentPage,
        perPage,
        perPageOptions,
        businessUnitOptions,
        warehouseOptions,
        productOptions,
        businessUnitSelectOptions,
        warehouseSelectOptions,
        productSelectOptions,
        form,
        showSaveMenu,
        filteredRows,
        visibleRows,
        from,
        to,
        paginationLinks,
        totalDifference,
        fetch,
        initForm,
        statusClass,
        batchOptionsForProduct,
        batchSelectOptionsForProduct,
        stockDifference,
        changePage,
        addItem,
        removeItem,
        handleProductChange,
        handleBatchChange,
        toggleSaveMenu,
        closeSaveMenu,
        confirmDelete,
        printOpname,
        submitForm,
        backToList,
    };
});
