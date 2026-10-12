<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    FARMACY_ACCOUNTING_MENUS,
    FARMACY_MASTER_MENUS,
    FARMACY_SETTING_MENUS,
} from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_management.state';

// Ikon menggunakan FontAwesome 6
const masterMenus = FARMACY_MASTER_MENUS;
const accountingMenus = FARMACY_ACCOUNTING_MENUS;
const settingMenus = FARMACY_SETTING_MENUS;

</script>

<template>
    <Head title="Manajemen & Pengaturan - Inventory" />

    <div class="page-header">
        <div>
            <div class="breadcrumb">
                <Link href="/" class="breadcrumb-link">
                    <i class="fa-solid fa-house"></i>
                </Link>
                <span>/</span> Operationals <span>/</span> <Link href="/farmasi/dashboard" class="breadcrumb-link">Farmacy</Link> <span>/</span> Manajemen
            </div>
            <div class="page-title">Manajemen & Pengaturan</div>
            <div class="page-sub">Pusat kendali data master, akuntansi, dan konfigurasi sistem farmasi.</div>
        </div>
    </div>

    <div class="management-container">
        <!-- Master Data Section -->
        <section class="menu-section">
            <div class="section-header">
                <i class="fa-solid fa-database section-icon"></i>
                <div>
                    <h2 class="section-title">MASTER DATA</h2>
                    <p class="section-desc">Kelola data master yang digunakan di seluruh sistem</p>
                </div>
            </div>
            <div class="menu-grid">
                <Link v-for="menu in masterMenus" :key="menu.title" :href="menu.href" class="menu-card">
                    <div class="menu-icon-wrap" :style="{ backgroundColor: menu.color + '15', color: menu.color }">
                        <i class="fa-solid" :class="menu.icon"></i>
                    </div>
                    <div class="menu-content">
                        <h3 class="menu-title">{{ menu.title }}</h3>
                        <p class="menu-desc">{{ menu.desc }}</p>
                    </div>
                    <i class="fa-solid fa-chevron-right menu-arrow"></i>
                </Link>
            </div>
        </section>

        <div class="grid-two-cols">
            <!-- Accounting Section -->
            <section class="menu-section">
                <div class="section-header">
                    <i class="fa-solid fa-calculator section-icon"></i>
                    <div>
                        <h2 class="section-title">AKUNTANSI</h2>
                        <p class="section-desc">Kelola data akuntansi dan pembukuan</p>
                    </div>
                </div>
                <div class="menu-grid vertical">
                    <Link v-for="menu in accountingMenus" :key="menu.title" :href="menu.href" class="menu-card">
                        <div class="menu-icon-wrap" :style="{ backgroundColor: menu.color + '15', color: menu.color }">
                            <i class="fa-solid" :class="menu.icon"></i>
                        </div>
                        <div class="menu-content">
                            <h3 class="menu-title">{{ menu.title }}</h3>
                            <p class="menu-desc">{{ menu.desc }}</p>
                        </div>
                        <i class="fa-solid fa-chevron-right menu-arrow"></i>
                    </Link>
                </div>
            </section>

            <!-- Settings Section -->
            <section class="menu-section">
                <div class="section-header">
                    <i class="fa-solid fa-gears section-icon"></i>
                    <div>
                        <h2 class="section-title">PENGATURAN</h2>
                        <p class="section-desc">Konfigurasi khusus modul farmasi</p>
                    </div>
                </div>
                <div class="menu-grid vertical">
                    <Link v-for="menu in settingMenus" :key="menu.title" :href="menu.href" class="menu-card">
                        <div class="menu-icon-wrap" :style="{ backgroundColor: menu.color + '15', color: menu.color }">
                            <i class="fa-solid" :class="menu.icon"></i>
                        </div>
                        <div class="menu-content">
                            <h3 class="menu-title">{{ menu.title }}</h3>
                            <p class="menu-desc">{{ menu.desc }}</p>
                        </div>
                        <i class="fa-solid fa-chevron-right menu-arrow"></i>
                    </Link>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.page-header {
    margin-bottom: 32px;
}

.management-container {
    display: flex;
    flex-direction: column;
    gap: 40px;
    padding-bottom: 40px;
}

.menu-section {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 0 4px;
}

.section-icon {
    font-size: 24px;
    color: var(--primary);
    opacity: 0.8;
}

.section-title {
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 1px;
    color: var(--text);
    margin: 0;
}

.section-desc {
    font-size: 12px;
    color: var(--muted);
    margin: 2px 0 0 0;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}

.menu-grid.vertical {
    grid-template-columns: 1fr;
}

.grid-two-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

.menu-card {
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s ease;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.menu-card:hover {
    border-color: var(--primary);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    transform: translateY(-2px);
}

.menu-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.menu-card:hover .menu-icon-wrap {
    transform: scale(1.1) rotate(5deg);
}

.menu-content {
    flex: 1;
}

.menu-title {
    font-size: 15px;
    font-weight: 800;
    color: var(--text);
    margin: 0;
}

.menu-desc {
    font-size: 12px;
    color: var(--muted);
    margin: 4px 0 0 0;
    line-height: 1.4;
}

.menu-arrow {
    font-size: 12px;
    color: var(--muted);
    opacity: 0;
    transition: all 0.2s ease;
    transform: translateX(-10px);
}

.menu-card:hover .menu-arrow {
    opacity: 1;
    transform: translateX(0);
    color: var(--primary);
}

@media (max-width: 1024px) {
    .grid-two-cols {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .menu-grid {
        grid-template-columns: 1fr;
    }
}
</style>

