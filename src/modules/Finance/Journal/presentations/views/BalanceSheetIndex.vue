<script setup lang="ts">import { BALANCE_SHEET_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/balance_sheet.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = BALANCE_SHEET_COLUMNS;
</script>

<template>
    <Head title="Neraca - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Neraca</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Neraca</div>
            <div class="page-sub">Laporan posisi keuangan.</div>
        </div>
    </div>

    <DataTable
        store-id="balancesheet-datatable"
        url=""
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
