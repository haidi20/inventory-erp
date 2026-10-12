// farmacy_stock_mutation.state.ts — state types untuk FarmacyStockMutationVM
export interface FarmacyStockMutationRow {
    uuid: string;
    referenceNo: string;
    sourceWarehouse: string;
    destinationWarehouse: string;
    date: string;
    description: string;
    status: 'draft' | 'posted';
}

export interface FarmacyStockMutationFormItem {
    uuid: string;
    productId: string;
    qty: number;
    unit_uuid: string;
}

export interface FarmacyStockMutationFormState {
    sourceWarehouseId: string;
    destinationWarehouseId: string;
    transactionDate: string;
    referenceNo: string;
    description: string;
    items: FarmacyStockMutationFormItem[];
}

export const MUTATION_PER_PAGE_OPTIONS = [
    { id: 10, text: '10' },
    { id: 25, text: '25' },
    { id: 50, text: '50' },
] as const;
