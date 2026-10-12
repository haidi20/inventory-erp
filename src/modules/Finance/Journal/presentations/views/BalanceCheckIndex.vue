<script setup lang="ts">import { BALANCE_CHECK_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/balance_check.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = BALANCE_CHECK_COLUMNS;
</script>

<template>
    <Head title="Cek Balance - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Cek Balance</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Cek Balance</div>
            <div class="page-sub">Validasi keseimbangan debit/kredit.</div>
        </div>
    </div>

    <DataTable
        store-id="balancecheck-datatable"
        url=""
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
