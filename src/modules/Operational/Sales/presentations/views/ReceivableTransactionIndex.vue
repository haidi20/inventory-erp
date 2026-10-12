<script setup lang="ts">import { RECEIVABLE_TRANSACTION_COLUMNS } from '@modules/Operational/Sales/presentations/viewmodels/receivable_transaction.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import type { ReceivableTransactionFilters } from '@modules/Operational/Sales/presentations/viewmodels/receivable_transaction.state';

const props = defineProps<{ filters?: ReceivableTransactionFilters }>();

const columns = RECEIVABLE_TRANSACTION_COLUMNS;
</script>

<template>
    <Head title="Piutang - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <span style="color:var(--text);">Piutang</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Piutang</div>
            <div class="page-sub">Daftar piutang dari transaksi penjualan.</div>
        </div>
    </div>

    <DataTable
        store-id="receivable-transaction-vm-datatable"
        url=""
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
        search-test-id="input-search-receivable-transaction-vm"
    />
</template>

