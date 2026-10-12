<script setup lang="ts">import { CASH_BANK_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/cash_bank.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = CASH_BANK_COLUMNS;
</script>

<template>
    <Head title="Kas & Bank - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Kas & Bank</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Kas & Bank</div>
            <div class="page-sub">Kelola transaksi kas dan bank.</div>
        </div>
    </div>

    <DataTable
        store-id="cashbank-datatable"
        url="/accounting/cash-flow"
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
