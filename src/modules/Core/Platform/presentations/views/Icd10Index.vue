<script setup lang="ts">import { ICD10_COLUMNS } from '@modules/Core/Platform/presentations/viewmodels/icd10.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = ICD10_COLUMNS;
</script>

<template>
    <Head title="ICD-10 - Inventory" />
    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/settings" class="breadcrumb-link">Pengaturan</a>
                <span>/</span>
                <span style="color:var(--text);">ICD-10</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Kode ICD-10</div>
            <div class="page-sub">Kelola kode klasifikasi penyakit internasional.</div>
        </div>
    </div>
    <DataTable store-id="icd10-datatable" url="/api/v1/icd10" :columns="columns" :default-per-page="10" search-placeholder="Cari kode ICD-10..." />
</template>

