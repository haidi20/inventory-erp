<script setup lang="ts">import { SALES_TRANSACTION_COLUMNS } from '@modules/Operational/Sales/presentations/viewmodels/sales_transaction.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import type { SalesTransactionFilters } from '@modules/Operational/Sales/presentations/viewmodels/sales_transaction.state';

const props = defineProps<{ filters?: SalesTransactionFilters }>();

const columns = SALES_TRANSACTION_COLUMNS;
</script>

<template>
    <Head title="Transaksi Penjualan - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <span style="color:var(--text);">Transaksi Penjualan</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Transaksi Penjualan</div>
            <div class="page-sub">Daftar transaksi penjualan.</div>
        </div>
    </div>

    <DataTable
        store-id="sales-transaction-vm-datatable"
        url=""
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
        search-test-id="input-search-sales-transaction-vm"
    />
</template>
