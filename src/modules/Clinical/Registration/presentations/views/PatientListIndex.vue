<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Data Pasien - Inventory" />
    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="page-title" style="margin-bottom:4px;">Data Pasien</div>
            <div class="page-sub">Daftar data pasien terdaftar.</div>
        </div>
    </div>
    <div class="empty-state" style="padding:80px;text-align:center;background:#fff;border:1px solid var(--border);border-radius:16px;">
        <i class="fa-solid fa-hospital" style="font-size:40px;color:var(--muted);margin-bottom:16px;display:block;opacity:0.4;"></i>
        <div style="font-size:15px;font-weight:700;color:var(--muted);">Halaman dalam pengembangan</div>
    </div>
</template>
