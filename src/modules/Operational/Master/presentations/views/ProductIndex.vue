<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';
import AsyncSelect2 from '@modules/Core/shared/Components/common/AsyncSelect2.vue';
import { useProductVM } from '@modules/Operational/Master/presentations/viewmodels/product.vm';

const vm = useProductVM();
const filters = usePage<Record<string, any> & { filters?: Record<string, any> }>().props.filters;

onMounted(() => {
    vm.initFilters(filters ?? {});
});



</script>

<template>

    <Head title="Master Produk - Inventory" />

    <div class="page-header product-page-header">
        <div class="page-heading">
            <a href="/inventory?tab=utama" class="back-link">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/inventory?tab=utama" class="breadcrumb-link">Master</a>
                <span>/</span>
                <span>Produk</span>
            </div>
            <div class="page-title">Master Produk</div>
            <div class="page-sub">Kelola data produk/obat (nama, kode, satuan, harga, stok).</div>
        </div>
        <div class="page-actions">
            <input v-if="vm.canManage" id="product-import-file" type="file" accept=".csv,text/csv" hidden data-testid="input-import-product" @change="vm.handleImportInput">
            <button v-if="vm.canManage" type="button" class="btn btn-outline" :disabled="vm.isImporting" data-testid="btn-import-product" @click="vm.openImportPicker">
                <i class="fa-solid fa-file-import"></i> {{ vm.isImporting ? 'Mengimpor...' : 'Impor' }}
            </button>
            <button v-if="vm.canManage" type="button" class="btn btn-outline" data-testid="btn-download-product-template" @click="vm.downloadImportTemplate">
                <i class="fa-solid fa-download"></i> Template
            </button>
            <button type="button" class="btn btn-outline" :disabled="vm.isExporting" data-testid="btn-export-product"
                @click="vm.exportProducts()">
                <i class="fa-solid fa-file-excel export-icon"></i>
                {{ vm.isExporting ? 'Mengekspor...' : 'Ekspor' }}
            </button>
            <button type="button" class="btn btn-outline" data-testid="btn-open-product-filter" @click="vm.openFilterModal()">
                <i class="fa-solid fa-filter"></i> Filter
                <span v-if="vm.hasActiveFilters" class="filter-state">ON</span>
            </button>
            <button v-if="vm.canManage" type="button" class="btn btn-primary" data-testid="btn-create-product"
                @click="vm.goToCreateProduct()">
                <i class="fa-solid fa-plus-circle"></i> Tambah Produk
            </button>
        </div>
    </div>

    <DataTable store-id="product-datatable" url="/products" :columns="vm.tableColumns" :default-per-page="10"
        default-sort-by="code" :query-params="vm.appliedFilters"
        :search-columns="vm.searchColumns" search-column-test-id="product-search-column"
        search-placeholder="Cari kode, nama, atau barcode produk..." search-test-id="input-search-product">
        <template #cell-code="{ value }">
            <span class="badge badge-secondary product-code">{{ value }}</span>
        </template>
        <template #cell-name="{ value }">
            <span class="product-name">{{ value }}</span>
        </template>
        <template #cell-unit_name="{ value }">
            <span class="cell-muted">{{ value || '-' }}</span>
        </template>
        <template #cell-latest_buy_price="{ value }">
            <span>{{ vm.formatRupiah(value) }}</span>
        </template>
        <template #cell-latest_sell_price="{ value }">
            <span>{{ vm.formatRupiah(value) }}</span>
        </template>
        <template #cell-base_price_buy="{ value }">
            <span class="cell-muted">{{ value || '-' }}</span>
        </template>
        <template #cell-base_price_sell="{ value }">
            <span>{{ vm.formatRupiah(value) }}</span>
        </template>
        <template #cell-is_active="{ value }">
            <span :class="value ? 'badge badge-success' : 'badge badge-danger'">
                {{ value ? 'Aktif' : 'Nonaktif' }}
            </span>
        </template>
        <template v-if="vm.canManage" #cell-actions="{ row }">
            <div class="table-actions">
                <button class="btn btn-sm btn-outline btn-icon" title="Edit" data-testid="btn-edit-product"
                    @click="vm.goToEditProduct(row)">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn btn-sm btn-outline btn-danger-soft btn-icon" title="Hapus"
                    data-testid="btn-delete-product" @click="vm.deleteProduct(row)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </template>
    </DataTable>

    <div v-if="vm.showFilterModal" class="modal-overlay" data-testid="product-filter-overlay" @click.self="vm.closeFilterModal()">
        <div class="modal-card modal-xl" data-testid="product-filter-modal">
            <div class="modal-head">
                <h2>Filter Produk</h2>
                <button type="button" class="modal-close" data-testid="btn-close-product-filter" @click="vm.closeFilterModal()">&times;</button>
            </div>

            <div class="filter-section">
                <h4>General</h4>
                <div v-if="vm.isSuperadmin" class="filter-field">
                    <span>Perusahaan</span>
                    <AsyncSelect2
                        v-model="vm.draftFilters.filter_company_uuid"
                        :endpoint="vm.optionEndpoint('company')"
                        label="name"
                        value-key="uuid"
                        :clearable="false"
                        placeholder="Pilih Perusahaan"
                        data-testid="filter-company"
                    />
                </div>
                <label class="filter-field">
                    <span>Barcode</span>
                    <input v-model="vm.draftFilters.filter_barcode" type="text" data-testid="filter-barcode">
                </label>
                <label class="filter-field">
                    <span>Kode Produk</span>
                    <input v-model="vm.draftFilters.filter_code" type="text" data-testid="filter-code">
                </label>
                <label class="filter-field">
                    <span>Nama Produk</span>
                    <input v-model="vm.draftFilters.filter_name" type="text" data-testid="filter-name">
                </label>
                <div class="filter-field">
                    <span>Satuan</span>
                    <AsyncSelect2
                        v-model="vm.draftFilters.filter_unit"
                        :endpoint="vm.optionEndpoint('unit')"
                        label="name"
                        value-key="uuid"
                        data-testid="filter-unit"
                    />
                </div>
                <div class="filter-field">
                    <span>Kelompok</span>
                    <AsyncSelect2
                        v-model="vm.draftFilters.filter_group_uuid"
                        :endpoint="vm.optionEndpoint('KELOMPOK')"
                        label="label"
                        value-key="uuid"
                        placeholder="Pilih Kelompok"
                        data-testid="filter-group"
                    />
                </div>
                <label class="filter-field">
                    <span>Keterangan</span>
                    <input v-model="vm.draftFilters.filter_keterangan" type="text" data-testid="filter-keterangan">
                </label>
            </div>

            <div class="filter-section">
                <h4>Filter Lainnya</h4>
                <div class="filter-field">
                    <span>Jenis</span>
                    <AsyncSelect2
                        v-model="vm.draftFilters.filter_kind"
                        :endpoint="vm.optionEndpoint('JENIS')"
                        label="label"
                        value-key="uuid"
                        placeholder="Pilih Jenis"
                        data-testid="filter-kind"
                    />
                </div>
                <div class="filter-field">
                    <span>Tipe Produk</span>
                    <AsyncSelect2
                        v-model="vm.draftFilters.filter_type_uuid"
                        :endpoint="vm.optionEndpoint('TIPE')"
                        label="label"
                        value-key="uuid"
                        placeholder="Pilih Tipe"
                        data-testid="filter-type"
                    />
                </div>
                <div class="filter-field">
                    <span>Merek</span>
                    <AsyncSelect2
                        v-model="vm.draftFilters.filter_brand_uuid"
                        :endpoint="vm.optionEndpoint('MERK')"
                        label="label"
                        value-key="uuid"
                        placeholder="Pilih Merek"
                        data-testid="filter-brand"
                    />
                </div>
                <div class="filter-field">
                    <span>Made-In</span>
                    <AsyncSelect2
                        v-model="vm.draftFilters.filter_origin_uuid"
                        :endpoint="vm.optionEndpoint('MADE_IN')"
                        label="label"
                        value-key="uuid"
                        placeholder="Pilih Asal Produksi"
                        data-testid="filter-origin"
                    />
                </div>
                <div class="filter-field">
                    <span>Grade</span>
                    <AsyncSelect2
                        v-model="vm.draftFilters.filter_grade_uuid"
                        :endpoint="vm.optionEndpoint('GRADE')"
                        label="label"
                        value-key="uuid"
                        placeholder="Pilih Grade"
                        data-testid="filter-grade"
                    />
                </div>
            </div>

            <div class="filter-section">
                <h4>History</h4>
                <div class="filter-field">
                    <span>User Buat</span>
                    <AsyncSelect2
                        v-model="vm.draftFilters.filter_created_by"
                        :endpoint="vm.optionEndpoint('user')"
                        label="name"
                        value-key="uuid"
                        placeholder="Pilih User"
                        data-testid="filter-created-by"
                    />
                </div>
                <div class="filter-field">
                    <span>User Edit</span>
                    <AsyncSelect2
                        v-model="vm.draftFilters.filter_updated_by"
                        :endpoint="vm.optionEndpoint('user')"
                        label="name"
                        value-key="uuid"
                        placeholder="Pilih User"
                        data-testid="filter-updated-by"
                    />
                </div>
                <div class="filter-field">
                    <span>Created Date</span>
                    <div class="filter-range">
                        <input v-model="vm.draftFilters.filter_created_from" type="date" data-testid="filter-created-from">
                        <small>s/d</small>
                        <input v-model="vm.draftFilters.filter_created_to" type="date" data-testid="filter-created-to">
                    </div>
                </div>
                <div class="filter-field">
                    <span>Last Update Date</span>
                    <div class="filter-range">
                        <input v-model="vm.draftFilters.filter_updated_from" type="date" data-testid="filter-updated-from">
                        <small>s/d</small>
                        <input v-model="vm.draftFilters.filter_updated_to" type="date" data-testid="filter-updated-to">
                    </div>
                </div>
                <label class="filter-field">
                    <span>Periode</span>
                    <input v-model="vm.draftFilters.filter_periode" type="month" data-testid="filter-periode">
                </label>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-outline" data-testid="btn-reset-product-filter" @click="vm.resetFilters()">Reset</button>
                <button type="button" class="btn btn-primary" data-testid="btn-apply-product-filter" @click="vm.applyFilters()">Terapkan</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.product-page-header {
    align-items: flex-start;
    gap: 24px;
    container: product-header / inline-size;
    flex-wrap: wrap;
}

