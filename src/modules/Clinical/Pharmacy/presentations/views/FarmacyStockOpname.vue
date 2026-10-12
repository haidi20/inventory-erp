<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';
import Pagination from '@modules/Core/shared/Components/Pagination.vue';
import { useFarmacyStockOpnameVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_stock_opname.vm';
import { OPNAME_PER_PAGE_OPTIONS } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_stock_opname.state';
import '@modules/Clinical/Pharmacy/presentations/styles/farmacy-stock-adjustment.css';

const vm = useFarmacyStockOpnameVM();
const perPageOptions = OPNAME_PER_PAGE_OPTIONS;

onMounted(() => {
    void vm.fetch();
});
</script>

<template>
    <Head title="Stok Opname - Inventory" />

    <section class="adjustment-page" aria-labelledby="stock-opname-title">
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
                    <button type="button" class="breadcrumb-link is-active">Stok Opname</button>
                </div>
                <div id="stock-opname-title" class="page-title" style="margin-bottom: 4px;">Stok Opname</div>
                <div class="page-sub">Kelola pencatatan stok fisik gudang dan selisih stok pembukuan.</div>
            </div>
            <div style="display:flex; gap:12px; align-items: center;">
                <div class="header-divider" style="height: 32px; margin: 0 8px;"></div>
                <button type="button" class="btn btn-outline" title="Download Excel">
                    <i class="fa-solid fa-file-excel" style="color:#16a34a;"></i>
                    Ekspor
                </button>
                <Link href="/farmasi/inventory/opname/create" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Buat
                </Link>
            </div>
        </div>

        <section class="adjustment-filter-panel stock-opname-filter-panel" aria-label="Filter daftar stok opname">
            <label class="filter-field search-field">
                <span>Cari</span>
                <div class="filter-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input v-model="vm.search" type="search" placeholder="Nomor opname, unit, gudang, deskripsi" />
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
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--text);">Daftar Stok Opname</h3>
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
                            <th style="width: 16%;">Tanggal</th>
                            <th style="width: 18%;">No. Opname</th>
                            <th style="width: 14%;">Unit Usaha</th>
                            <th style="width: 16%;">Gudang</th>
                            <th>Deskripsi</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 150px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in vm.visibleRows" :key="row.uuid">
                            <td>{{ row.date }}</td>
                            <td><code class="adjustment-reference">{{ row.referenceNo }}</code></td>
                            <td><span class="badge badge-secondary" style="font-size: 9px;">{{ row.businessUnit }}</span></td>
                            <td>{{ row.warehouse }}</td>
                            <td><div class="adjustment-description">{{ row.description }}</div></td>
                            <td>
                                <span class="status-badge" :class="vm.statusClass(row.status)">
                                    {{ row.status }}
                                </span>
                            </td>
                            <td>
                                <div class="table-action-cell" style="justify-content: center;">
                                    <Link :href="`/farmasi/inventory/opname/${row.uuid}/edit`" class="adjustment-action-button is-edit" title="Sunting Data">
                                        <i class="fa-solid fa-pen"></i>
                                    </Link>
                                    <button type="button" class="adjustment-action-button is-print" title="Cetak Data" @click.stop="vm.printOpname(row)">
                                        <i class="fa-solid fa-print"></i>
                                    </button>
                                    <button type="button" class="adjustment-action-button is-delete" title="Hapus Data" @click.stop="vm.confirmDelete(row)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!vm.isLoading && vm.visibleRows.length === 0">
                            <td colspan="7">
                                <div class="empty-state" style="border:none;background:transparent;padding:60px;">
                                    <i class="fa-solid fa-clipboard-list" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                                    Data stok opname tidak ditemukan
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
.stock-opname-filter-panel {
    grid-template-columns: minmax(260px, 1.4fr) minmax(320px, 1.1fr) minmax(160px, 0.5fr);
}

.filter-select-vs {
    min-width: 160px;
}

:deep(.vs__dropdown-toggle) {
    min-height: 38px;
    border: 1px solid var(--stock-border);
    border-radius: 8px;
    background: #ffffff;
}

@media (max-width: 1023px) {
    .stock-opname-filter-panel {
        grid-template-columns: 1fr;
    }
}
</style>

