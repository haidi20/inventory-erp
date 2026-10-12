<script setup lang="ts">import { SUPPLIER_COLUMNS, createSupplierUIState } from '@modules/Operational/Master/presentations/viewmodels/supplier.state';

import { onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import DataTable from "@modules/Core/shared/Components/common/DataTable.vue";
import { useSupplierVM } from "@modules/Operational/Master/presentations/viewmodels/supplier.vm";
import SupplierForm from "@modules/Operational/Master/presentations/views/Partials/SupplierForm.vue";
import type { SupplierFilters } from "@modules/Operational/Master/presentations/viewmodels/supplier.state";

const props = defineProps<{ filters?: SupplierFilters }>();
const vm = useSupplierVM();
const { datatableRef } = createSupplierUIState();
onMounted(() => {
    vm.initFilters(props.filters ?? {});
    vm.registerReload(() => datatableRef.value?.reload());
});

const columns = SUPPLIER_COLUMNS;
</script>

<template>
    <Head title="Master Supplier - Inventory" />

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
                <a href="/customer-supplier/suppliers" class="breadcrumb-link">Master</a>
                <span>/</span>
                <span>Supplier</span>
            </div>
            <div class="page-title">Master Supplier</div>
            <div class="page-sub">Kelola data pemasok/supplier untuk kebutuhan pembelian.</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button v-if="vm.canManage" type="button" class="btn btn-primary" data-testid="btn-create-supplier" @click="vm.openCreate()">
                <i class="fa-solid fa-plus-circle"></i> Tambah Supplier
            </button>
        </div>
    </div>

    <div style="margin-bottom:16px;">
        <a href="/customer-supplier/suppliers" class="btn btn-outline btn-sm">
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

    <DataTable ref="datatableRef" store-id="supplier-datatable" :query-params="vm.activeQueryParams"
        url="/suppliers" :columns="columns" :default-per-page="10" default-sort-by="name"
        search-placeholder="Cari supplier..." search-test-id="input-search-supplier">
        <template #cell-name="{ value }">
            <span style="font-weight:700;">{{ value }}</span>
        </template>
        <template #cell-phone="{ value }">
            <span style="color:var(--muted);">{{ value || "-" }}</span>
        </template>
        <template #cell-email="{ value }">
            <span style="color:var(--muted);">{{ value || "-" }}</span>
        </template>
        <template #cell-term_days="{ value }">
            <span class="badge badge-secondary">{{ value ?? 0 }} hari</span>
        </template>
        <template #cell-is_active="{ value }">
            <span :class="value ? 'badge badge-success' : 'badge badge-danger'">{{ value ? "Aktif" : "Nonaktif" }}</span>
        </template>
        <template v-if="vm.canManage" #cell-actions="{ row }">
            <div style="display:flex;gap:6px;justify-content:center;">
                <button class="btn btn-sm btn-outline btn-icon" title="Edit" data-testid="btn-edit-supplier" @click="vm.openEdit(row)"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-outline btn-danger-soft btn-icon" title="Hapus" data-testid="btn-delete-supplier" @click="vm.deleteSupplier(row)"><i class="fa-solid fa-trash"></i></button>
            </div>
        </template>
    </DataTable>

    <SupplierForm v-if="vm.showForm" />
</template>

