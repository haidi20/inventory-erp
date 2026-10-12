<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { SETTINGS_INDEX_MENUS } from '@modules/Core/Platform/presentations/viewmodels/settings_index.state';

const menus = SETTINGS_INDEX_MENUS;
</script>

<template>
    <Head title="Pengaturan - Inventory" />
    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <span style="color:var(--text);">Pengaturan</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Pengaturan</div>
            <div class="page-sub">Konfigurasi sistem klinik.</div>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px;">
        <Link v-for="m in menus" :key="m.url" :href="m.url" class="menu-card">
            <i :class="`fa-solid ${m.icon}`" style="font-size:28px;color:var(--primary);margin-bottom:12px;"></i>
            <div style="font-weight:700;">{{ m.label }}</div>
        </Link>
    </div>
</template>

<style scoped>
.menu-card { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:32px 20px; background:#fff; border:1px solid var(--border); border-radius:16px; text-decoration:none; color:var(--text); transition:all 0.2s ease; text-align:center; }
.menu-card:hover { border-color:var(--primary); box-shadow:0 4px 20px rgba(59,130,246,0.12); transform:translateY(-2px); }
</style>

