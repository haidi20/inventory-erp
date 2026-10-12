// farmacy_stock_opname.state.ts — state types untuk FarmacyStockOpnameVM
export interface FarmacyStockOpnameRow {
    uuid: string;
    referenceNo: string;
    businessUnit: string;
    warehouse: string;
    date: string;
    description: string;
    status: 'draft' | 'posted';
}

export interface FarmacyStockOpnameFormItem {
    uuid: string;
    productId: string;
    batchId: string;
    bookStock: number;
    physicalStock: number;
}

export interface FarmacyStockOpnameFormState {
    businessUnit: string;
    warehouseId: string;
    transactionDate: string;
    referenceNo: string;
    description: string;
    items: FarmacyStockOpnameFormItem[];
}

export const OPNAME_PER_PAGE_OPTIONS = [
    { id: 10, text: '10' },
    { id: 25, text: '25' },
    { id: 50, text: '50' },
] as const;
