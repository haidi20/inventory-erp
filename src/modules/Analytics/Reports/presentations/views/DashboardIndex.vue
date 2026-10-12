<script setup lang="ts">
import { onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import DataTable from "@modules/Core/shared/Components/common/DataTable.vue";
import { useDashboardStore, type InventoryDashboardProps } from "@modules/Analytics/Reports/presentations/viewmodels/dashboard.vm";

const props = defineProps<InventoryDashboardProps>();

const store = useDashboardStore();

onMounted(() => {
    void store.mountDashboard(props);
});

</script>

<template>
    <Head title="Dashboard Inventory" />

    <div class="dashboard-shell">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <div class="breadcrumb">
                    <i class="fa-solid fa-house" style="font-size:11px;"></i>
                    <span>/</span> Dashboard
                </div>
                <div class="page-title">DASHBOARD INVENTORY</div>
                <div class="page-sub">Ringkasan stok, pembelian, dan penjualan hari ini.</div>
            </div>
            <div class="status-badge"><span class="dot"></span> System Online</div>
        </div>

        <!-- KPI Cards -->
        <div class="stat-grid">
            <div class="stat-card primary">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Stok</div>
                        <div class="stat-value">{{ totalStok.toLocaleString("id-ID") }}</div>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                </div>
                <div class="stat-hint">{{ totalProduk }} produk terdaftar</div>
            </div>

            <div class="stat-card success">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Nilai Stok</div>
                        <div class="stat-value">{{ store.formatRupiah(nilaiStok) }}</div>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-warehouse"></i></div>
                </div>
                <div class="stat-hint">Total nilai persediaan saat ini</div>
            </div>

            <div class="stat-card warning">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Total Pembelian</div>
                        <div class="stat-value">{{ store.formatRupiah(totalBeli) }}</div>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-cart-flatbed"></i></div>
                </div>
                <div class="stat-hint">Akumulasi pembelian bulan ini</div>
            </div>

            <div class="stat-card info">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Omzet Today</div>
                        <div class="stat-value">{{ store.formatRupiah(totalOmzetToday) }}</div>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-shop"></i></div>
                </div>
                <div class="stat-hint">Penjualan hari ini</div>
            </div>

            <div class="stat-card danger">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Hutang</div>
                        <div class="stat-value">{{ store.formatRupiah(totalHutang) }}</div>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                </div>
                <div class="stat-hint">Total hutang ke supplier</div>
            </div>

            <div class="stat-card purple">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Piutang</div>
                        <div class="stat-value">{{ store.formatRupiah(totalPiutang) }}</div>
                    </div>
                    <div class="stat-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                </div>
                <div class="stat-hint">Total piutang dari customer</div>
            </div>
        </div>

        <!-- Chart + Tab Panel -->
        <div style="display:grid; grid-template-columns:1fr 2fr; gap:24px; margin-top:24px;">

            <!-- Grafik Stok -->
            <div class="card">
                <div class="card-title">Mutasi Stok 7 Bulan</div>
                <div class="card-sub">Stok masuk vs keluar.</div>
                <div class="chart-wrap" style="height:260px;">
                    <canvas :ref="store.setChartCanvas"></canvas>
                </div>
            </div>

            <!-- Tab Panel -->
            <div class="card" style="padding:0; overflow:hidden;">

                <!-- Tab Headers -->
                <div style="display:flex; border-bottom:1px solid #e2e8f0; overflow-x:auto;">
                    <button
                        v-for="tab in store.tabs"
                        :key="tab.key"
                        @click="store.onTabChange(tab.key)"
                        :style="{
                            padding: '12px 16px',
                            fontSize: '12px',
                            fontWeight: 700,
                            border: 'none',
                            borderBottom: store.activeTab === tab.key ? '2px solid #6366f1' : '2px solid transparent',
                            background: 'transparent',
                            color: store.activeTab === tab.key ? '#6366f1' : '#64748b',
                            cursor: 'pointer',
                            whiteSpace: 'nowrap',
                            transition: 'all .15s',
                        }"
                    >
                        {{ tab.label }}
                        <span style="margin-left:4px; background:#f1f5f9; border-radius:99px; padding:1px 7px; font-size:11px;">
                            {{ store.tabCount(tab.key) }}
                        </span>
                    </button>
                </div>

                <!-- DataTable (server-side) -->
                <DataTable
                    :columns="store.activeColumns"
                    url="/dashboard/tab-data"
                    :query-params="store.tableQueryParams"
                >
                    <template #cell-nilai="{ value }">{{ store.formatFull(value) }}</template>
                    <template #cell-terbayar="{ value }">{{ store.formatFull(value) }}</template>
                    <template #cell-sisa="{ value }">
                        <span :style="{ color: value > 0 ? '#ef4444' : '#10b981', fontWeight: 600 }">
                            {{ store.formatFull(value) }}
                        </span>
                    </template>
                </DataTable>

            </div>
        </div>
    </div>
</template>

<style scoped>
.stat-card.danger { border-left: 4px solid #ef4444; }
.stat-card.danger .stat-icon { background: #fee2e2; color: #ef4444; }
.stat-card.purple { border-left: 4px solid #8b5cf6; }
.stat-card.purple .stat-icon { background: #ede9fe; color: #8b5cf6; }
.tp { color: #6366f1; font-weight: 600; }
.tm { color: #94a3b8; }
.td { color: #ef4444; font-weight: 600; }
</style>
