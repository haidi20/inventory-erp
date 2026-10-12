// dashboard.state.ts — state types untuk DashboardVM (inventory dashboard)
import { ref } from 'vue';

export interface InventoryDashboardProps {
    totalStok: number;
    totalProduk: number;
    nilaiStok: number;
    totalBeli: number;
    totalJual: number;
    totalOmzetToday: number;
    totalHutang: number;
    totalPiutang: number;
    stokMinimum: number;
    stokItems: any[];
    beliItems: any[];
    omzetItems: any[];
    omzetTodayItems: any[];
    hutangItems: any[];
    piutangItems: any[];
    stokChartLabels: string[];
    stokMasukSeries: number[];
    stokKeluarSeries: number[];
}

export type DashboardTabKey = 'stok' | 'beli' | 'omzet' | 'omzetToday' | 'hutang' | 'piutang';

export interface DashboardTab {
    key: DashboardTabKey;
    label: string;
}

export interface DashboardTableState {
    data: any[];
    total: number;
    page: number;
    perPage: number;
    lastPage: number;
    search: string;
    loading: boolean;
}

export const DASHBOARD_TABS: DashboardTab[] = [
    { key: 'stok',       label: 'STOK' },
    { key: 'beli',       label: 'BELI' },
    { key: 'omzet',      label: 'OMZET' },
    { key: 'omzetToday', label: 'OMZET TODAY' },
    { key: 'hutang',     label: 'HUTANG' },
    { key: 'piutang',    label: 'PIUTANG' },
];

export const DASHBOARD_PER_PAGE_OPTIONS = [10, 25, 50, 100] as const;

export function createDashboardUIState() {
    const chartCanvasRef = ref<HTMLCanvasElement | null>(null);

    return { chartCanvasRef };
}

