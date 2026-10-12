<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useFarmacyInventoryVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_inventory.vm';
import '@modules/Clinical/Pharmacy/presentations/styles/farmacy-inventory.css';

const vm = useFarmacyInventoryVM();

onMounted(() => {
    vm.fetch();
});
</script>

<template>
    <Head title="Manajemen Stok & Persediaan Farmasi - Inventory" />

    <section class="stock-hub" aria-labelledby="stock-hub-title">
        <div v-if="!vm.activeMenuComponent" class="stock-hero">
            <div>
                <div class="stock-breadcrumb">
                    <Link href="/" class="breadcrumb-link">
                        <i class="fa-solid fa-house"></i>
                    </Link>
                    <span>/</span>
                    <Link href="/farmasi/dashboard" class="breadcrumb-link">Farmasi</Link>
                    <span>/</span>
                    <Link href="/farmasi/inventory" class="breadcrumb-link stock-breadcrumb-current">
                        Inventori
                    </Link>
                </div>
                <h1 id="stock-hub-title">Inventory</h1>
                <!-- <p>Pusat kendali stok farmasi untuk penyesuaian, opname, mutasi, saldo awal, deposit, dan retur residu.</p> -->
            </div>
            
        </div>

        <section v-if="!vm.activeMenuComponent" class="stock-menu-panel" aria-labelledby="stock-menu-title">
            <div class="stock-section-heading">
                <!-- <div>
                    <h2 id="stock-menu-title">Menu Utama Persediaan</h2>
                    <p>Akses cepat ke seluruh proses stok dan persediaan farmasi.</p>
                </div>
                <span class="stock-section-chip">6 Sub-modul</span> -->
            </div>

            <div class="stock-menu-grid">
                <Link
                    v-for="menu in vm.menus"
                    :key="menu.title"
                    :href="menu.href"
                    class="stock-menu-card"
                    :class="`tone-${menu.tone}`"
                >
                    <div class="stock-menu-icon">
                        <i :class="menu.icon"></i>
                    </div>
                    <div class="stock-menu-content">
                        <div class="stock-menu-title-row">
                            <h3>{{ menu.title }}</h3>
                            <span v-if="menu.badge" class="stock-menu-badge">{{ menu.badge }}</span>
                        </div>
                        <p>{{ menu.description }}</p>
                        <div class="stock-action-list">
                            <span v-for="action in menu.actions" :key="action.label" class="stock-action-badge">
                                <i :class="action.icon"></i>
                                {{ action.label }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>
        </section>

        <section v-else class="stock-vue-route-panel" aria-label="Halaman sub menu inventory">
            <!-- <div class="stock-vue-route-toolbar">
                <button type="button" class="stock-refresh-button" @click="vm.closeMenuRoute">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali ke menu inventory
                </button>
            </div> -->
            
            <component :is="vm.activeMenuComponent" />
        </section>

        <!-- <section class="stock-summary-panel" aria-labelledby="stock-summary-title">
            <div class="stock-section-heading">
                <div>
                    <h2 id="stock-summary-title">Ringkasan Stok</h2>
                    <p>Monitoring cepat untuk kondisi persediaan yang perlu diperhatikan.</p>
                </div>
            </div>

            <div class="stock-summary-grid">
                <article
                    v-for="card in vm.summaryCards"
                    :key="card.title"
                    class="stock-summary-card"
                    :class="`tone-${card.tone}`"
                >
                    <div class="stock-summary-icon">
                        <i :class="card.icon"></i>
                    </div>
                    <div>
                        <h3>{{ card.title }}</h3>
                        <div class="stock-summary-value">{{ card.value }}</div>
                        <p>{{ card.description }}</p>
                    </div>
                </article>
            </div>
        </section> -->

        <!-- <section class="stock-activity-panel" aria-labelledby="stock-activity-title">
            <div class="stock-section-heading">
                <div>
                    <h2 id="stock-activity-title">Aktivitas Terbaru</h2>
                    <p>Perubahan stok terakhir dari proses adjustment, opname, dan retur.</p>
                </div>
                <Link href="/farmasi/inventory/mutations" class="stock-view-all-link">
                    Lihat mutasi
                    <i class="fa-solid fa-arrow-right"></i>
                </Link>
            </div>

            <div class="stock-activity-list">
                <article
                    v-for="activity in vm.activities"
                    :key="activity.uuid"
                    class="stock-activity-row"
                    :class="`tone-${activity.tone}`"
                >
                    <div class="stock-activity-icon"><i :class="activity.icon"></i></div>
                    <div>
                        <div class="stock-activity-type">{{ activity.type }}</div>
                        <div class="stock-activity-item">{{ activity.item }}</div>
                    </div>
                    <div class="stock-activity-change">{{ activity.change }}</div>
                    <div class="stock-activity-time">{{ activity.time }}</div>
                </article>
            </div>
        </section> -->
    </section>
</template>

