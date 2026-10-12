<script setup lang="ts">import { BARCODE_PRINT_COLUMNS } from '@modules/Operational/Inventory/presentations/viewmodels/barcode_print.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';

const props = defineProps<{ items?: any[] }>();
const columns = BARCODE_PRINT_COLUMNS;
</script>

<template>
    <Head title="Cetak Barcode - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/inventory" class="breadcrumb-link">Inventory</a>
                <span>/</span>
                <span style="color:var(--text);">Cetak Barcode</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Cetak Barcode</div>
            <div class="page-sub">Cetak label barcode produk.</div>
        </div>
    </div>

    <DataTable
        store-id="barcodeprintindex-datatable"
        url=""
        :columns="columns"
        :items="props.items"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
