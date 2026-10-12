<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useFarmacyReportVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_report.vm';

const vm = useFarmacyReportVM();

onMounted(() => {
    vm.fetch();
});
</script>

<template>
    <Head title="Laporan Farmasi - Inventory" />

    <div class="page-header" style="margin-bottom: 32px;">
        <div>
            <div class="breadcrumb">
                <Link href="/" class="breadcrumb-link">
                    <i class="fa-solid fa-house"></i>
                </Link>
                <span>/</span> Operationals <span>/</span> <Link href="/farmasi/dashboard" class="breadcrumb-link">Farmacy</Link> <span>/</span> Laporan
            </div>
            <div class="page-title" style="margin-bottom: 4px;">Pusat Laporan</div>
            <div class="page-sub">Analisa data penjualan, stok, dan performa keuangan farmasi.</div>
        </div>
        <div style="display:flex; gap:12px; align-items: center;">
            <button type="button" class="btn btn-outline">
                <i class="fa-solid fa-print"></i> Cetak Semua
            </button>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <div v-for="report in vm.reports" :key="report.name" class="report-card">
            <div class="report-icon">
                <i class="fa-solid fa-file-chart-column"></i>
            </div>
            <div class="report-info">
                <div class="report-name">{{ report.name }}</div>
                <div class="report-desc">{{ report.description }}</div>
            </div>
            <button class="btn btn-outline btn-sm">Buka</button>
        </div>
    </div>
</template>

<style scoped>
.report-card {
    background: #fff;
    padding: 20px;
    border-radius: 16px;
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s;
}
.report-card:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: var(--shadow-sm); }
.report-icon {
    width: 40px; height: 40px;
    background: var(--primary-light);
    color: var(--primary);
    border-radius: 10px;
    display: grid; place-items: center;
    font-size: 18px;
}
.report-info { flex: 1; }
.report-name { font-weight: 800; color: var(--text); font-size: 14px; margin-bottom: 2px; }
.report-desc { font-size: 12px; color: var(--muted); line-height: 1.4; }
</style>

