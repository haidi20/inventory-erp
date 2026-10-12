<script setup lang="ts">import { BRANCH_COLUMNS, createBranchUIState } from '@modules/Core/Organization/presentations/viewmodels/branch.state';

import { onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import DataTable from "@modules/Core/shared/Components/common/DataTable.vue";
import { useBranchVM } from "@modules/Core/Organization/presentations/viewmodels/branch.vm";
import BranchForm from "@modules/Core/Organization/presentations/views/Partials/BranchForm.vue";

const props = defineProps<{ filters: Record<string, any> }>();
const vm   = useBranchVM();
const { datatableRef } = createBranchUIState();

onMounted(() => {
    vm.initFilters(props.filters);
    vm.registerReload(() => datatableRef.value?.reload());
});

const columns = BRANCH_COLUMNS;
</script>

<template>
    <Head title="Master Cabang - Inventory" />

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
                <span>Cabang</span>
            </div>
            <div class="page-title">Master Cabang</div>
            <div class="page-sub">Kelola data cabang perusahaan.</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button v-if="vm.canManage" type="button" @click="vm.openCreate" class="btn btn-primary" data-testid="btn-create-branch">
                <i class="fa-solid fa-plus-circle"></i> Tambah Cabang
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

    <DataTable ref="datatableRef" store-id="branch-datatable" :query-params="vm.activeQueryParams"
        url="/master/branches" :columns="columns" :default-per-page="10" default-sort-by="name"
        search-placeholder="Cari cabang..." search-test-id="input-search-branch">
        <template #cell-short_name="{ value }">
            <span class="badge badge-secondary">{{ value }}</span>
        </template>
        <template #cell-name="{ value }">
            <span style="font-weight:700;">{{ value }}</span>
        </template>
        <template #cell-description="{ value }">
            <span style="color:var(--muted);">{{ value || "-" }}</span>
        </template>
        <template #cell-is_active="{ value }">
            <span :class="value ? 'badge badge-success' : 'badge badge-danger'">{{ value ? "Aktif" : "Nonaktif" }}</span>
        </template>
        <template v-if="vm.canManage" #cell-actions="{ row }">
            <div style="display:flex;gap:6px;justify-content:center;">
                <button class="btn btn-sm btn-outline btn-icon" @click="vm.openEdit(row)" title="Edit" data-testid="btn-edit-branch"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-outline btn-danger-soft btn-icon" @click="vm.deleteBranch(row.uuid)" title="Hapus" data-testid="btn-delete-branch"><i class="fa-solid fa-trash"></i></button>
            </div>
        </template>
    </DataTable>

    <BranchForm v-if="vm.showForm" />
</template>