.page-heading {
    min-width: 0;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
    padding: 6px 10px 6px 8px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--card);
    color: var(--muted);
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
    text-decoration: none;
    transition: color .18s ease, border-color .18s ease, background-color .18s ease;
}

.back-link:hover {
    background: var(--bg);
    border-color: var(--primary);
    color: var(--primary);
}

.page-actions {
    display: flex;
    flex: 0 0 auto;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
}

.page-actions .btn {
    min-height: 38px;
    white-space: nowrap;
}

.export-icon {
    color: #16a34a;
}

.filter-state {
    margin-left: 2px;
    padding: 3px 6px;
    border-radius: 5px;
    background: var(--primary-light);
    color: var(--primary);
    font-size: 10px;
    font-weight: 800;
    line-height: 1;
}

.product-name {
    display: block;
    overflow: hidden;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-code {
    white-space: nowrap;
}

.cell-muted {
    color: var(--muted);
}

.table-actions {
    display: inline-flex;
    gap: 6px;
    justify-content: center;
}

.table-actions .btn-icon {
    width: 30px;
    height: 30px;
    padding: 0;
    border-radius: 8px;
    justify-content: center;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
    padding: 24px;
}

.modal-card {
    box-sizing: border-box;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    align-items: end;
    width: min(1140px, 100%);
    max-width: 1140px;
    max-height: 90vh;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 22px 24px;
    border-radius: 16px;
    background: var(--card);
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
}

.modal-head {
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.modal-head h2 {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
}

.modal-close {
    width: 28px;
    height: 28px;
    border: 0;
    border-radius: 8px;
    background: var(--lv-background);
    color: var(--lv-neutral);
    font-size: 15px;
    cursor: pointer;
}

.filter-section {
    display: contents;
}

.filter-section h4 {
    grid-column: 1 / -1;
    margin: 0;
    padding-top: 2px;
    font-size: 13px;
    font-weight: 800;
    color: var(--lv-neutral);
}

.filter-field {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 0;
}

.filter-field > span {
    font-size: 12px;
    font-weight: 700;
    color: var(--lv-neutral);
}

.filter-field input,
.filter-field select {
    min-height: 38px;
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 13px;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.filter-field input:focus,
.filter-field select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
}

.filter-field :deep(.vs__dropdown-toggle) {
    min-height: 39px;
    border-radius: 10px;
}

.filter-range {
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-range input {
    flex: 1;
    min-width: 0;
    width: 0;
    min-height: 38px;
}

@media (max-width: 991px) {
    .modal-card {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .modal-card {
        grid-template-columns: 1fr;
    }

    .product-page-header {
        flex-direction: column;
        gap: 16px;
    }

    .page-actions {
        justify-content: flex-start;
        width: 100%;
        flex-basis: 100%;
    }

    .page-actions .btn {
        flex: 1 1 100%;
    }

    .page-actions .btn {
        justify-content: center;
    }
}

.filter-range small {
    color: var(--lv-neutral);
}

.modal-actions {
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 2px;
    padding-top: 14px;
    border-top: 1px solid var(--border);
}

@container product-header (max-width: 1100px) {
    .product-page-header {
        flex-direction: column;
        gap: 16px;
    }

    .page-actions {
        width: 100%;
        justify-content: flex-start;
    }

}
</style>

