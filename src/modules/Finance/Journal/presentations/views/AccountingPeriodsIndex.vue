<script setup lang="ts">import { ACCOUNTING_PERIODS_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/accounting_periods.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = ACCOUNTING_PERIODS_COLUMNS;
</script>

<template>
    <Head title="Periode Akuntansi - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Periode Akuntansi</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Periode Akuntansi</div>
            <div class="page-sub">Kelola periode buku akuntansi.</div>
        </div>
    </div>

    <DataTable
        store-id="accountingperiods-datatable"
        url="/api/v1/accounting/periods"
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
