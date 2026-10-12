<script setup lang="ts">
import { onMounted } from 'vue';
import { useReportIndexVM } from '@modules/Analytics/Reports/presentations/viewmodels/report_index.vm';

const vm = useReportIndexVM();

onMounted(() => {
    vm.initFromQuery();
});
</script>

<template>
    <div class="lap-page">

        <!-- Header -->
        <div class="page-header">
            <div>
                <div class="breadcrumb">
                    <i class="fa-solid fa-house" style="font-size:11px;" />
                    <span>/</span> Laporan
                </div>
                <div class="page-title">Menu Laporan</div>
                <div class="page-sub">Kelola dan akses seluruh laporan keuangan, penjualan, pembelian, dan klinis.</div>
            </div>
        </div>

        <!-- Tab Nav -->
        <div class="lap-tabs">
            <button
                v-for="tab in vm.tabs"
                :key="tab.key"
                class="lap-tab-btn"
                :class="{ active: vm.activeTab === tab.key }"
                @click="vm.setActiveTab(tab.key)"
            >
                <i :class="['fa-solid', tab.icon]" />
                {{ tab.label }}
            </button>
        </div>

        <!-- Menu Grid -->
        <div class="lap-menu-grid">
            <a
                v-for="item in vm.activeMenuItems"
                :key="item.key"
                :href="item.url"
                class="lap-menu-card"
            >
                <div class="lap-menu-icon">
                    <i :class="['fa-solid', item.icon]" />
                </div>
                <div class="lap-menu-info">
                    <div class="lap-menu-label">{{ item.label }}</div>
                    <div class="lap-menu-caption">{{ item.caption }}</div>
                </div>
            </a>
        </div>

    </div>
</template>

<style scoped>
.lap-page {
    min-height: 100%;
}

.lap-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 2px solid var(--border);
    margin-bottom: 24px;
}

.lap-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    background: transparent;
    color: var(--muted);
    font-size: 13.5px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: color .2s, border-color .2s;
}

.lap-tab-btn:hover {
    color: var(--primary);
    border-bottom-color: rgba(99, 102, 241, .3);
}

.lap-tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.lap-menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
}

.lap-menu-card {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-soft);
    text-decoration: none;
    transition: transform .2s, box-shadow .2s, border-color .2s;
}

.lap-menu-card:hover {
    transform: translateY(-2px);
    border-color: rgba(99, 102, 241, .25);
    box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
}

.lap-menu-icon {
    width: 44px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--primary) 0%, #818cf8 100%);
    color: #fff;
    font-size: 1.05rem;
    flex-shrink: 0;
}

.lap-menu-label {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 3px;
}

.lap-menu-caption {
    font-size: 12px;
    color: var(--muted);
    line-height: 1.4;
}
</style>
