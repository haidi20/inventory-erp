// farmacy_report.state.ts — state types untuk FarmacyReportVM
export interface ReportFilters {
    search?: string;
    date_from?: string;
    date_to?: string;
    report_type?: string;
    per_page?: number;
    page?: number;
}

export interface ReportMeta {
    total: number;
    current_page: number;
    per_page: number;
    last_page?: number;
}

export interface FarmacyReportState {
    isLoading: boolean;
}

export const REPORT_PER_PAGE_OPTIONS = [
    { id: 10,  text: '10' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;
