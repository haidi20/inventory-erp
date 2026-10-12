// farmacy_opening_balance.state.ts — state types untuk FarmacyOpeningBalanceVM
export interface FarmacyOpeningBalanceRow {
    uuid: string;
    productName: string;
    productCode: string;
    businessUnit: string;
    warehouse: string;
    totalStock: number;
    totalAsset: number;
    date: string;
}

export interface FarmacyOpeningBalanceFormState {
    businessUnit: string;
    productId: string;
    warehouseId: string;
    expiryDate: string;
    batchNumber: string;
    purchasePrice: number;
    totalStock: number;
}

export interface FarmacyOpeningBalanceFilters {
    search?: string;
    startDate?: string;
    endDate?: string;
    per_page?: number;
    page?: number;
}

export const OPENING_BALANCE_PER_PAGE_OPTIONS = [
    { id: 10, text: '10' },
    { id: 25, text: '25' },
    { id: 50, text: '50' },
] as const;
