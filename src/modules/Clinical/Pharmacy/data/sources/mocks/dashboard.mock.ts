import type {
    FarmacyRecentActivitySource,
    FarmacyTransactionDefaults,
    FarmacyStockActivity
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const MOCK_DASHBOARD_SUMMARY = {
    income: 45250000,
    expense: 12400000,
    profit: 32850000,
    transactionsToday: 24,
    expiringSoon: 5,
    lowStock: 12
};

export const FARMACY_TRANSACTION_DEFAULTS: FarmacyTransactionDefaults = {
    receivableCount: 3,
    receivableAmount: 12500000,
    payableCount: 2,
    payableAmount: 5750000,
    newOrderCount: 5,
    lowStockCount: 8,
};

export const FARMACY_FALLBACK_RECENT_ACTIVITIES: FarmacyRecentActivitySource[] = [
    {
        uuid: 'sample-sale',
        icon: 'fa-solid fa-receipt',
        reference: 'INV-001',
        type: 'Penjualan',
        amount: 500000,
        time: '2 jam lalu',
        status: 'Selesai',
    },
    {
        uuid: 'sample-purchase',
        icon: 'fa-solid fa-clipboard-list',
        reference: 'PO-002',
        type: 'Pembelian',
        amount: 750000,
        time: '5 jam lalu',
        status: 'Pending',
    },
    {
        uuid: 'sample-payment',
        icon: 'fa-solid fa-credit-card',
        reference: 'PMT-003',
        type: 'Piutang',
        amount: 300000,
        time: 'Kemarin',
        status: 'Selesai',
    },
];

export const FARMACY_STOCK_ACTIVITIES: FarmacyStockActivity[] = [
    {
        uuid: 'stock-activity-adjustment',
        icon: 'fa-solid fa-screwdriver-wrench',
        type: 'Penyesuaian',
        item: 'Paracetamol 500mg',
        change: '+50 box',
        time: '2 jam lalu',
        tone: 'blue',
    },
    {
        uuid: 'stock-activity-opname',
        icon: 'fa-solid fa-chart-simple',
        type: 'Stok Opname',
        item: 'Opname Bulan Apr',
        change: 'Selisih -2',
        time: 'Kemarin',
        tone: 'green',
    },
    {
        uuid: 'stock-activity-residue',
        icon: 'fa-solid fa-arrow-rotate-left',
        type: 'Retur Residu',
        item: 'Amoxicillin 500mg',
        change: '10 box',
        time: '5 jam lalu',
        tone: 'yellow',
    },
];
