<script setup lang="ts">import { PHARMACY_SETTING_COLUMNS } from '@modules/Clinical/Pharmacy/presentations/viewmodels/pharmacy_setting.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import { createLovSetVMStore } from '@modules/Operational/Master/presentations/viewmodels/lov_set.vm';
import LovSetForm from '@modules/Operational/Master/presentations/views/Partials/LovSetForm.vue';
import type { LovSetFilters } from '@modules/Operational/Master/presentations/viewmodels/lov_set.state';

const props = defineProps<{ filters?: LovSetFilters }>();
const useVM = createLovSetVMStore('pharmacy-setting-vm');
const vm = useVM();

onMounted(() => {
    if (props.filters) vm.initFilters(props.filters);
});

const columns = PHARMACY_SETTING_COLUMNS;
</script>

<template>
    <Head title="Pengaturan Farmasi - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/pharmac" class="breadcrumb-link">Farmasi</a>
                <span>/</span>
                <span style="color:var(--text);">Pengaturan</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Pengaturan Farmasi</div>
            <div class="page-sub">Pengaturan khusus farmasi (apoteker default, margin obat, dll).</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button
                type="button"
                class="btn btn-outline"
                :disabled="vm.isExporting"
                data-testid="btn-export-pharmacy-setting"
                @click="vm.exportItems()"
            >
                <i class="fa-solid fa-file-excel" style="color:#16a34a;"></i>
                {{ vm.isExporting ? 'Mengekspor...' : 'Ekspor' }}
            </button>
            <button
                v-if="vm.canManage"
                type="button"
                class="btn btn-primary"
                data-testid="btn-create-pharmacy-setting"
                @click="vm.openCreate()"
            >
                <i class="fa-solid fa-plus-circle"></i> Tambah
            </button>
        </div>
    </div>

    <DataTable
        store-id="pharmacy-setting-datatable"
        url="/lov/sets"
        :columns="columns"
        :default-per-page="10"
        default-sort-by="name"
        search-placeholder="Cari pengaturan..."
        search-test-id="input-search-pharmacy-setting"
    >
        <template #cell-program_code="{ value }">
            <span class="badge badge-secondary">{{ value || '-' }}</span>
        </template>
        <template #cell-name="{ value }">
            <span style="font-weight:700;">{{ value }}</span>
        </template>
        <template #cell-description="{ value }">
            <span style="color:var(--muted);">{{ value || '-' }}</span>
        </template>
        <template v-if="vm.canManage" #cell-actions="{ row }">
            <div style="display:flex;gap:6px;justify-content:center;">
                <button
                    class="btn btn-sm btn-primary"
                    title="Edit"
                    data-testid="btn-edit-pharmacy-setting"
                    @click="vm.openEdit(row)"
                >
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button
                    class="btn btn-sm btn-danger"
                    title="Hapus"
                    data-testid="btn-delete-pharmacy-setting"
                    @click="vm.deleteItem(row)"
                >
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </template>
    </DataTable>

    <LovSetForm v-if="vm.showForm" :vm="vm" />
</template>

