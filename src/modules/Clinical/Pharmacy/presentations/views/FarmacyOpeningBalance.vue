<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useFarmacyOpeningBalanceVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_opening_balance.vm';
import { OPENING_BALANCE_PER_PAGE_OPTIONS } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_opening_balance.state';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';
import Pagination from '@modules/Core/shared/Components/Pagination.vue';
import '@modules/Clinical/Pharmacy/presentations/styles/farmacy-stock-adjustment.css';

const vm = useFarmacyOpeningBalanceVM();

const perPageOptions = OPENING_BALANCE_PER_PAGE_OPTIONS;

onMounted(() => {
    void vm.fetch();
});
</script>

<template>
    <Head title="Saldo Awal Persediaan - Inventory" />

    <section class="adjustment-page" aria-labelledby="opening-balance-title" @click="vm.closeActionMenu">
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
                        Saldo Awal Persediaan
                    </button>
                </div>
                <div id="opening-balance-title" class="page-title" style="margin-bottom: 4px;">Saldo Awal Persediaan</div>
                <div class="page-sub">Menampilkan daftar saldo awal persediaan (stok & nilai aset).</div>
            </div>
            <div style="display:flex; gap:12px; align-items: center;">
                <div class="header-divider" style="height: 32px; margin: 0 8px;"></div>
                <Link href="/farmasi/inventory/opening-balance/create" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Buat
                </Link>
            </div>
        </div>

        <section class="adjustment-filter-panel opening-balance-filter" aria-label="Filter daftar saldo awal">
            <div class="filter-field date-range-field">
                <span>Pilih Jarak Tanggal</span>
                <div class="date-range-inputs">
                    <input v-model="vm.startDate" type="date" />
                    <span>sd</span>
                    <input v-model="vm.endDate" type="date" />
                </div>
            </div>
            <label class="filter-field search-field">
                <span>Pencarian</span>
                <div class="filter-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input v-model="vm.search" type="search" placeholder="Cari produk, unit usaha, warehouse" />
                </div>
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
                    <h3 id="opening-balance-list-title" style="font-size: 16px; font-weight: 800; color: var(--text);">Daftar Saldo Awal</h3>
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
                            <th style="width: 30%;">Produk</th>
                            <th style="width: 12%;">Unit Usaha</th>
                            <th style="width: 20%;">Warehouse</th>
                            <th style="width: 10%;">Total Stok</th>
                            <th style="width: 13%;">Total Asset</th>
                            <th style="width: 150px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in vm.visibleRows" :key="row.uuid">
                            <td>
                                <div style="font-weight: 700; color: var(--text);">{{ row.productName }}</div>
                                <div style="font-size: 11px; color: var(--muted);">Kode: {{ row.productCode }}</div>
                            </td>
                            <td>
                                <span class="badge badge-secondary" style="font-size: 9px;">{{ row.businessUnit }}</span>
                            </td>
                            <td>{{ row.warehouse }}</td>
                            <td>{{ row.totalStock }}</td>
                            <td>{{ vm.formatCurrency(row.totalAsset) }}</td>
                            <td>
                                <div class="table-action-cell" style="justify-content: center;">
                                    <Link :href="`/farmasi/inventory/opening-balance/${row.uuid}/edit`" class="adjustment-action-button is-edit" title="Sunting Data">
                                        <i class="fa-solid fa-pen"></i>
                                    </Link>
                                    <button type="button" class="adjustment-action-button is-print" title="Cetak Data" @click.stop="vm.printOpeningBalance(row)">
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
                                    Tidak ada data saldo awal ditemukan
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
                            <div style="font-size: 11px; color: var(--muted); text-transform: uppercase; margin-bottom: 2px;">Produk</div>
                            <div style="font-weight: 700; color: var(--text);">{{ row.productName }}</div>
                            <div style="font-size: 11px; color: var(--muted);">Kode: {{ row.productCode }}</div>
                        </div>
                        <span class="badge badge-secondary" style="font-size: 9px;">{{ row.businessUnit }}</span>
                    </div>
                    
                    <div style="margin: 8px 0; padding: 10px; background: #f8fafc; border-radius: 10px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                            <div>
                                <div style="font-size: 10px; color: var(--muted); text-transform: uppercase;">Stok</div>
                                <div style="font-weight: 700;">{{ row.totalStock }}</div>
                            </div>
                            <div>
                                <div style="font-size: 10px; color: var(--muted); text-transform: uppercase;">Asset</div>
                                <div style="font-weight: 700;">{{ vm.formatCurrency(row.totalAsset) }}</div>
                            </div>
                        </div>
                        <div style="margin-top: 8px;">
                            <div style="font-size: 10px; color: var(--muted); text-transform: uppercase;">Warehouse</div>
                            <div style="font-size: 12px;">{{ row.warehouse }}</div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-top: 4px;">
                        <Link :href="`/farmasi/inventory/opening-balance/${row.uuid}/edit`" class="btn btn-outline btn-sm" style="width: 100%; height: 38px;">
                            Sunting
                        </Link>
                        <button type="button" class="btn btn-outline btn-sm" style="width: 100%; height: 38px;" @click="vm.printOpeningBalance(row)">
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
.opening-balance-filter {
    grid-template-columns: minmax(320px, 1.1fr) minmax(260px, 1.4fr) minmax(160px, 0.5fr);
}

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

