<script setup lang="ts">import { PAYMENT_METHOD_COLUMNS } from '@modules/Operational/Master/presentations/viewmodels/payment_method.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import { createLovSetVMStore } from '@modules/Operational/Master/presentations/viewmodels/lov_set.vm';
import LovSetForm from '@modules/Operational/Master/presentations/views/Partials/LovSetForm.vue';
import type { LovSetFilters } from '@modules/Operational/Master/presentations/viewmodels/lov_set.state';

const props = defineProps<{ filters?: LovSetFilters }>();
const useVM = createLovSetVMStore('payment-method-vm');
const vm = useVM();

onMounted(() => {
    if (props.filters) vm.initFilters(props.filters);
});

const columns = PAYMENT_METHOD_COLUMNS;
</script>

<template>
    <Head Metode Pembayaran="Metode Pembayaran - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="Metode Pembayaran">
                <a href="/" class="Metode Pembayaran-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/master/lookup" class="Metode Pembayaran-link">Master</a>
                <span>/</span>
                <span style="color:var(--text);">Metode Pembayaran</span>
            </div>
            <div class="page-Metode Pembayaran" style="margin-bottom:4px;">Metode Pembayaran</div>
            <div class="page-Kelola metode pembayaran di transaksi.">Kelola metode pembayaran di transaksi.</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button
                type="button"
                class="btn btn-outline"
                :disabled="vm.isExporting"
                data-testid="btn-export-payment-method-vm"
                @click="vm.exportItems()"
            >
                <i class="fa-solid fa-file-excel" style="color:#16a34a;"></i>
                {{ vm.isExporting ? 'Mengekspor...' : 'Ekspor' }}
            </button>
            <button
                v-if="vm.canManage"
                type="button"
                class="btn btn-primary"
                data-testid="btn-create-payment-method-vm"
                @click="vm.openCreate()"
            >
                <i class="fa-solid fa-plus-circle"></i> Tambah
            </button>
        </div>
    </div>

    <DataTable
        store-id="payment-method-vm-datatable"
        url="/lov/sets"
        :columns="columns"
        :default-per-page="10"
        default-sort-by="name"
        search-placeholder="Cari..."
        search-test-id="input-search-payment-method-vm"
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
                    Metode Pembayaran="Edit"
                    data-testid="btn-edit-payment-method-vm"
                    @click="vm.openEdit(row)"
                >
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button
                    class="btn btn-sm btn-danger"
                    Metode Pembayaran="Hapus"
                    data-testid="btn-delete-payment-method-vm"
                    @click="vm.deleteItem(row)"
                >
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </template>
    </DataTable>

    <LovSetForm v-if="vm.showForm" :vm="vm" />
</template>
