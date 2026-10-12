<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useFarmacyTransactionVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_transaction.vm';
import '@modules/Clinical/Pharmacy/presentations/styles/farmacy-transaction.css';

const vm = useFarmacyTransactionVM();

onMounted(() => {
    vm.fetch();
});
</script>

<template>
    <Head title="Penjualan & Pembelian Farmasi - Inventory" />

    <section class="transaction-hub" aria-labelledby="transaction-hub-title">
        <div class="hero-panel">
            <div>
                <div class="breadcrumb">
                    <Link href="/" class="breadcrumb-link">
                        <i class="fa-solid fa-house"></i>
                    </Link>
                    <span>/</span> <Link href="/farmasi/dashboard" class="breadcrumb-link" style="color:var(--muted);">Farmasi</Link> <span>/</span>
                    <span class="breadcrumb-current">Transaksi</span>
                </div>
                <h1 id="transaction-hub-title">Penjualan & Pembelian Farmasi</h1>
                <p>
                    Pusat akses cepat untuk faktur, pesanan, piutang, hutang, dan alur transaksi farmasi.
                </p>
            </div>
            <div class="hero-actions">
                <!-- <Link href="/farmasi/dashboard" class="hub-button secondary">
                    <i class="fa-solid fa-chart-line"></i>
                    Dashboard
                </Link>
                <Link href="/transaksi/penjualan/create" class="hub-button primary">
                    <i class="fa-solid fa-plus"></i>
                    Transaksi Baru
                </Link> -->
            </div>
        </div>

        <div class="module-grid" aria-label="Menu transaksi farmasi">
            <section class="module-card sales-card" aria-labelledby="sales-menu-title">
                <div class="module-header">
                    <div class="module-title-wrap">
                        <div class="module-icon">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div>
                            <h2 id="sales-menu-title">Penjualan</h2>
                            <p>Transaksi keluar, tagihan customer, dan retur penjualan.</p>
                        </div>
                    </div>
                    <span class="module-chip">7 Menu</span>
                </div>

                <div class="menu-grid">
                    <component
                        :is="item.href ? Link : 'button'"
                        v-for="item in vm.salesMenus"
                        :key="`sales-${item.title}`"
                        :href="item.href"
                        type="button"
                        class="menu-card"
                        :class="`tone-${item.tone}`"
                    >
                        <span class="menu-icon"><i :class="item.icon"></i></span>
                        <span class="menu-copy">
                            <span class="menu-title">{{ item.title }}</span>
                            <span class="menu-desc">{{ item.description }}</span>
                        </span>
                        <!-- <span v-if="item.badge" class="menu-badge">{{ item.badge }}</span> -->
                    </component>
                </div>
            </section>

            <section class="module-card purchase-card" aria-labelledby="purchase-menu-title">
                <div class="module-header">
                    <div class="module-title-wrap">
                        <div class="module-icon">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                        <div>
                            <h2 id="purchase-menu-title">Pembelian</h2>
                            <p>Transaksi masuk, penerimaan supplier, hutang, dan retur pembelian.</p>
                        </div>
                    </div>
                    <span class="module-chip">7 Menu</span>
                </div>

                <div class="menu-grid">
                    <component
                        :is="item.href ? Link : 'button'"
                        v-for="item in vm.purchaseMenus"
                        :key="`purchase-${item.title}`"
                        :href="item.href"
                        type="button"
                        class="menu-card"
                        :class="`tone-${item.tone}`"
                    >
                        <span class="menu-icon"><i :class="item.icon"></i></span>
                        <span class="menu-copy">
                            <span class="menu-title">{{ item.title }}</span>
                            <span class="menu-desc">{{ item.description }}</span>
                        </span>
                        <!-- <span v-if="item.badge" class="menu-badge">{{ item.badge }}</span> -->
                    </component>
                </div>
            </section>
        </div>

        <!-- <section class="status-panel" aria-labelledby="status-title">
            <div class="section-heading">
                <div>
                    <h2 id="status-title">Status Terkini</h2>
                    <p>Ringkasan pekerjaan penting yang perlu dipantau hari ini.</p>
                </div>
                <button type="button" class="refresh-button" @click="vm.fetch" :disabled="vm.isLoading">
                    <i class="fa-solid fa-rotate" :class="{ 'fa-spin': vm.isLoading }"></i>
                    {{ vm.isLoading ? 'Memuat' : 'Refresh' }}
                </button>
            </div>

            <div class="status-grid">
                <article
                    v-for="item in vm.statusCards"
                    :key="item.title"
                    class="status-card"
                    :class="`tone-${item.tone}`"
                >
                    <div class="status-icon"><i :class="item.icon"></i></div>
                    <div>
                        <h3>{{ item.title }}</h3>
                        <p>{{ item.description }}</p>
                        <div class="status-value">{{ item.value }}</div>
                        <div class="status-amount">{{ item.amount }}</div>
                    </div>
                </article>
            </div>
        </section>

        <section class="activity-panel" aria-labelledby="activity-title">
            <div class="section-heading">
                <div>
                    <h2 id="activity-title">Aktivitas Terbaru</h2>
                    <p>Transaksi terakhir dari modul penjualan dan pembelian farmasi.</p>
                </div>
                <Link href="/transaksi/penjualan" class="view-all-link">
                    Lihat semua
                    <i class="fa-solid fa-arrow-right"></i>
                </Link>
            </div>

            <div class="activity-list">
                <article v-for="activity in vm.recentActivities" :key="activity.uuid" class="activity-row">
                    <div class="activity-icon"><i :class="activity.icon"></i></div>
                    <div class="activity-main">
                        <div class="activity-reference">{{ activity.reference }}</div>
                        <div class="activity-type">{{ activity.type }}</div>
                    </div>
                    <div class="activity-amount">{{ activity.amount }}</div>
                    <div class="activity-time">{{ activity.time }}</div>
                    <div class="activity-status">{{ activity.status }}</div>
                </article>
            </div>
        </section> -->
    </section>
</template>

