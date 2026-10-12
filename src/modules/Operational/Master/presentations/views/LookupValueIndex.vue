<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted } from 'vue';
import type { LookupValueFilters, LookupValueModel } from '@modules/Operational/Master/domain/models/lookup_value.model';
import { useLookupValueVM } from '@modules/Operational/Master/presentations/viewmodels/lookup_value.vm';
import LookupValueFiltersComp from '@modules/Operational/Master/presentations/views/Partials/LookupValueFilters.vue';
import LookupValueForm from '@modules/Operational/Master/presentations/views/Partials/LookupValueForm.vue';
import LookupValueTable from '@modules/Operational/Master/presentations/views/Partials/LookupValueTable.vue';

const props = defineProps<{
    lookupset: { uuid: string; id: number; name: string; program_code: string };
    values: any;
    filters: LookupValueFilters;
}>();

const vm = useLookupValueVM();
vm.init(props.lookupset.uuid, props.filters, props.values);

onMounted(() => {
    vm.initImportMenuDismiss();
});

onBeforeUnmount(() => {
    vm.disposeImportMenuDismiss();
});

</script>

<template>
    <Head :title="`Nilai Lookup: ${lookupset.name} - Inventory`" />

    <div v-if="vm.flash.success" class="alert-banner alert-success">
        <i class="fa-solid fa-circle-check"></i>
        {{ vm.flash.success }}
    </div>
    
    <div class="page-header" style="margin-bottom: 32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" style="text-decoration:none; color:inherit;"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/inventory/master/daftar-nilai" style="text-decoration:none; color:inherit;">Inventory</a>
                <span>/</span>
                <a href="/inventory/master/daftar-nilai" style="text-decoration:none; color:inherit;">Master</a>
                <span>/</span>
                <a href="/inventory/master/daftar-nilai" style="text-decoration:none; color:inherit;">Daftar Nilai</a>
                <span>/</span>
                <span style="color:var(--text); font-weight:700;">{{ lookupset.name }}</span>
            </div>
            <div class="page-title" style="margin-bottom: 4px;">{{ lookupset.name }}</div>
            <div class="page-sub">Kelola daftar nilai (items) untuk kategori ini.</div>
        </div>
        <div style="display:flex; gap:12px; align-items: center;">
            <a href="/inventory/master/daftar-nilai" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <div class="header-divider" style="height:32px; margin:0 4px;"></div>
            <button type="button" @click="vm.exportData()" class="btn btn-outline" :disabled="vm.isExporting.value" data-testid="btn-export-value">
                <i class="fa-solid fa-file-excel" style="color:#16a34a;"></i>
                Ekspor
            </button>
            <div class="import-menu" @click.stop>
                <button type="button" class="btn btn-outline import-menu-trigger" :disabled="vm.isImporting.value" @click="vm.toggleImportMenu" data-testid="btn-import-menu-value">
                    <i class="fa-solid fa-file-import"></i>
                    Impor
                    <i class="fa-solid fa-chevron-down import-menu-caret" :class="{ open: vm.showImportMenu.value }"></i>
                </button>
                <div v-if="vm.showImportMenu.value" class="import-menu-dropdown">
                    <button type="button" class="import-menu-item" @click="vm.openImportData" data-testid="btn-import-data-value">
                        <i class="fa-solid fa-file-import"></i>
                        Import Data
                    </button>
                    <button type="button" class="import-menu-item" @click="vm.closeImportMenuAndDownload" data-testid="btn-import-template-value">
                        <i class="fa-solid fa-file-arrow-down"></i>
                        Download Template
                    </button>
                </div>
            </div>
            <button type="button" @click="vm.openCreate" class="btn btn-primary" data-testid="btn-create-value">
                <i class="fa-solid fa-plus-circle"></i> Tambah Nilai
            </button>
        </div>
    </div>

    <LookupValueFiltersComp />

    <div style="margin-top: 32px;">
        <LookupValueTable :lookup-set-uuid="lookupset.uuid" :can-manage="true" />


    </div>

    <LookupValueForm v-if="vm.showForm.value" />

    <input
        id="lookupValueImportInput"
        type="file"
        accept=".xlsx,.xls"
        style="display:none;"
        @change="vm.handleImport"
    >
</template>

<style scoped>
.alert-banner {
    margin-bottom: 20px;
    padding: 14px 16px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    font-weight: 700;
}

.alert-success {
    background: #ecfdf5;
    color: #166534;
    border: 1px solid #86efac;
}

.import-menu {
    position: relative;
}

.import-menu-trigger {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.import-menu-caret {
    font-size: 11px;
    transition: transform 0.2s ease;
}

.import-menu-caret.open {
    transform: rotate(180deg);
}

.import-menu-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    min-width: 220px;
    padding: 8px;
    border: 1px solid var(--border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 18px 38px rgba(15, 23, 42, 0.12);
    display: grid;
    gap: 4px;
    z-index: 20;
}

.import-menu-item {
    width: 100%;
    border: none;
    background: transparent;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    text-align: left;
}

.import-menu-item:hover {
    background: #f8fafc;
    color: var(--primary);
}
</style>

