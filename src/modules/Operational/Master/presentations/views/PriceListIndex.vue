<script setup lang="ts">import { PRICE_LIST_COLUMNS } from '@modules/Operational/Master/presentations/viewmodels/price_list.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import { usePriceListVM } from '@modules/Operational/Master/presentations/viewmodels/price_list.vm';
import type { PriceListFilters } from '@modules/Operational/Master/presentations/viewmodels/price_list.state';

const props = defineProps<{ filters?: PriceListFilters }>();
const vm = usePriceListVM();

onMounted(() => vm.initFilters(props.filters ?? {}));

const columns = PRICE_LIST_COLUMNS;
</script>

<template>
    <Head title="Daftar Harga - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <span style="color:var(--text);">Daftar Harga</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Daftar Harga</div>
            <div class="page-sub">Lihat daftar harga jual dan beli produk yang aktif.</div>
        </div>
    </div>

    <DataTable
        store-id="price-list-datatable"
        url="/products"
        :columns="columns"
        :default-per-page="10"
        default-sort-by="code"
        search-placeholder="Cari produk..."
        search-test-id="input-search-price-list"
    >
        <template #cell-code="{ value }">
            <span class="badge badge-secondary">{{ value || '-' }}</span>
        </template>
        <template #cell-name="{ value }">
            <span style="font-weight:700;">{{ value }}</span>
        </template>
        <template #cell-unit_name="{ value }">
            <span style="color:var(--muted);">{{ value || '-' }}</span>
        </template>
        <template #cell-price_sell="{ value }">
            <span>{{ value ? Number(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }) : '-' }}</span>
        </template>
        <template #cell-price_buy="{ value }">
            <span style="color:var(--muted);">{{ value ? Number(value).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }) : '-' }}</span>
        </template>
        <template #cell-stock="{ value }">
            <span :style="{ color: Number(value) <= 0 ? '#ef4444' : 'var(--text)', fontWeight: '700' }">
                {{ value ?? 0 }}
            </span>
        </template>
        <template #cell-is_active="{ value }">
            <span :class="value ? 'badge badge-success' : 'badge badge-danger'">
                {{ value ? 'Aktif' : 'Nonaktif' }}
            </span>
        </template>
    </DataTable>
</template>

