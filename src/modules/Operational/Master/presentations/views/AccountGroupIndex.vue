<script setup lang="ts">import { ACCOUNT_GROUP_COLUMNS } from '@modules/Operational/Master/presentations/viewmodels/account_group.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import { createLovSetVMStore } from '@modules/Operational/Master/presentations/viewmodels/lov_set.vm';
import LovSetForm from '@modules/Operational/Master/presentations/views/Partials/LovSetForm.vue';
import type { LovSetFilters } from '@modules/Operational/Master/presentations/viewmodels/lov_set.state';

const props = defineProps<{ filters?: LovSetFilters }>();
const useVM = createLovSetVMStore('account-group-vm');
const vm = useVM();

onMounted(() => {
    if (props.filters) vm.initFilters(props.filters);
});

const columns = ACCOUNT_GROUP_COLUMNS;
</script>

<template>
    <Head Grup Akun="Grup Akun - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="Grup Akun">
                <a href="/" class="Grup Akun-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/master/lookup" class="Grup Akun-link">Master</a>
                <span>/</span>
                <span style="color:var(--text);">Grup Akun</span>
            </div>
            <div class="page-Grup Akun" style="margin-bottom:4px;">Grup Akun</div>
            <div class="page-Kelola grup akun untuk pengelompokan COA.">Kelola grup akun untuk pengelompokan COA.</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button
                type="button"
                class="btn btn-outline"
                :disabled="vm.isExporting"
                data-testid="btn-export-account-group-vm"
                @click="vm.exportItems()"
            >
                <i class="fa-solid fa-file-excel" style="color:#16a34a;"></i>
                {{ vm.isExporting ? 'Mengekspor...' : 'Ekspor' }}
            </button>
            <button
                v-if="vm.canManage"
                type="button"
                class="btn btn-primary"
                data-testid="btn-create-account-group-vm"
                @click="vm.openCreate()"
            >
                <i class="fa-solid fa-plus-circle"></i> Tambah
            </button>
        </div>
    </div>

    <DataTable
        store-id="account-group-vm-datatable"
        url="/lov/sets"
        :columns="columns"
        :default-per-page="10"
        default-sort-by="name"
        search-placeholder="Cari..."
        search-test-id="input-search-account-group-vm"
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
                    Grup Akun="Edit"
                    data-testid="btn-edit-account-group-vm"
                    @click="vm.openEdit(row)"
                >
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button
                    class="btn btn-sm btn-danger"
                    Grup Akun="Hapus"
                    data-testid="btn-delete-account-group-vm"
                    @click="vm.deleteItem(row)"
                >
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </template>
    </DataTable>

    <LovSetForm v-if="vm.showForm" :vm="vm" />
</template>
