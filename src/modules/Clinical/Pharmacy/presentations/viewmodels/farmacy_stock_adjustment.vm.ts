import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { onClickOutside } from '@vueuse/core';
import Swal from 'sweetalert2';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import { printDataDocument } from '@modules/Core/shared/core/helpers/print_data_document';
import {
    ADJUSTMENT_PER_PAGE_OPTIONS,
    ADJUSTMENT_STATUS_OPTIONS,
    createFarmacyStockAdjustmentState,
} from './farmacy_stock_adjustment.state';
import type {
    FarmacyStockAdjustmentBatchOption,
    FarmacyStockAdjustmentFormItem,
    FarmacyStockAdjustmentOption,
    FarmacyStockAdjustmentRow,
    FarmacyStockAdjustmentStatus,
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

function toSelectOptions(options: FarmacyStockAdjustmentOption[]) {
    return options.map((option) => ({ id: option.value, text: option.label }));
}

export const useFarmacyStockAdjustmentVM = defineStore('farmacy-stock-adjustment-vm', () => {
    const { getStockAdjustmentsUseCase, getAdjustmentOptionsUseCase } = farmacyDependencies;

    const {
        activeRoute,
        formMode,
        businessUnitOptions,
        warehouseOptions,
        productOptions,
        batchOptions,
        search,
        startDate,
        endDate,
        statusFilter,
        perPage,
        currentPage,
        isLoading,
        showSaveMenu,
        activeActionUuid,
        form,
        rows,
        saveDropdownRef,
    } = createFarmacyStockAdjustmentState();

    const statusOptions = ADJUSTMENT_STATUS_OPTIONS;
    const perPageOptions = ADJUSTMENT_PER_PAGE_OPTIONS;

    const businessUnitSelectOptions = computed(() => toSelectOptions(businessUnitOptions.value));
    const warehouseSelectOptions = computed(() => toSelectOptions(warehouseOptions.value));
    const productSelectOptions = computed(() => toSelectOptions(productOptions.value));
    const formTitle = computed(() => `${formMode.value === 'edit' ? 'Ubah' : 'Tambah'} Penyesuaian Persediaan`);
    const formModeLabel = computed(() => (formMode.value === 'edit' ? 'Ubah' : 'Tambah'));

    const fetchOptions = async () => {
        const result = await getAdjustmentOptionsUseCase.execute();
        result.fold(
            (failure) => console.error(failure.message),
            (data) => {
                businessUnitOptions.value = data.businessUnits;
                warehouseOptions.value = data.warehouses;
                productOptions.value = data.products;
                batchOptions.value = data.batches;
            }
        );
    };

    const fetch = async () => {
        isLoading.value = true;
        
        // Fetch options if empty
        if (businessUnitOptions.value.length === 0) {
            await fetchOptions();
        }

        const result = await getStockAdjustmentsUseCase.execute();
        result.fold(
            (failure) => {
                console.error(failure.message);
            },
            (data) => {
                rows.value = data;
            }
        );
        isLoading.value = false;
    };

    const initForm = async (mode: string, uuid?: string) => {
        formMode.value = mode === 'edit' ? 'edit' : 'create';
        if (mode === 'edit' && uuid) {
            const row = rows.value.find(r => r.uuid === uuid);
            if (row) {
                form.value = {
                    businessUnit: row.businessUnit,
                    invoiceNo: row.referenceNo,
                    warehouseId: 'WH-001',
                    transactionDate: row.date + ' ' + row.time,
                    description: row.description,
                    items: [
                        {
                            uuid: 'adjustment-form-item-mocked',
                            productId: 'PROD-001',
                            batchId: 'BATCH-001',
                            unitPrice: 15000,
                            quantity: 10,
                        }
                    ],
                };
            }
        } else {
            // Reset to default
            form.value = {
                businessUnit: 'Klinik Utama',
                invoiceNo: 'IJ-KLINIK-' + Date.now().toString().slice(-6),
                warehouseId: '',
                transactionDate: new Date().toISOString().slice(0, 19).replace('T', ' '),
                description: '',
                items: [
                    {
                        uuid: 'adjustment-form-item-' + Date.now(),
                        productId: '',
                        batchId: '',
                        unitPrice: 0,
                        quantity: 1,
                    },
                ],
            };
        }
    };

    const filteredRows = computed(() => {
        const keyword = search.value.trim().toLowerCase();

        return rows.value.filter((row) => {
            const matchesStatus = statusFilter.value === 'Semua' || row.status === statusFilter.value;
            const matchesSearch = !keyword
                || row.referenceNo.toLowerCase().includes(keyword)
                || row.businessUnit.toLowerCase().includes(keyword)
                || row.description.toLowerCase().includes(keyword);

            return matchesStatus && matchesSearch;
        });
    });

    watch([search, statusFilter, perPage], () => {
        currentPage.value = 1;
    });

    const totalPages = computed(() => Math.max(1, Math.ceil(filteredRows.value.length / perPage.value)));
    const isListRoute = computed(() => activeRoute.value === 'list');
    const isFormRoute = computed(() => activeRoute.value === 'form');
    const from = computed(() => filteredRows.value.length === 0 ? 0 : ((currentPage.value - 1) * perPage.value) + 1);
    const to = computed(() => Math.min(currentPage.value * perPage.value, filteredRows.value.length));
    const paginationLinks = computed(() => {
        const links = [
            {
                url: currentPage.value > 1 ? String(currentPage.value - 1) : null,
                label: '&laquo; Previous',
                active: false,
            },
        ];

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
    
    const totalFilledItems = computed(() => form.value.items.filter((item) => item.productId && item.batchId).length);
    const totalAdjustment = computed(() => form.value.items.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0));

    const statusClass = (status: FarmacyStockAdjustmentStatus): string => {
        const classes: Record<FarmacyStockAdjustmentStatus, string> = {
            Diposting: 'status-posted',
            Draft: 'status-draft',
            Dibatalkan: 'status-cancelled',
        };

        return classes[status];
    };

    const getItemSubtotal = (item: FarmacyStockAdjustmentFormItem): number => item.unitPrice * item.quantity;

    const setSaveDropdown = (instance: unknown) => {
        saveDropdownRef.value = instance instanceof HTMLElement ? instance : null;
    };

    let unbindSaveMenuDismiss: (() => void) | null = null;
    const initSaveMenuDismiss = () => {
        unbindSaveMenuDismiss = onClickOutside(saveDropdownRef, () => {
            closeSaveMenu();
        });
    };
    const disposeSaveMenuDismiss = () => {
        unbindSaveMenuDismiss?.();
        unbindSaveMenuDismiss = null;
    };

    const closeActionMenu = () => {
        activeActionUuid.value = null;
    };

    const toggleActionMenu = (uuid: string) => {
        activeActionUuid.value = activeActionUuid.value === uuid ? null : uuid;
    };

    const printAdjustment = (row: FarmacyStockAdjustmentRow) => {
        closeActionMenu();
        printDataDocument({
            title: 'Data Penyesuaian Persediaan',
            subtitle: row.referenceNo,
            fields: [
                { label: 'Tanggal', value: row.date },
                { label: 'Nomor Referensi', value: row.referenceNo },
                { label: 'Unit Usaha', value: row.businessUnit },
                { label: 'Deskripsi', value: row.description },
                { label: 'Status', value: row.status },
            ],
        });
    };

    const confirmDelete = async (row: FarmacyStockAdjustmentRow) => {
        closeActionMenu();

        const confirmResult = await Swal.fire({
            title: 'Hapus data?',
            text: `Data penyesuaian ${row.referenceNo} akan dihapus dari daftar dummy.`,
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
            text: `Data ${row.referenceNo} berhasil dihapus.`,
            icon: 'success',
            timer: 1600,
            showConfirmButton: false,
        });
    };

    const goPrevious = () => {
        currentPage.value = Math.max(1, currentPage.value - 1);
    };

    const goNext = () => {
        currentPage.value = Math.min(totalPages.value, currentPage.value + 1);
    };

    const changePage = (pageUrl: string) => {
        const nextPage = Number(pageUrl);
        if (Number.isNaN(nextPage)) {
            return;
        }

        currentPage.value = Math.min(Math.max(nextPage, 1), totalPages.value);
    };

    const openCreateForm = () => {
        activeRoute.value = 'form';
    };

    const backToList = () => {
        showSaveMenu.value = false;
        activeRoute.value = 'list';
    };

    const toggleSaveMenu = () => {
        showSaveMenu.value = !showSaveMenu.value;
    };

    const closeSaveMenu = () => {
        showSaveMenu.value = false;
    };

    const createEmptyItem = (): FarmacyStockAdjustmentFormItem => ({
        uuid: `adjustment-form-item-${Date.now()}-${form.value.items.length + 1}`,
        productId: '',
        batchId: '',
        unitPrice: 0,
        quantity: 1,
    });

    const addItem = () => {
        form.value.items.push(createEmptyItem());
    };

    const removeItem = (uuid: string) => {
        form.value.items = form.value.items.length > 1
            ? form.value.items.filter((item) => item.uuid !== uuid)
            : [createEmptyItem()];
    };

    const batchOptionsForProduct = (productId: string): FarmacyStockAdjustmentBatchOption[] => {
        return batchOptions.value.filter((batch) => batch.productId === productId);
    };
    const batchSelectOptionsForProduct = (productId: string) => toSelectOptions(batchOptionsForProduct(productId));

    const handleProductChange = (item: FarmacyStockAdjustmentFormItem) => {
        item.batchId = '';
        item.unitPrice = item.productId ? 15000 : 0;
    };

    const incrementQuantity = (item: FarmacyStockAdjustmentFormItem) => {
        item.quantity += 1;
    };

    const decrementQuantity = (item: FarmacyStockAdjustmentFormItem) => {
        item.quantity -= 1;
    };

    const formatCurrency = (value: number): string => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(value);
    };

    const submitForm = async () => {
        isLoading.value = true;
        setTimeout(async () => {
            isLoading.value = false;
            await Swal.fire({
                title: 'Berhasil',
                text: 'Penyesuaian persediaan berhasil disimpan.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
            });
            backToList();
        }, 1000);
    };

    return {
        activeRoute,
        formMode,
        formModeLabel,
        formTitle,
        isListRoute,
        isFormRoute,
        statusOptions,
        perPageOptions,
        businessUnitOptions,
        warehouseOptions,
        productOptions,
        businessUnitSelectOptions,
        warehouseSelectOptions,
        productSelectOptions,
        batchOptions,
        search,
        startDate,
        endDate,
        statusFilter,
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
        totalFilledItems,
        totalAdjustment,
        fetch,
        initForm,
        statusClass,
        closeActionMenu,
        toggleActionMenu,
        printAdjustment,
        confirmDelete,
        goPrevious,
        goNext,
        changePage,
        openCreateForm,
        backToList,
        toggleSaveMenu,
        closeSaveMenu,
        addItem,
        removeItem,
        batchOptionsForProduct,
        batchSelectOptionsForProduct,
        handleProductChange,
        incrementQuantity,
        decrementQuantity,
        formatCurrency,
        getItemSubtotal,
        setSaveDropdown,
        initSaveMenuDismiss,
        disposeSaveMenuDismiss,
        submitForm,
    };
});
