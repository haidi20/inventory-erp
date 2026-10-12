import { ref, shallowRef } from 'vue';
import type {
    FarmacyStockAdjustmentBatchOption,
    FarmacyStockAdjustmentFormState,
    FarmacyStockAdjustmentOption,
    FarmacyStockAdjustmentPageRoute,
    FarmacyStockAdjustmentRow,
    FarmacyStockAdjustmentStatus,
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export type StockAdjustmentStatusFilter = 'Semua' | FarmacyStockAdjustmentStatus;
export type FarmacyStockAdjustmentFormMode = 'create' | 'edit';

export const ADJUSTMENT_STATUS_OPTIONS: ReadonlyArray<{
    id: StockAdjustmentStatusFilter;
    text: string;
}> = [
    { id: 'Semua', text: 'Semua' },
    { id: 'Diposting', text: 'Diposting' },
    { id: 'Draft', text: 'Draft' },
    { id: 'Dibatalkan', text: 'Dibatalkan' },
] as const;

export const ADJUSTMENT_PER_PAGE_OPTIONS: readonly number[] = [5, 10, 25, 50] as const;

export const FARMACY_STOCK_ADJUSTMENT_DEFAULT_FORM: FarmacyStockAdjustmentFormState = {
    businessUnit: 'Klinik Utama',
    invoiceNo: 'IJ-KLINIK-00000111',
    warehouseId: '',
    transactionDate: '2026-04-28 03:55:57',
    description: '',
    items: [
        {
            uuid: 'adjustment-form-item-001',
            productId: '',
            batchId: '',
            unitPrice: 0,
            quantity: 1,
        },
    ],
};

export function createFarmacyStockAdjustmentState() {
    const activeRoute = ref<FarmacyStockAdjustmentPageRoute>('list');
    const formMode = ref<FarmacyStockAdjustmentFormMode>('create');
    const businessUnitOptions = ref<FarmacyStockAdjustmentOption[]>([]);
    const warehouseOptions = ref<FarmacyStockAdjustmentOption[]>([]);
    const productOptions = ref<FarmacyStockAdjustmentOption[]>([]);
    const batchOptions = ref<FarmacyStockAdjustmentBatchOption[]>([]);
    const search = ref('');
    const startDate = ref('2026-04-01');
    const endDate = ref('2026-04-30');
    const statusFilter = ref<StockAdjustmentStatusFilter>('Semua');
    const perPage = ref(5);
    const currentPage = ref(1);
    const isLoading = ref(false);
    const showSaveMenu = ref(false);
    const activeActionUuid = ref<string | null>(null);
    const form = ref<FarmacyStockAdjustmentFormState>({
        ...FARMACY_STOCK_ADJUSTMENT_DEFAULT_FORM,
        items: FARMACY_STOCK_ADJUSTMENT_DEFAULT_FORM.items.map((item) => ({ ...item })),
    });
    const rows = ref<FarmacyStockAdjustmentRow[]>([]);
    const saveDropdownRef = shallowRef<HTMLElement | null>(null);

    return {
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
    };
}
