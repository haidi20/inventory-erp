<script setup lang="ts">import { GENERAL_LEDGER_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/general_ledger.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = GENERAL_LEDGER_COLUMNS;
</script>

<template>
    <Head title="Buku Besar - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Buku Besar</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Buku Besar</div>
            <div class="page-sub">Rincian transaksi per akun.</div>
        </div>
    </div>

    <DataTable
        store-id="generalledger-datatable"
        url="/general-ledger"
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
