<script setup lang="ts">import { LOOKUP_VALUE_TABLE_COLUMNS } from '@modules/Operational/Master/presentations/viewmodels/lookup_value_table.state';

import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import { useLookupValueVM } from '@modules/Operational/Master/presentations/viewmodels/lookup_value.vm';

const props = defineProps<{
    lookupSetUuid: string;
    canManage: boolean;
}>();

const vm = useLookupValueVM();

const columns = LOOKUP_VALUE_TABLE_COLUMNS;

const apiUrl = vm.apiUrl;
const queryParams = vm.queryParams;
const formatDate = vm.formatDate;
</script>

<template>
    <DataTable
        store-id="lookup-value-table"
        :columns="columns"
        :url="apiUrl"
        :query-params="queryParams"
        default-sort-by="seq"
        default-sort-direction="asc"
        :default-per-page="10"
        :per-page-options="[10, 25, 50, 100]"
        search-placeholder="Cari nama atau kode..."
    >
        <template #cell-display_name="{ row }">
            <div class="value-main-name">{{ row.display_name }}</div>
            <div v-if="row.description" class="value-sub-text">{{ row.description }}</div>
        </template>

        <template #cell-program_code="{ value }">
            <span v-if="value" class="badge badge-secondary" style="font-size:10px;">{{ value }}</span>
            <span v-else class="text-muted">-</span>
        </template>

        <template #cell-primary_flag="{ value }">
            <span v-if="value === 'Y'" class="badge badge-primary">
                <i class="fa-solid fa-star" style="font-size:10px;"></i> Default
            </span>
            <span v-else class="text-muted">-</span>
        </template>

        <template #cell-active_flag="{ value }">
            <span :class="['badge', value === 'Y' ? 'badge-success' : 'badge-danger']">
                {{ value === 'Y' ? 'Aktif' : 'Non-Aktif' }}
            </span>
        </template>

        <template #cell-start_date="{ value }">
            <span style="font-size:12px;">{{ formatDate(value) }}</span>
        </template>

        <template #cell-end_date="{ value }">
            <span style="font-size:12px;">{{ formatDate(value) }}</span>
        </template>

        <template #cell-max_stock="{ value }">
            <span style="font-size:12px;">{{ value != null ? value : '-' }}</span>
        </template>

        <template #cell-_actions="{ row }">
            <div class="table-action-cell" style="justify-content:center;">
                <button
                    v-if="canManage"
                    type="button"
                    class="btn btn-outline btn-sm"
                    @click.stop="vm.openEdit(row)"
                    title="Edit Nilai"
                    data-testid="btn-edit-value"
                >
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button
                    v-if="canManage"
                    type="button"
                    class="btn btn-outline btn-sm btn-danger-soft"
                    @click.stop="vm.remove(row)"
                    title="Hapus Nilai"
                    data-testid="btn-delete-value"
                >
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </template>
    </DataTable>
</template>

<style scoped>
.value-main-name { font-weight: 700; color: var(--text); font-size: 13.5px; }
.value-sub-text  { font-size: 11px; color: var(--muted); margin-top: 2px; }
.text-muted      { color: var(--muted); font-size: 12px; }
.badge-success   { background: #dcfce7; color: #166534; }
.badge-danger    { background: #fee2e2; color: #991b1b; }
.badge-primary   { background: #ede9fe; color: #5b21b6; }
.badge-secondary { background: #f1f5f9; color: #475569; }
.btn-danger-soft { color: #ef4444; border-color: rgba(239,68,68,0.1); }
.btn-danger-soft:hover { background: #fee2e2; border-color: #ef4444; color: #b91c1c; }
</style>
