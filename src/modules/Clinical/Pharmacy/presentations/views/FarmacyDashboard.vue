<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useFarmacyDashboardVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_dashboard.vm';

const vm = useFarmacyDashboardVM();

onMounted(() => {
    vm.refresh();
});

</script>

<template>
    <Head title="Farmasi Dashboard - Inventory" />

    <div class="page-header" style="margin-bottom: 32px;">
        <div>
            <div class="breadcrumb">
                <Link href="/" class="breadcrumb-link">
                    <i class="fa-solid fa-house"></i>
                </Link>
                <span>/</span> Operationals <span>/</span> <Link href="/farmasi/dashboard" class="breadcrumb-link">Farmacy</Link> <span>/</span> Dashboard
            </div>
            <div class="page-title" style="margin-bottom: 4px;">Dashboard Farmasi</div>
            <div class="page-sub">Ringkasan operasional, stok, dan performa apotek hari ini.</div>
        </div>
        <div style="display:flex; gap:12px; align-items: center;">
            <button type="button" class="btn btn-primary" @click="vm.refresh" :disabled="vm.isLoading">
                <i class="fa-solid fa-sync" :class="{ 'fa-spin': vm.isLoading }"></i> 
                {{ vm.isLoading ? 'Memuat...' : 'Refresh Data' }}
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pendapatan & Pengeluaran</div>
                <div class="stat-value">{{ vm.formatIDR(vm.summary.income) }}</div>
                <div class="stat-hint" style="color: #ef4444;">Keluar: {{ vm.formatIDR(vm.summary.expense) }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Laba Rugi</div>
                <div class="stat-value">{{ vm.formatIDR(vm.summary.profit) }}</div>
                <div class="stat-hint">Margin: +15% vs bln lalu</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Transaksi Hari Ini</div>
                <div class="stat-value">{{ vm.summary.transactionsToday }}</div>
                <div class="stat-hint">Selesai: 20 | Batal: 4</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Peringatan Stok</div>
                <div class="stat-value">{{ vm.summary.lowStock }} Item</div>
                <div class="stat-hint">Kadaluarsa: {{ vm.summary.expiringSoon }} item</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-top: 32px;">
        <!-- Left Column: Top Products -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Obat Terlaris</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Obat</th>
                            <th>Kategori</th>
                            <th>Stok Tersedia</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in vm.topProducts" :key="item.uuid">
                            <td>{{ item.product_name }}</td>
                            <td>{{ item.category }}</td>
                            <td>{{ item.stock_qty }} {{ item.unit }}</td>
                            <td><span class="badge badge-success">â†‘ {{ Math.floor(Math.random() * 20) + 1 }}%</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column: Payments -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Berdasarkan Jenis Pembayaran</h3>
            </div>
            <div class="payment-stats">
                <div class="payment-item">
                    <span>Umum (Tunai)</span>
                    <span class="font-bold">Rp 25.000.000</span>
                </div>
                <div class="payment-item">
                    <span>BPJS</span>
                    <span class="font-bold">Rp 15.200.000</span>
                </div>
                <div class="payment-item">
                    <span>Asuransi Lain</span>
                    <span class="font-bold">Rp 5.050.000</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
}
.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 16px;
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-sm); }
.stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: grid; place-items: center;
    font-size: 20px;
}
.stat-label { font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; }
.stat-value { font-size: 20px; font-weight: 800; color: var(--text); margin: 2px 0; }
.stat-hint { font-size: 11px; font-weight: 600; color: var(--muted); }

.card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--border);
    overflow: hidden;
}
.card-header { padding: 16px 20px; border-bottom: 1px solid var(--border); }
.card-title { font-size: 15px; font-weight: 800; color: var(--text); }

.payment-stats { padding: 20px; display: flex; flex-direction: column; gap: 12px; }
.payment-item {
    display: flex; justify-content: space-between;
    padding: 12px;
    background: #f8fafc;
    border-radius: 10px;
    font-size: 13px;
}
.font-bold { font-weight: 800; color: var(--primary); }
</style>

