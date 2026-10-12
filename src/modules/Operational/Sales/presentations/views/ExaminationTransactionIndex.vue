<script setup lang="ts">import { EXAMINATION_TRANSACTION_COLUMNS } from '@modules/Operational/Sales/presentations/viewmodels/examination_transaction.state';

import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
import type { ExaminationTransactionFilters } from '@modules/Operational/Sales/presentations/viewmodels/examination_transaction.state';

const props = defineProps<{ filters?: ExaminationTransactionFilters }>();

const columns = EXAMINATION_TRANSACTION_COLUMNS;
</script>

<template>
    <Head title="Transaksi Pemeriksaan - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <span style="color:var(--text);">Transaksi Pemeriksaan</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Transaksi Pemeriksaan</div>
            <div class="page-sub">Daftar transaksi pemeriksaan pasien.</div>
        </div>
    </div>

    <DataTable
        store-id="examination-transaction-vm-datatable"
        url=""
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
        search-test-id="input-search-examination-transaction-vm"
    />
</template>

