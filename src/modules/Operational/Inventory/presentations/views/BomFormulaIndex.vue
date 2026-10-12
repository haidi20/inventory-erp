<script setup lang="ts">import { BOM_FORMULA_COLUMNS } from '@modules/Operational/Inventory/presentations/viewmodels/bom_formula.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';

const props = defineProps<{ items?: any[] }>();
const columns = BOM_FORMULA_COLUMNS;
</script>

<template>
    <Head title="Formula (BOM) - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/inventory" class="breadcrumb-link">Inventory</a>
                <span>/</span>
                <span style="color:var(--text);">Formula (BOM)</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Formula (BOM)</div>
            <div class="page-sub">Kelola bill of materials / formula produksi.</div>
        </div>
    </div>

    <DataTable
        store-id="bomformulaindex-datatable"
        url=""
        :columns="columns"
        :items="props.items"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
