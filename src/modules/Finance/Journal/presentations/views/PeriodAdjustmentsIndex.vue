<script setup lang="ts">import { PERIOD_ADJUSTMENTS_COLUMNS } from '@modules/Finance/Journal/presentations/viewmodels/period_adjustments.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = PERIOD_ADJUSTMENTS_COLUMNS;
</script>

<template>
    <Head title="Penyesuaian Periode - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/accounting" class="breadcrumb-link">Akuntansi</a>
                <span>/</span>
                <span style="color:var(--text);">Penyesuaian Periode</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Penyesuaian Periode</div>
            <div class="page-sub">Jurnal penyesuaian akhir periode.</div>
        </div>
    </div>

    <DataTable
        store-id="periodadjustments-datatable"
        url=""
        :columns="columns"
        :default-per-page="10"
        search-placeholder="Cari..."
    />
</template>
