// farmacy_dashboard.state.ts — state types untuk FarmacyDashboardVM
export interface DashboardSummary {
    income: number;
    expense: number;
    profit: number;
    transactionsToday: number;
    expiringSoon: number;
    lowStock: number;
}

export interface FarmacyDashboardState {
    isLoading: boolean;
}
