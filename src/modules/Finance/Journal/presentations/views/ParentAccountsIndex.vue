<script setup lang="ts">import { PARENT_ACCOUNTS_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/parent_accounts.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = PARENT_ACCOUNTS_COLUMNS;
</script>

<template>
    <Head title="Akun Induk - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Akun Induk</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Akun Induk</div>
            <div class="page-sub">Kelola akun induk chart of accounts.</div>
        </div>
    </div>

    <DataTable
        store-id="parentaccounts-datatable"
        url="/accounts"
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
