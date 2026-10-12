import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import { printDataDocument } from '@modules/Core/shared/core/helpers/print_data_document';
import type {
    FarmacyStockAdjustmentBatchOption,
    FarmacyStockAdjustmentOption,
    FarmacyStockMutationFormItem,
    FarmacyStockMutationFormState,
    FarmacyStockMutationRow,
    FarmacyStockMutationStatus,
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

const createEmptyMutationItem = (index: number): FarmacyStockMutationFormItem => ({
    uuid: `stock-mutation-item-${Date.now()}-${index}`,
    productId: '',
    batchId: '',
    stock: 0,
    quantity: 0,
});

const toTimestamp = (date: string): number => {
    const parsedDate = new Date(date);
    return Number.isNaN(parsedDate.getTime()) ? 0 : parsedDate.getTime();
};

function toSelectOptions(options: FarmacyStockAdjustmentOption[]) {
    return options.map((option) => ({ id: option.value, text: option.label }));
}

export const useFarmacyStockMutationVM = defineStore('farmacy-stock-mutation-vm', () => {
    const { getStockMutationsUseCase, getAdjustmentOptionsUseCase } = farmacyDependencies;

    const rows = ref<FarmacyStockMutationRow[]>([]);
    const isLoading = ref(false);
    const search = ref('');
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

    const form = ref<FarmacyStockMutationFormState>({
        businessUnit: '',
        sourceWarehouseId: '',
        destinationWarehouseId: '',
        transactionDate: '28-04-2026 14:12:40',
        referenceNo: 'SM000000001',
        description: '',
        items: [createEmptyMutationItem(1)],
    });

    const showSaveMenu = ref(false);

    const statusClass = (status: FarmacyStockMutationStatus): string => {
        if (status === 'Diterima') {
            return 'is-posted';
        }

        if (status === 'Dibatalkan') {
            return 'is-cancelled';
        }

        return 'is-draft';
    };

    const filteredRows = computed(() => {
        const keyword = search.value.trim().toLowerCase();

        return rows.value
            .filter((row) => keyword.length === 0
                || row.referenceNo.toLowerCase().includes(keyword)
                || row.receiptNo.toLowerCase().includes(keyword)
                || row.description.toLowerCase().includes(keyword)
                || row.sourceWarehouse.toLowerCase().includes(keyword)
                || row.destinationWarehouse.toLowerCase().includes(keyword)
                || row.status.toLowerCase().includes(keyword)
                || row.receivedBy.toLowerCase().includes(keyword))
            .sort((a, b) => toTimestamp(b.date) - toTimestamp(a.date));
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

    const batchOptionsForProduct = (productId: string) => batchOptions.value.filter((option) => option.productId === productId);
    const batchSelectOptionsForProduct = (productId: string) => toSelectOptions(batchOptionsForProduct(productId));
    const remainingStock = (item: FarmacyStockMutationFormItem): number => Math.max(item.stock - item.quantity, 0);
    const totalQuantity = computed(() => form.value.items.reduce((total, item) => total + item.quantity, 0));

    const fetch = async () => {
        isLoading.value = true;
        const [rowResult, optionResult] = await Promise.all([
            getStockMutationsUseCase.execute(),
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
                    businessUnit: 'Klinik Utama',
                    sourceWarehouseId: 'WH-001',
                    destinationWarehouseId: 'WH-002',
                    transactionDate: row.date,
                    referenceNo: row.referenceNo,
                    description: row.description,
                    items: [
                        {
                            uuid: 'stock-mutation-item-mocked',
                            productId: 'PROD-001',
                            batchId: 'BATCH-001',
                            stock: 20,
                            quantity: 5,
                        }
                    ],
                };
            }
        } else {
            // Reset to default
            form.value = {
                businessUnit: '',
                sourceWarehouseId: '',
                destinationWarehouseId: '',
                transactionDate: new Date().toISOString().slice(0, 19).replace('T', ' '),
                referenceNo: 'SM' + Date.now().toString().slice(-9),
                description: '',
                items: [createEmptyMutationItem(1)],
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
        form.value.items.push(createEmptyMutationItem(form.value.items.length + 1));
    };

    const removeItem = (uuid: string) => {
        if (form.value.items.length === 1) {
            return;
        }

        form.value.items = form.value.items.filter((item) => item.uuid !== uuid);
    };

    const handleProductChange = (item: FarmacyStockMutationFormItem, productId: string | number | null) => {
        item.productId = productId === null ? '' : String(productId);
        item.batchId = '';
        item.stock = 0;
        item.quantity = 0;
    };

    const handleBatchChange = (item: FarmacyStockMutationFormItem, batchId: string | number | null) => {
        item.batchId = batchId === null ? '' : String(batchId);
        const batch = batchOptions.value.find((option) => option.value === item.batchId);
        item.stock = batch?.stock ?? 0;
        item.quantity = 0;
    };

    const toggleSaveMenu = () => {
        showSaveMenu.value = !showSaveMenu.value;
    };

    const closeSaveMenu = () => {
        showSaveMenu.value = false;
    };

    const confirmDelete = async (row: FarmacyStockMutationRow) => {
        const result = await Swal.fire({
            title: 'Hapus stok mutasi?',
            text: `${row.referenceNo} akan dihapus dari daftar dummy.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
        });

        if (result.isConfirmed) {
            rows.value = rows.value.filter((item) => item.uuid !== row.uuid);
            await Swal.fire('Terhapus', 'Data stok mutasi berhasil dihapus.', 'success');
        }
    };

    const printMutation = (row: FarmacyStockMutationRow) => {
        printDataDocument({
            title: 'Data Stok Mutasi',
            subtitle: row.referenceNo,
            fields: [
                { label: 'Tanggal', value: row.date },
                { label: 'No Referensi', value: row.referenceNo },
                { label: 'No Penerimaan', value: row.receiptNo },
                { label: 'Deskripsi', value: row.description },
                { label: 'Gudang Awal', value: row.sourceWarehouse },
                { label: 'Gudang Tujuan', value: row.destinationWarehouse },
                { label: 'Status', value: row.status },
                { label: 'Diterima', value: row.receivedBy },
            ],
        });
    };

    watch([search, perPage], () => {
        currentPage.value = 1;
    });

    const backToList = () => {
        router.visit('/farmasi/inventory/mutations');
    };

    return {
        rows,
        isLoading,
        search,
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
        totalQuantity,
        fetch,
        initForm,
        statusClass,
        batchOptionsForProduct,
        batchSelectOptionsForProduct,
        remainingStock,
        changePage,
        addItem,
        removeItem,
        handleProductChange,
        handleBatchChange,
        toggleSaveMenu,
        closeSaveMenu,
        confirmDelete,
        printMutation,
        backToList,
    };
});
