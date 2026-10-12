<script setup lang="ts">import { PAYABLE_TRANSACTION_COLUMNS } from '@modules/Operational/Sales/presentations/viewmodels/payable_transaction.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import type { PayableTransactionFilters } from '@modules/Operational/Sales/presentations/viewmodels/payable_transaction.state';

const props = defineProps<{ filters?: PayableTransactionFilters }>();

const columns = PAYABLE_TRANSACTION_COLUMNS;
</script>

<template>
    <Head title="Hutang - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <span style="color:var(--text);">Hutang</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Hutang</div>
            <div class="page-sub">Daftar hutang dari transaksi pembelian.</div>
        </div>
    </div>

    <DataTable
        store-id="payable-transaction-vm-datatable"
        url=""
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
        search-test-id="input-search-payable-transaction-vm"
    />
</template>

