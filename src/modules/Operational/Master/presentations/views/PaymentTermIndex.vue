<script setup lang="ts">import { PAYMENT_TERM_COLUMNS, createPaymentTermUIState } from '@modules/Operational/Master/presentations/viewmodels/payment_term.state';

import { onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import DataTable from "@modules/Core/shared/Components/common/DataTable.vue";
import { usePaymentTermVM } from "@modules/Operational/Master/presentations/viewmodels/payment_term.vm";
import PaymentTermForm from "@modules/Operational/Master/presentations/views/Partials/PaymentTermForm.vue";

const props = defineProps<{ filters: Record<string, any> }>();
const vm   = usePaymentTermVM();
const { datatableRef } = createPaymentTermUIState();
onMounted(() => {
    vm.initFilters(props.filters);
    vm.registerReload(() => datatableRef.value?.reload());
});

const columns = PAYMENT_TERM_COLUMNS;
</script>

<template>
    <Head title="Master Termin - Inventory" />

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
                <span>Termin Pembayaran</span>
            </div>
            <div class="page-title">Master Termin Pembayaran</div>
            <div class="page-sub">Kelola jangka waktu pembayaran (COD, T7, T14, T30, dll).</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button v-if="vm.canManage" type="button" @click="vm.openCreate" class="btn btn-primary" data-testid="btn-create-term">
                <i class="fa-solid fa-plus-circle"></i> Tambah Termin
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

    <DataTable ref="datatableRef" store-id="payment-term-datatable" :query-params="vm.activeQueryParams"
        url="/masters/payment-terms" :columns="columns" :default-per-page="10" default-sort-by="name"
        search-placeholder="Cari termin..." search-test-id="input-search-term">
        <template #cell-name="{ value }">
            <span class="badge badge-secondary">{{ value }}</span>
        </template>
        <template #cell-description="{ value }">
            <span style="color:var(--muted);">{{ value || "-" }}</span>
        </template>
        <template #cell-due_days="{ value }">
            <span style="font-weight:700;">{{ value }} hari</span>
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
                <button class="btn btn-sm btn-outline btn-icon" @click="vm.openEdit(row)" title="Edit" data-testid="btn-edit-term"><i class="fa-solid fa-pen"></i></button>
                <button class="btn btn-sm btn-outline btn-danger-soft btn-icon" @click="vm.deleteTerm(row.uuid)" title="Hapus" data-testid="btn-delete-term"><i class="fa-solid fa-trash"></i></button>
            </div>
        </template>
    </DataTable>

    <PaymentTermForm v-if="vm.showForm" />
</template>

