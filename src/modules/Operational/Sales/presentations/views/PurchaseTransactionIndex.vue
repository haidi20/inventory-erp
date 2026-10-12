<script setup lang="ts">import { PURCHASE_TRANSACTION_COLUMNS } from '@modules/Operational/Sales/presentations/viewmodels/purchase_transaction.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import type { PurchaseTransactionFilters } from '@modules/Operational/Sales/presentations/viewmodels/purchase_transaction.state';

const props = defineProps<{ filters?: PurchaseTransactionFilters }>();

const columns = PURCHASE_TRANSACTION_COLUMNS;
</script>

<template>
    <Head title="Transaksi Pembelian - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <span style="color:var(--text);">Transaksi Pembelian</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Transaksi Pembelian</div>
            <div class="page-sub">Daftar transaksi pembelian.</div>
        </div>
    </div>

    <DataTable
        store-id="purchase-transaction-vm-datatable"
        url=""
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
        search-test-id="input-search-purchase-transaction-vm"
    />
</template>

