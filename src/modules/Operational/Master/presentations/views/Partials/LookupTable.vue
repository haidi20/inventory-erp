<script setup lang="ts">import { LOOKUP_TABLE_COLUMNS, LOOKUP_TABLE_SEARCH_COLUMNS } from '@modules/Operational/Master/presentations/viewmodels/lookup_table.state';


import { route } from 'ziggy-js';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import { useLookupVM } from '@modules/Operational/Master/presentations/viewmodels/lookup.vm';

defineProps<{ lookups: any[] }>();

const vm = useLookupVM();
const columns = LOOKUP_TABLE_COLUMNS;

const searchColumns = LOOKUP_TABLE_SEARCH_COLUMNS;

</script>

<template>
    <DataTable :ref="vm.setLookupTable" store-id="lookup-table" :columns="columns" :url="'/lov/sets'" :search-columns="searchColumns"
        search-test-id="input-search-lookup" search-column-test-id="select-search-column-lookup"
        search-placeholder="Ketik nama atau kode program..." default-sort-by="name" default-sort-direction="asc"
        :default-per-page="10" :per-page-options="[10, 25, 50, 100]">
        <template #cell-user_can_edit_flag="{ value }">
            <span :class="['badge', value === 'Y' ? 'badge-success' : 'badge-danger']">
                {{ value === 'Y' ? 'EDITABLE' : 'READONLY' }}
            </span>
        </template>

        <template #cell-name="{ value }">
            <div class="lookup-main-name">{{ value }}</div>
        </template>

        <template #cell-program_code="{ value }">
            <span class="badge badge-secondary" style="font-size:10px;">{{ value || '-' }}</span>
        </template>

        <template #cell-description="{ value }">
            <span class="text-muted" style="font-size:12px;">{{ value || '-' }}</span>
        </template>

        <template #cell-_actions="{ row }">
            <div class="table-action-cell" style="justify-content:center;">
                <a :href="route('inventory.master.daftar-nilai.values', { uuid: row.uuid })"
                    class="btn btn-outline btn-icon" title="Lihat Nilai" data-testid="btn-view-lookup-values" @click.stop>
                    <i class="fa-solid fa-eye"></i>
                </a>
                <button v-show="vm.canManageLookups" type="button" class="btn btn-outline btn-icon" @click.stop="vm.openEdit(row)" title="Edit Lookup" data-testid="btn-edit-lookup">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button v-show="vm.canManageLookups" type="button" class="btn btn-outline btn-icon btn-danger-soft"
                    @click.stop="vm.deleteLookup(row)" title="Hapus Lookup" data-testid="btn-delete-lookup">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </template>
    </DataTable>
</template>

<style scoped>
.lookup-main-name {
    font-weight: 700;
    color: var(--text);
    font-size: 13.5px;
}

.text-muted {
    color: var(--muted);
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

.badge-secondary {
    background: #f1f5f9;
    color: #475569;
}

.btn-danger-soft {
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.1);
}

.btn-danger-soft:hover {
    background: #fee2e2;
    border-color: #ef4444;
    color: #b91c1c;
}
</style>
