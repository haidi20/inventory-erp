<script setup lang="ts">import { JOURNALS_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/journals.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = JOURNALS_COLUMNS;
</script>

<template>
    <Head title="Jurnal Umum - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Jurnal Umum</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Jurnal Umum</div>
            <div class="page-sub">Buku jurnal umum semua transaksi.</div>
        </div>
    </div>

    <DataTable
        store-id="journals-datatable"
        url="/journals"
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
