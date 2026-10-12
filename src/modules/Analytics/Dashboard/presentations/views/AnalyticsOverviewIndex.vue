<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Analitik Ikhtisar - Inventory" />
    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/analytics" class="breadcrumb-link">Analitik</a>
                <span>/</span>
                <span style="color:var(--text);">Analitik Ikhtisar</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Analitik Ikhtisar</div>
            <div class="page-sub">Ringkasan analitik klinik.</div>
        </div>
    </div>
    <div class="empty-state" style="padding:80px;text-align:center;background:#fff;border:1px solid var(--border);border-radius:16px;">
        <i class="fa-solid fa-chart-mixed" style="font-size:40px;color:var(--muted);margin-bottom:16px;display:block;opacity:0.4;"></i>
        <div style="font-size:15px;font-weight:700;color:var(--muted);">Grafik & data akan tersedia segera</div>
    </div>
</template>
