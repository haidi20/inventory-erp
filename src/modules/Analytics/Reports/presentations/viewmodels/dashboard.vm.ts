import type { InventoryDashboardProps } from './dashboard.state';
import { createDashboardUIState } from './dashboard.state';
import { defineStore } from "pinia";
import { ref, computed, nextTick } from "vue";
import { Chart, registerables } from "chart.js";
import axios from "axios";
import type { DataTableColumn } from "@modules/Core/shared/Components/common/DataTable-types";

Chart.register(...registerables);

export const useDashboardStore = defineStore("dashboard", () => {
    const { chartCanvasRef } = createDashboardUIState();

    // -- Tab state --
    const activeTab = ref<string>("stok");

    const tabs = [
        { key: "stok",       label: "STOK" },
        { key: "beli",       label: "BELI" },
        { key: "omzet",      label: "OMZET" },
        { key: "omzetToday", label: "OMZET TODAY" },
        { key: "hutang",     label: "HUTANG" },
        { key: "piutang",    label: "PIUTANG" },
    ];

    // -- Server-side table state --
    const tableData     = ref<any[]>([]);
    const tableTotal    = ref(0);
    const tablePage     = ref(1);
    const tablePerPage  = ref(10);
    const tableLastPage = ref(1);
    const tableSearch   = ref("");
    const tableLoading  = ref(false);

    const perPageOptions = [10, 25, 50, 100];

    const columns: Record<string, DataTableColumn[]> = {
        stok: [
            { key: "kode", label: "Kode", class: "tp" },
            { key: "nama", label: "Nama Item" },
            { key: "nilai", label: "Nilai", align: "right" },
            { key: "minimal_stok", label: "Minimal Stok", align: "right" },
            { key: "kurang", label: "Kurang", align: "right", class: "td" },
        ],
        beli: [
            { key: "tanggal", label: "Tanggal", class: "tm" },
            { key: "no_transaksi", label: "No Transaksi", class: "tp" },
            { key: "supplier", label: "Supplier" },
            { key: "kode", label: "Kode", class: "tp" },
            { key: "nama", label: "Nama Item" },
            { key: "qty_order", label: "Order", align: "right" },
            { key: "qty_terima", label: "Terima", align: "right" },
            { key: "qty_sisa", label: "Sisa", align: "right", class: "td" },
        ],
        omzet: [
            { key: "tanggal", label: "Tanggal", class: "tm" },
            { key: "no_transaksi", label: "No Transaksi", class: "tp" },
            { key: "customer", label: "Customer" },
            { key: "alamat", label: "Alamat", class: "tm" },
            { key: "nilai", label: "Nilai", align: "right" },
            { key: "terbayar", label: "Terbayar", align: "right" },
            { key: "sisa", label: "Sisa", align: "right" },
        ],
        hutang: [
            { key: "tanggal", label: "Tanggal", class: "tm" },
            { key: "no_transaksi", label: "No Transaksi", class: "tp" },
            { key: "supplier", label: "Supplier" },
            { key: "alamat", label: "Alamat", class: "tm" },
            { key: "nilai", label: "Nilai", align: "right" },
            { key: "terbayar", label: "Terbayar", align: "right" },
            { key: "sisa", label: "Sisa", align: "right", class: "td" },
        ],
        piutang: [
            { key: "tanggal", label: "Tanggal", class: "tm" },
            { key: "no_transaksi", label: "No Transaksi", class: "tp" },
            { key: "customer", label: "Customer" },
            { key: "alamat", label: "Alamat", class: "tm" },
            { key: "nilai", label: "Nilai", align: "right" },
            { key: "terbayar", label: "Terbayar", align: "right" },
            { key: "sisa", label: "Sisa", align: "right", class: "td" },
        ],
    };

    const activeColumns = computed<DataTableColumn[]>(
        () => columns[activeTab.value] ?? [],
    );
    const tableQueryParams = computed(() => ({ tab: activeTab.value }));

    let searchTimer: ReturnType<typeof setTimeout> | null = null;

    // -- Tab counts (set from props) --
    const tabCounts = ref<Record<string, number>>({
        stok: 0, beli: 0, omzet: 0, omzetToday: 0, hutang: 0, piutang: 0,
    });

    const setTabCounts = (props: InventoryDashboardProps) => {
        tabCounts.value = {
            stok:       props.stokMinimum,
            beli:       props.beliItems?.length ?? 0,
            omzet:      props.omzetItems?.length ?? 0,
            omzetToday: props.omzetTodayItems?.length ?? 0,
            hutang:     props.hutangItems?.length ?? 0,
            piutang:    props.piutangItems?.length ?? 0,
        };
    };

    const tabCount = (key: string): number => tabCounts.value[key] ?? 0;

    // -- Fetch --
    const fetchTabData = async () => {
        tableLoading.value = true;
        try {
            const res = await axios.get("/dashboard/tab-data", {
                params: {
                    tab:      activeTab.value,
                    search:   tableSearch.value,
                    page:     tablePage.value,
                    per_page: tablePerPage.value,
                },
            });
            tableData.value     = res.data.data;
            tableTotal.value    = res.data.total;
            tableLastPage.value = res.data.last_page;
        } catch (e) {
            console.error("Dashboard tab-data error:", e);
        } finally {
            tableLoading.value = false;
        }
    };

    // -- Actions --
    const onTabChange = (key: string) => {
        activeTab.value   = key;
        tablePage.value   = 1;
        tableSearch.value = "";
        fetchTabData();
    };

    const onSearchInput = () => {
        if (searchTimer) clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            tablePage.value = 1;
            fetchTabData();
        }, 350);
    };

    const onPerPageChange = () => {
        tablePage.value = 1;
        fetchTabData();
    };

    const goToPage = (page: number) => {
        if (page < 1 || page > tableLastPage.value) return;
        tablePage.value = page;
        fetchTabData();
    };

    // -- Pagination pages --
    const pageNumbers = computed(() => {
        const pages: number[] = [];
        const start = Math.max(1, tablePage.value - 2);
        const end   = Math.min(tableLastPage.value, tablePage.value + 2);
        for (let i = start; i <= end; i++) pages.push(i);
        return pages;
    });

    // -- Formatters --
    const formatRupiah = (val: number): string => {
        if (val >= 1000000000) return "Rp " + (val / 1000000000).toFixed(1) + "M";
        if (val >= 1000000)    return "Rp " + (val / 1000000).toFixed(1) + "jt";
        if (val >= 1000)       return "Rp " + (val / 1000).toFixed(0) + "rb";
        return "Rp " + val;
    };

    const formatFull = (val: number): string =>
        "Rp " + (val ?? 0).toLocaleString("id-ID");

    const initChart = (props: InventoryDashboardProps, canvas: HTMLCanvasElement) => {
        const ctx = canvas.getContext("2d");
        if (!ctx) return;

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: props.stokChartLabels,
                datasets: [
                    {
                        label: "Stok Masuk",
                        data: props.stokMasukSeries,
                        backgroundColor: "#6366f1",
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 28,
                    },
                    {
                        label: "Stok Keluar",
                        data: props.stokKeluarSeries,
                        backgroundColor: "#10b981",
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 28,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: "top", labels: { font: { size: 12, weight: 600 }, color: "#64748b" } },
                    tooltip: { backgroundColor: "#1e293b", padding: 12, cornerRadius: 8 },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: 600 }, color: "#64748b" } },
                    y: {
                        grid: { color: "rgba(226,232,240,0.4)" },
                        border: { display: false },
                        ticks: { font: { size: 11 }, color: "#64748b" },
                    },
                },
            },
        });
    };

    const setChartCanvas = (instance: unknown) => {
        chartCanvasRef.value = instance instanceof HTMLCanvasElement ? instance : null;
    };

    const mountDashboard = async (props: InventoryDashboardProps) => {
        setTabCounts(props);
        fetchTabData();
        await nextTick();
        const canvas = chartCanvasRef.value;
        if (canvas instanceof HTMLCanvasElement) {
            initChart(props, canvas);
        }
    };

    return {
        activeTab, tabs,
        tableData, tableTotal, tablePage, tablePerPage, tableLastPage,
        tableSearch, tableLoading, perPageOptions, pageNumbers,
        activeColumns, tableQueryParams,
        tabCounts, setTabCounts, tabCount,
        fetchTabData, onTabChange, onSearchInput, onPerPageChange, goToPage,
        formatRupiah, formatFull, initChart, setChartCanvas, mountDashboard,
    };
});
