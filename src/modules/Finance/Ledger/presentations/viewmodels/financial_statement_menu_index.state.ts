export interface FinancialStatementMenuState {
    label: string;
    url: string;
    icon: string;
}

export const FINANCIAL_STATEMENT_MENU_INDEX_MENUS: FinancialStatementMenuState[] = [
    { label: 'Neraca Saldo', url: '/accounting/trial-balance', icon: 'fa-scale-balanced' },
    { label: 'Neraca', url: '/accounting/balance-sheet', icon: 'fa-building-columns' },
    { label: 'Laba Rugi', url: '/accounting/income-statement', icon: 'fa-chart-line' },
    { label: 'Arus Kas', url: '/accounting/cash-flow', icon: 'fa-money-bill-transfer' },
    { label: 'Penyesuaian Periode', url: '/accounting/period-adjustments', icon: 'fa-sliders' },
    { label: 'Cek Balance', url: '/accounting/balance-check', icon: 'fa-check-double' },
];
