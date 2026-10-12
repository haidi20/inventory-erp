<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useFarmacyStockAdjustmentVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_stock_adjustment.vm';
import { ADJUSTMENT_PER_PAGE_OPTIONS, ADJUSTMENT_STATUS_OPTIONS } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_stock_adjustment.state';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';
import Pagination from '@modules/Core/shared/Components/Pagination.vue';
import '@modules/Clinical/Pharmacy/presentations/styles/farmacy-stock-adjustment.css';

const vm = useFarmacyStockAdjustmentVM();

const statusOptions = ADJUSTMENT_STATUS_OPTIONS;
const perPageOptions = ADJUSTMENT_PER_PAGE_OPTIONS;

onMounted(() => {
    void vm.fetch();
});
</script>

<template>
    <Head title="Penyesuaian Persediaan - Inventory" />

    <section class="adjustment-page" aria-labelledby="adjustment-title" @click="vm.closeActionMenu">
        <div class="page-header" style="margin-bottom: 32px;">
            <div>
                <div class="breadcrumb">
                    <Link href="/" class="breadcrumb-link">
                        <i class="fa-solid fa-house"></i>
                    </Link>
                    <span>/</span>
                    <Link href="/farmasi/dashboard" class="breadcrumb-link">Farmasi</Link>
                    <span>/</span>
                    <Link href="/farmasi/inventory" class="breadcrumb-link">Inventori</Link>
                    <span>/</span>
                    <button type="button" class="breadcrumb-link is-active">
                        Penyesuaian Persediaan
                    </button>
                </div>
                <div id="adjustment-title" class="page-title" style="margin-bottom: 4px;">Penyesuaian Persediaan</div>
                <div class="page-sub">Kelola histori koreksi stok, tambah stok, pengurangan stok, dan penyesuaian expired.</div>
            </div>
            <div style="display:flex; gap:12px; align-items: center;">
                <div class="header-divider" style="height: 32px; margin: 0 8px;"></div>
                <button type="button" class="btn btn-outline" title="Filter lengkap">
                    <i class="fa-solid fa-filter"></i>
                    Filter
                </button>
                <button type="button" class="btn btn-outline" title="Download Excel">
                    <i class="fa-solid fa-file-excel" style="color:#16a34a;"></i>
                    Ekspor
                </button>
                <Link href="/farmasi/inventory/adjustments/create" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Buat
                </Link>
            </div>
        </div>

        <section class="adjustment-filter-panel" aria-label="Filter daftar penyesuaian persediaan">
            <label class="filter-field search-field">
                <span>Cari</span>
                <div class="filter-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input v-model="vm.search" type="search" placeholder="Nomor referensi, unit, deskripsi" />
                </div>
            </label>
            <div class="filter-field date-range-field">
                <span>Jarak Tanggal</span>
                <div class="date-range-inputs">
                    <input v-model="vm.startDate" type="date" />
                    <span>sd</span>
                    <input v-model="vm.endDate" type="date" />
                </div>
            </div>
            <label class="filter-field">
                <span>Status</span>
                <Select2ClientSide
                    v-model="vm.statusFilter"
                    :get-data="statusOptions"
                    :clearable="false"
                    placeholder="Pilih Status"
                    class="filter-select-vs"
                />
            </label>
            <label class="filter-field">
                <span>Per Halaman</span>
                <Select2ClientSide
                    v-model="vm.perPage"
                    :get-data="perPageOptions"
                    :clearable="false"
                    placeholder="10"
                    class="filter-select-vs filter-select-compact"
                />
            </label>
        </section>

        <div style="margin-top: 32px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; padding: 0 4px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                    <h3 id="adjustment-list-title" style="font-size: 16px; font-weight: 800; color: var(--text);">Daftar Penyesuaian Persediaan</h3>
                </div>
                <div class="badge badge-secondary">{{ vm.filteredRows.length }} total data</div>
            </div>

            <div class="table-wrap adjustment-table-wrap" style="position: relative;">
                <Transition name="fade">
                    <div v-if="vm.isLoading" class="loading-overlay" data-testid="loading-indicator">
                        <div class="loading-spinner">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </div>
                    </div>
                </Transition>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 18%;">Tanggal</th>
                            <th style="width: 20%;">No. Referensi</th>
                            <th style="width: 14%;">Unit Usaha</th>
                            <th>Deskripsi</th>
                            <th style="width: 130px;">Status</th>
                            <th style="width: 150px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in vm.visibleRows" :key="row.uuid">
                            <td>
                                <div class="adjustment-date-cell">
                                    <div class="adjustment-date-main">{{ row.date }}</div>
                                </div>
                            </td>
                            <td>
                                <code class="adjustment-reference">{{ row.referenceNo }}</code>
                            </td>
                            <td>
                                <span class="badge badge-secondary" style="font-size: 9px;">{{ row.businessUnit }}</span>
                            </td>
                            <td>
                                <div class="adjustment-description">{{ row.description }}</div>
                            </td>
                            <td>
                                <span class="status-badge" :class="vm.statusClass(row.status)">
                                    {{ row.status }}
                                </span>
                            </td>
                            <td>
                                <div class="table-action-cell" style="justify-content: center;">
                                    <Link :href="`/farmasi/inventory/adjustments/${row.uuid}/edit`" class="adjustment-action-button is-edit" title="Sunting Data">
                                        <i class="fa-solid fa-pen"></i>
                                    </Link>
                                    <button type="button" class="adjustment-action-button is-print" title="Cetak Data" @click.stop="vm.printAdjustment(row)">
                                        <i class="fa-solid fa-print"></i>
                                    </button>
                                    <button type="button" class="adjustment-action-button is-delete" title="Hapus Data" @click.stop="vm.confirmDelete(row)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!vm.isLoading && vm.visibleRows.length === 0">
                            <td colspan="6">
                                <div class="empty-state" style="border:none;background:transparent;padding:60px;">
                                    <i class="fa-solid fa-boxes-stacked" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                                    Tidak ada penyesuaian persediaan ditemukan
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="adjustment-mobile-list">
                <article v-for="row in vm.visibleRows" :key="`mobile-${row.uuid}`" class="adjustment-mobile-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
                        <div>
                            <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; margin-bottom: 2px;">Tanggal & Ref</div>
                            <div><i class="fa-solid fa-calendar-day" style="color: var(--primary);"></i> {{ row.date }}</div>
                            <div style="margin-top: 2px;"><i class="fa-regular fa-clock" style="color: var(--muted);"></i> {{ row.time }}</div>
                            <div style="margin-top: 2px;"><code>{{ row.referenceNo }}</code></div>
                        </div>
                        <span class="status-badge" :class="vm.statusClass(row.status)">{{ row.status }}</span>
                    </div>
                    
                    <div style="margin: 8px 0; padding: 10px; background: #f8fafc; border-radius: 10px;">
                        <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; margin-bottom: 4px;">Unit & Deskripsi</div>
                        <div style="font-size: 13px;"><i class="fa-solid fa-building"></i> {{ row.businessUnit }}</div>
                        <p style="margin-top: 4px; font-size: 13px;">{{ row.description }}</p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-top: 4px;">
                        <Link :href="`/farmasi/inventory/adjustments/${row.uuid}/edit`" class="btn btn-outline btn-sm" style="width: 100%; height: 38px;">
                            Sunting
                        </Link>
                        <button type="button" class="btn btn-outline btn-sm" style="width: 100%; height: 38px;" @click="vm.printAdjustment(row)">
                            Cetak
                        </button>
                        <button type="button" class="btn btn-outline btn-sm btn-danger-soft" style="width: 100%; height: 38px;" @click="vm.confirmDelete(row)">
                            Hapus
                        </button>
                    </div>
                </article>
            </div>

            <div v-if="vm.filteredRows.length > 0" class="table-footer">
                <div class="footer-stats">
                    Menampilkan {{ vm.from }} - {{ vm.to }} dari {{ vm.filteredRows.length }} data
                </div>
                <Pagination :links="vm.paginationLinks" manual @change-page="vm.changePage" />
            </div>
        </div>
    </section>
</template>

<style scoped>
.filter-select-vs {
    min-width: 160px;
}

:deep(.vs__dropdown-toggle) {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid var(--stock-border);
    min-height: 38px;
}
</style>

