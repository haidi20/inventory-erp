<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FINANCIAL_STATEMENT_MENU_INDEX_MENUS } from '@modules/Finance/Ledger/presentations/viewmodels/financial_statement_menu_index.state';

const menus = FINANCIAL_STATEMENT_MENU_INDEX_MENUS;
</script>

<template>
    <Head title="Menu Laporan Keuangan - Inventory" />

    <div class="page-header" style="margin-bottom:32px;">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <span style="color:var(--text);">Laporan Keuangan</span>
            </div>
            <div class="page-title" style="margin-bottom:4px;">Menu Laporan Keuangan</div>
            <div class="page-sub">Pilih laporan keuangan yang ingin dilihat.</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;">
        <Link
            v-for="m in menus"
            :key="m.url"
            :href="m.url"
            class="menu-card"
        >
            <i :class="`fa-solid ${m.icon}`" style="font-size:28px;color:var(--primary);margin-bottom:12px;"></i>
            <div style="font-weight:700;font-size:15px;">{{ m.label }}</div>
        </Link>
    </div>
</template>

<style scoped>
.menu-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px 20px;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    text-decoration: none;
    color: var(--text);
    transition: all 0.2s ease;
    text-align: center;
}
.menu-card:hover {
    border-color: var(--primary);
    box-shadow: 0 4px 20px rgba(59,130,246,0.12);
    transform: translateY(-2px);
}
</style>

