<script setup lang="ts">import { ANNOUNCEMENTS_COLUMNS } from '@modules/Core/Platform/presentations/viewmodels/announcements.state';

import { Head } from '@inertiajs/vue3';
import DataTable from '@modules/Core/shared/Components/common/DataTable.vue';
const columns = ANNOUNCEMENTS_COLUMNS;
</script>

<template>
    <Head title="Pengumuman - Inventory" />
    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <span style="color:var(--text);">Pengumuman</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Pengumuman</div>
            <div class="page-sub">Kelola pengumuman/himbauan yang tampil di sistem.</div>
        </div>
    </div>
    <DataTable store-id="announcements-datatable" url="" :columns="columns" :default-per-page="10" search-placeholder="Cari pengumuman..." />
</template>

