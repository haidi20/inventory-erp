<script setup lang="ts">import { EMPLOYEE_COLUMNS } from '@modules/Operational/Master/presentations/viewmodels/employee.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import { useEmployeeVM } from '@modules/Operational/Master/presentations/viewmodels/employee.vm';
import type { EmployeeFilters } from '@modules/Operational/Master/presentations/viewmodels/employee.state';

const props = defineProps<{ filters?: EmployeeFilters }>();
const vm = useEmployeeVM();

onMounted(() => vm.initFilters(props.filters ?? {}));

const columns = EMPLOYEE_COLUMNS;
</script>

<template>
    <Head title="Master Karyawan - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/master/employees" class="breadcrumb-link">Master</a>
                <span>/</span>
                <span style="color:var(--text);">Karyawan</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Master Karyawan</div>
            <div class="page-sub">Kelola data karyawan yang terdaftar di sistem.</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button
                v-if="vm.canManage"
                type="button"
                class="btn btn-primary"
                data-testid="btn-create-employee"
                @click="vm.openCreate()"
            >
                <i class="fa-solid fa-plus-circle"></i> Tambah Karyawan
            </button>
        </div>
    </div>

    <DataTable
        store-id="employee-datatable"
        url="/employees"
        :columns="columns"
        :default-per-page="10"
        default-sort-by="name"
        search-placeholder="Cari karyawan..."
        search-test-id="input-search-employee"
    >
        <template #cell-code="{ value }">
            <span class="badge badge-secondary">{{ value || '-' }}</span>
        </template>
        <template #cell-name="{ value }">
            <span style="font-weight:700;">{{ value }}</span>
        </template>
        <template #cell-position="{ value }">
            <span style="color:var(--muted);">{{ value || '-' }}</span>
        </template>
        <template #cell-phone="{ value }">
            <span style="color:var(--muted);">{{ value || '-' }}</span>
        </template>
        <template #cell-email="{ value }">
            <span style="color:var(--muted);">{{ value || '-' }}</span>
        </template>
        <template #cell-is_active="{ value }">
            <span :class="value ? 'badge badge-success' : 'badge badge-danger'">
                {{ value ? 'Aktif' : 'Nonaktif' }}
            </span>
        </template>
        <template v-if="vm.canManage" #cell-actions="{ row }">
            <div style="display:flex;gap:6px;justify-content:center;">
                <button class="btn btn-sm btn-primary" title="Edit" data-testid="btn-edit-employee" @click="vm.openEdit(row)">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn btn-sm btn-danger" title="Hapus" data-testid="btn-delete-employee" @click="vm.deleteEmployee(row)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </template>
    </DataTable>
</template>

