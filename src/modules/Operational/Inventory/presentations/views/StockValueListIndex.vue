<script setup lang="ts">import { STOCK_VALUE_LIST_COLUMNS } from '@modules/Operational/Inventory/presentations/viewmodels/stock_value_list.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = STOCK_VALUE_LIST_COLUMNS;
</script>

<template>
    <Head title="Nilai Stok - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/inventory" class="breadcrumb-link">Inventory</a>
                <span>/</span>
                <span style="color:var(--text);">Nilai Stok</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Nilai Stok</div>
            <div class="page-sub">Daftar nilai stok produk per gudang.</div>
        </div>
    </div>

    <DataTable
        store-id="stockvaluelistindex-datatable"
        url=""
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
