<script setup lang="ts">import { SUB_ACCOUNTS_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/sub_accounts.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = SUB_ACCOUNTS_COLUMNS;
</script>

<template>
    <Head title="Sub Akun - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Sub Akun</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Sub Akun</div>
            <div class="page-sub">Kelola sub akun dari akun induk.</div>
        </div>
    </div>

    <DataTable
        store-id="subaccounts-datatable"
        url="/accounts"
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
