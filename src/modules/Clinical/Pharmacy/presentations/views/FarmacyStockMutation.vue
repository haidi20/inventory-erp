<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';
import Pagination from '@modules/Core/shared/Components/Pagination.vue';
import { useFarmacyStockMutationVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_stock_mutation.vm';
import { MUTATION_PER_PAGE_OPTIONS } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_stock_mutation.state';
import '@modules/Clinical/Pharmacy/presentations/styles/farmacy-stock-adjustment.css';

const vm = useFarmacyStockMutationVM();
const perPageOptions = MUTATION_PER_PAGE_OPTIONS;

onMounted(() => {
    void vm.fetch();
});
</script>

<template>
    <Head title="Stok Mutasi - Inventory" />

    <section class="adjustment-page" aria-labelledby="stock-mutation-title">
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
                    <button type="button" class="breadcrumb-link is-active">Stok Mutasi</button>
                </div>
                <div id="stock-mutation-title" class="page-title" style="margin-bottom: 4px;">Stok Mutasi</div>
                <div class="page-sub">Kelola perpindahan stok antar gudang dan status penerimaannya.</div>
            </div>
            <div style="display:flex; gap:12px; align-items: center;">
                <div class="header-divider" style="height: 32px; margin: 0 8px;"></div>
                <Link href="/farmasi/inventory/mutations/create" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Buat
                </Link>
            </div>
        </div>

        <section class="adjustment-filter-panel stock-mutation-filter-panel" aria-label="Filter daftar stok mutasi">
            <label class="filter-field search-field">
                <span>Cari</span>
                <div class="filter-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input v-model="vm.search" type="search" placeholder="Referensi, penerimaan, gudang, deskripsi" />
                </div>
            </label>
            <label class="filter-field stock-mutation-per-page-field">
                <span>Per Halaman</span>
                <Select2ClientSide
                    v-model="vm.perPage"
                    :get-data="perPageOptions"
                    :clearable="false"
                    placeholder="5"
                    class="filter-select-vs filter-select-compact"
                />
            </label>
        </section>

        <div style="margin-top: 32px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; padding: 0 4px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--text);">Daftar Stok Mutasi</h3>
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
                <table class="stock-mutation-table">
                    <colgroup>
                        <col class="col-date" />
                        <col class="col-reference" />
                        <col class="col-receipt" />
                        <col class="col-description" />
                        <col class="col-warehouse" />
                        <col class="col-warehouse" />
                        <col class="col-status" />
                        <col class="col-received" />
                        <col class="col-action" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No Referensi</th>
                            <th>No Penerimaan</th>
                            <th>Deskripsi</th>
                            <th>Gudang Awal</th>
                            <th>Gudang Tujuan</th>
                            <th>Status</th>
                            <th>Diterima</th>
                            <th class="stock-mutation-action-head">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in vm.visibleRows" :key="row.uuid">
                            <td class="stock-mutation-date">{{ row.date }}</td>
                            <td><code class="adjustment-reference">{{ row.referenceNo }}</code></td>
                            <td><code class="adjustment-reference">{{ row.receiptNo }}</code></td>
                            <td><div class="adjustment-description stock-mutation-description">{{ row.description }}</div></td>
                            <td class="stock-mutation-warehouse">{{ row.sourceWarehouse }}</td>
                            <td class="stock-mutation-warehouse">{{ row.destinationWarehouse }}</td>
                            <td>
                                <span class="status-badge" :class="vm.statusClass(row.status)">
                                    {{ row.status }}
                                </span>
                            </td>
                            <td class="stock-mutation-received">{{ row.receivedBy }}</td>
                            <td>
                                <div class="table-action-cell" style="justify-content: center;">
                                    <Link :href="`/farmasi/inventory/mutations/${row.uuid}/edit`" class="adjustment-action-button is-edit" title="Sunting Data">
                                        <i class="fa-solid fa-pen"></i>
                                    </Link>
                                    <button type="button" class="adjustment-action-button is-print" title="Cetak Data" @click.stop="vm.printMutation(row)">
                                        <i class="fa-solid fa-print"></i>
                                    </button>
                                    <button type="button" class="adjustment-action-button is-delete" title="Hapus Data" @click.stop="vm.confirmDelete(row)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!vm.isLoading && vm.visibleRows.length === 0">
                            <td colspan="9">
                                <div class="empty-state" style="border:none;background:transparent;padding:60px;">
                                    <i class="fa-solid fa-right-left" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                                    Data stok mutasi tidak ditemukan
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
                            <div style="margin-top: 2px;"><code>{{ row.referenceNo }}</code></div>
                            <div style="margin-top: 2px;"><code>{{ row.receiptNo }}</code></div>
                        </div>
                        <span class="status-badge" :class="vm.statusClass(row.status)">{{ row.status }}</span>
                    </div>
                    <div style="margin: 8px 0; padding: 10px; background: #f8fafc; border-radius: 10px;">
                        <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; margin-bottom: 4px;">Gudang</div>
                        <div style="font-size: 13px;">{{ row.sourceWarehouse }} ke {{ row.destinationWarehouse }}</div>
                        <p style="margin-top: 4px; font-size: 13px;">{{ row.description }}</p>
                        <div style="font-size: 12px; color: var(--muted);">Diterima: {{ row.receivedBy }}</div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-top: 4px;">
                        <Link :href="`/farmasi/inventory/mutations/${row.uuid}/edit`" class="btn btn-outline btn-sm" style="width: 100%; height: 38px;">
                            Sunting
                        </Link>
                        <button type="button" class="btn btn-outline btn-sm" style="width: 100%; height: 38px;" @click="vm.printMutation(row)">
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

