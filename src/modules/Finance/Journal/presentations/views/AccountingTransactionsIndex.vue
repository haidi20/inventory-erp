<script setup lang="ts">import { ACCOUNTING_TRANSACTIONS_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/accounting_transactions.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = ACCOUNTING_TRANSACTIONS_COLUMNS;
</script>

<template>
    <Head title="Transaksi Jurnal - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Transaksi Jurnal</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Transaksi Jurnal</div>
            <div class="page-sub">Daftar transaksi jurnal manual.</div>
        </div>
    </div>

    <DataTable
        store-id="accountingtransactions-datatable"
        url="/journals"
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
