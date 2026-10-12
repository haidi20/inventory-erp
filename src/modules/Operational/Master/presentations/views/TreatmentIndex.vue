<script setup lang="ts">import { TREATMENT_COLUMNS } from '@modules/Operational/Master/presentations/viewmodels/treatment.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import { useTreatmentVM } from '@modules/Operational/Master/presentations/viewmodels/treatment.vm';
import type { TreatmentFilters } from '@modules/Operational/Master/presentations/viewmodels/treatment.state';

const props = defineProps<{ filters?: TreatmentFilters }>();
const vm = useTreatmentVM();

onMounted(() => vm.initFilters(props.filters ?? {}));

const columns = TREATMENT_COLUMNS;
</script>

<template>
    <Head title="Master Tindakan - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/master/treatments" class="breadcrumb-link">Master</a>
                <span>/</span>
                <span style="color:var(--text);">Tindakan</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Master Tindakan</div>
            <div class="page-sub">Kelola data tindakan/layanan medis beserta harga dan fee dokter.</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button
                v-if="vm.canManage"
                type="button"
                class="btn btn-primary"
                data-testid="btn-create-treatment"
                @click="vm.openCreate()"
            >
                <i class="fa-solid fa-plus-circle"></i> Tambah Tindakan
            </button>
        </div>
    </div>

    <DataTable
        store-id="treatment-datatable"
        url="/masters/treatments"
        :columns="columns"
        :default-per-page="10"
        default-sort-by="name"
        search-placeholder="Cari tindakan..."
        search-test-id="input-search-treatment"
    >
        <template #cell-code="{ value }">
            <span class="badge badge-secondary">{{ value || '-' }}</span>
        </template>
        <template #cell-name="{ value }">
            <span style="font-weight:700;">{{ value }}</span>
        </template>
        <template #cell-duration_minutes="{ value }">
            <span style="color:var(--muted);">{{ value ?? 0 }}</span>
        </template>
        <template #cell-price="{ value }">
            <span>{{ value ? Number(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }) : '-' }}</span>
        </template>
        <template #cell-doctor_fee="{ value }">
            <span>{{ value ? Number(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }) : '-' }}</span>
        </template>
        <template #cell-is_active="{ value }">
            <span :class="value ? 'badge badge-success' : 'badge badge-danger'">
                {{ value ? 'Aktif' : 'Nonaktif' }}
            </span>
        </template>
        <template v-if="vm.canManage" #cell-actions="{ row }">
            <div style="display:flex;gap:6px;justify-content:center;">
                <button class="btn btn-sm btn-primary" title="Edit" data-testid="btn-edit-treatment" @click="vm.openEdit(row)">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn btn-sm btn-danger" title="Hapus" data-testid="btn-delete-treatment" @click="vm.deleteTreatment(row)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </template>
    </DataTable>
</template>

