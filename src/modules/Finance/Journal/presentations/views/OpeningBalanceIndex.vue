<script setup lang="ts">import { OPENING_BALANCE_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/opening_balance.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = OPENING_BALANCE_COLUMNS;
</script>

<template>
    <Head title="Saldo Awal - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Saldo Awal</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Saldo Awal</div>
            <div class="page-sub">Set saldo awal akun per periode.</div>
        </div>
    </div>

    <DataTable
        store-id="openingbalance-datatable"
        url="/accounting/opening-balances"
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
