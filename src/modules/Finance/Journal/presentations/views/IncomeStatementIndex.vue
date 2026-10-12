<script setup lang="ts">import { INCOME_STATEMENT_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/income_statement.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = INCOME_STATEMENT_COLUMNS;
</script>

<template>
    <Head title="Laba Rugi - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Laba Rugi</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Laba Rugi</div>
            <div class="page-sub">Laporan laba rugi periode berjalan.</div>
        </div>
    </div>

    <DataTable
        store-id="incomestatement-datatable"
        url=""
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
