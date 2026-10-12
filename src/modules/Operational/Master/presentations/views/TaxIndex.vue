<script setup lang="ts">import { TAX_COLUMNS, createTaxUIState } from '@modules/Operational/Master/presentations/viewmodels/tax.state';

import { onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import DataTable from "@modules/Core/shared/Components/common/DataTable.vue";
import { useTaxVM } from "@modules/Operational/Master/presentations/viewmodels/tax.vm";
import TaxForm from "@modules/Operational/Master/presentations/views/Partials/TaxForm.vue";

const props = defineProps<{ filters: Record<string, any> }>();
const vm   = useTaxVM();
const { datatableRef } = createTaxUIState();
onMounted(() => {
    vm.initFilters(props.filters);
    vm.registerReload(() => datatableRef.value?.reload());
});

const columns = TAX_COLUMNS;
</script>

<template>
    <Head title="Master Pajak - Inventory" />

    <div v-if="vm.flash.success" class="alert-banner alert-success">
        <i class="fa-solid fa-circle-check"></i> {{ vm.flash.success }}
    </div>
    <div v-else-if="vm.flash.error" class="alert-banner alert-error">
        <i class="fa-solid fa-triangle-exclamation"></i> {{ vm.flash.error }}
    </div>

    <div class="page-header">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/inventory?tab=utama" class="breadcrumb-link">Master</a>
                <span>/</span>
                <span>Pajak</span>
            </div>
            <div class="page-title">Master Pajak</div>
            <div class="page-sub">Kelola kode pajak (PPN/PPh) yang digunakan pada transaksi.</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button v-if="vm.canManage" type="button" @click="vm.openCreate" class="btn btn-primary" data-testid="btn-create-tax">
                <i class="fa-solid fa-plus-circle"></i> Tambah Pajak
            </button>
        </div>
    </div>

    <div style="margin-bottom:16px;">
        <a href="/inventory?tab=utama" class="btn btn-outline btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="filter-status-card">
        <div class="filter-status-label"><i class="fa-solid fa-filter"></i><span>Filter Status</span></div>
        <div class="filter-status-buttons">
            <button class="filter-btn" :class="{ active: vm.filterActive === null }" @click="vm.setFilterActive(null)" data-testid="filter-all"><i class="fa-solid fa-list"></i> Semua</button>
            <button class="filter-btn filter-btn-active" :class="{ active: vm.filterActive === true }" @click="vm.setFilterActive(true)" data-testid="filter-active"><i class="fa-solid fa-check-circle"></i> Aktif</button>
            <button class="filter-btn filter-btn-inactive" :class="{ active: vm.filterActive === false }" @click="vm.setFilterActive(false)" data-testid="filter-inactive"><i class="fa-solid fa-times-circle"></i> Non-aktif</button>
        </div>
    </div>

    <DataTable ref="datatableRef" store-id="tax-datatable" :query-params="vm.activeQueryParams"
        url="/masters/taxes" :columns="columns" :default-per-page="10" default-sort-by="tax_code"
        search-placeholder="Cari kode pajak..." search-test-id="input-search-tax">
        <template #cell-tax_code="{ value }">
            <span class="badge badge-secondary">{{ value }}</span>
        </template>
        <template #cell-percentage="{ value }">
            <span style="font-weight:700;">{{ Number(value).toFixed(2) }}%</span>
        </template>
        <template #cell-is_pph="{ value }">
            <i v-if="value" class="fa-solid fa-check" style="color:#16a34a;"></i>
            <i v-else class="fa-solid fa-minus" style="color:#cbd5e1;"></i>
        </template>
        <template #cell-is_primary="{ value }">
            <i v-if="value" class="fa-solid fa-check" style="color:#16a34a;"></i>
            <i v-else class="fa-solid fa-minus" style="color:#cbd5e1;"></i>
        </template>
        <template #cell-is_active="{ value }">
            <span :class="value ? 'badge badge-success' : 'badge badge-danger'">{{ value ? "Aktif" : "Nonaktif" }}</span>
        </template>
        <template v-if="vm.canManage" #cell-actions="{ row }">
            <div style="display:flex;gap:6px;justify-content:center;">
                <button class="btn btn-sm btn-outline btn-icon" @click="vm.openEdit(row)" title="Edit" data-testid="btn-edit-tax"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-outline btn-danger-soft btn-icon" @click="vm.deleteTax(row.uuid)" title="Hapus" data-testid="btn-delete-tax"><i class="fa-solid fa-trash"></i></button>
            </div>
        </template>
    </DataTable>

    <TaxForm v-if="vm.showForm" />
</template>

