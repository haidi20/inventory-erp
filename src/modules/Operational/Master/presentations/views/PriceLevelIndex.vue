<script setup lang="ts">import { PRICE_LEVEL_COLUMNS } from '@modules/Operational/Master/presentations/viewmodels/price_level.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import { usePriceLevelVM } from '@modules/Operational/Master/presentations/viewmodels/price_level.vm';
import PriceLevelForm from '@modules/Operational/Master/presentations/views/Partials/PriceLevelForm.vue';
import type { PriceLevelFilters } from '@modules/Operational/Master/presentations/viewmodels/price_level.state';

const props = defineProps<{ filters?: PriceLevelFilters }>();
const vm = usePriceLevelVM();

onMounted(() => vm.initFilters(props.filters ?? {}));

const columns = PRICE_LEVEL_COLUMNS;
</script>

<template>
    <Head title="Level Harga - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/master" class="breadcrumb-link">Master</a>
                <span>/</span>
                <span style="color:var(--text);">Level Harga</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Level Harga</div>
            <div class="page-sub">Kelola level harga yang dipakai untuk produk (Retail, Grosir, dll).</div>
        </div>
        <div style="display:flex;gap:12px;align-items:center;">
            <button
                v-if="vm.canManage"
                type="button"
                class="btn btn-primary"
                data-testid="btn-create-price-level"
                @click="vm.openCreate()"
            >
                <i class="fa-solid fa-plus-circle"></i> Tambah Level Harga
            </button>
        </div>
    </div>

    <DataTable
        store-id="price-level-datatable"
        url="/masters/price-levels"
        :columns="columns"
        :default-per-page="10"
        default-sort-by="code"
        search-placeholder="Cari level harga..."
        search-test-id="input-search-price-level"
    >
        <template #cell-code="{ value }">
            <span class="badge badge-secondary">{{ value }}</span>
        </template>
        <template #cell-name="{ value }">
            <span style="font-weight:700;">{{ value }}</span>
        </template>
        <template #cell-description="{ value }">
            <span style="color:var(--muted);">{{ value || '-' }}</span>
        </template>
        <template #cell-is_active="{ value }">
            <span :class="value ? 'badge badge-success' : 'badge badge-danger'">
                {{ value ? 'Aktif' : 'Nonaktif' }}
            </span>
        </template>
        <template v-if="vm.canManage" #cell-actions="{ row }">
            <div style="display:flex;gap:6px;justify-content:center;">
                <button class="btn btn-sm btn-primary" title="Edit" data-testid="btn-edit-price-level" @click="vm.openEdit(row)">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="btn btn-sm btn-danger" title="Hapus" data-testid="btn-delete-price-level" @click="vm.deleteItem(row)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </template>
    </DataTable>

    <PriceLevelForm v-if="vm.showForm" :vm="vm" />
</template>

