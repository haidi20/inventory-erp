<script setup lang="ts">import { LOOKUP_COLUMNS, createLookupUIState } from '@modules/Operational/Master/presentations/viewmodels/lookup.state';

import { onBeforeUnmount, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import DataTable from "@modules/Core/shared/Components/common/DataTable.vue";
import { useLookupVM } from "@modules/Operational/Master/presentations/viewmodels/lookup.vm";
import LookupForm from "@modules/Operational/Master/presentations/views/Partials/LookupForm.vue";
import type { LookupFilters, LookupSummary } from "@modules/Operational/Master/domain/models/lookup.model";

const props = defineProps<{ lookups: any; summary: LookupSummary; filters: LookupFilters }>();
const vm   = useLookupVM();
const { datatableRef } = createLookupUIState();
onMounted(() => {
    vm.initFilters(props.filters);
    vm.registerReload(() => datatableRef.value?.reload());
    vm.initImportMenuDismiss();
});
onBeforeUnmount(() => vm.disposeImportMenuDismiss());

const columns = LOOKUP_COLUMNS;
</script>

<template>
    <Head title="Master Lookup - Inventory" />

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
                <span>Lookup</span>
            </div>
            <div class="page-title">Master Lookup</div>
            <div class="page-sub">Kelola daftar kategori lookup yang dipakai modul klinik secara terpusat.</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button type="button" @click="vm.exportLookups()" class="btn btn-outline" :disabled="vm.isExporting" data-testid="btn-export-lookup">
                <i class="fa-solid fa-file-excel" style="color:#16a34a;"></i>
                {{ vm.isExporting ? "Mengekspor..." : "Ekspor" }}
            </button>
            <div v-if="vm.canManageLookups" class="import-menu" @click.stop>
                <button type="button" class="btn btn-outline" :disabled="vm.isImporting" @click.stop="vm.toggleImportMenu" data-testid="btn-import-menu-lookup">
                    <i class="fa-solid fa-file-import"></i>
                    {{ vm.isImporting ? "Mengimpor..." : "Impor" }}
                    <i class="fa-solid fa-chevron-down" :style="{ transform: vm.showImportMenu ? 'rotate(180deg)' : '', transition: 'transform .2s', fontSize: '11px' }"></i>
                </button>
                <div v-if="vm.showImportMenu" class="import-menu-dropdown">
                    <button type="button" class="import-menu-item" @click="vm.openImportData" data-testid="btn-import-data-lookup">
                        <i class="fa-solid fa-file-import"></i> Import Data
                    </button>
                    <button type="button" class="import-menu-item" @click="vm.downloadImportTemplate" data-testid="btn-import-template-lookup">
                        <i class="fa-solid fa-file-arrow-down"></i> Download Template
                    </button>
                </div>
            </div>
            <button v-if="vm.canManageLookups" type="button" @click="vm.openCreate" class="btn btn-primary" data-testid="btn-create-lookup">
                <i class="fa-solid fa-plus-circle"></i> Tambah Lookup
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

    <DataTable ref="datatableRef" store-id="lookup-datatable" :query-params="vm.activeQueryParams"
        url="/lov/sets" :columns="columns" :default-per-page="10" default-sort-by="set_code"
        search-placeholder="Cari lookup..." search-test-id="input-search-lookup">
        <template #cell-set_code="{ value }">
            <span class="badge badge-secondary">{{ value }}</span>
        </template>
        <template #cell-set_name="{ value }">
            <span style="font-weight:700;">{{ value }}</span>
        </template>
        <template #cell-description="{ value }">
            <span style="color:var(--muted);">{{ value || "-" }}</span>
        </template>
        <template #cell-scope="{ value }">
            <span :class="value === 'system' ? 'badge badge-info' : 'badge badge-secondary'">{{ value }}</span>
        </template>
        <template #cell-is_active="{ value }">
            <span :class="value ? 'badge badge-success' : 'badge badge-danger'">{{ value ? "Aktif" : "Nonaktif" }}</span>
        </template>
        <template v-if="vm.canManageLookups" #cell-actions="{ row }">
            <div style="display:flex;gap:6px;justify-content:center;">
                <button class="btn btn-sm btn-outline btn-icon" @click="vm.openEdit(row)" title="Edit" data-testid="btn-edit-lookup"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-outline btn-danger-soft btn-icon" @click="vm.deleteLookup(row.uuid)" title="Hapus" data-testid="btn-delete-lookup"><i class="fa-solid fa-trash"></i></button>
            </div>
        </template>
    </DataTable>

    <LookupForm v-if="vm.showForm" />

    <input id="lookupImportInput" type="file" accept=".xlsx,.xls" style="display:none;" @change="vm.handleImport" />
</template>

