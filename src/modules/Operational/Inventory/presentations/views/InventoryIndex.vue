<script setup lang="ts">
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useInventoryIndexVM } from '@modules/Operational/Inventory/presentations/viewmodels/inventory_index.vm';

const vm = useInventoryIndexVM();
onMounted(() => {
    vm.initFromQuery();
});
</script>

<template>
    <Head title="Inventory" />
    <div class="inv-page">

        <!-- Header -->
        <div class="page-header">
            <div>
                <div class="breadcrumb">
                    <i class="fa-solid fa-house" style="font-size:11px;" />
                    <span>/</span> Inventory
                </div>
                <div class="page-title">Menu Inventory</div>
                <div class="page-sub">Kelola stok, pembelian, dan penjualan barang dalam sistem.</div>
            </div>
        </div>

        <!-- Tab Nav + Search -->
        <div class="inv-tabs-bar">
            <div class="inv-tabs">
                <button
                    v-for="tab in vm.tabs"
                    :key="tab.key"
                    class="inv-tab-btn"
                    :class="{ active: vm.activeTab === tab.key }"
                    @click="vm.setActiveTab(tab.key); vm.expandAll()"
                >
                    <i :class="['fa-solid', tab.icon]" />
                    {{ tab.label }}
                </button>
            </div>
            <div class="inv-search">
                <i class="fa-solid fa-magnifying-glass inv-search-icon" />
                <input
                    type="text"
                    class="inv-search-input"
                    placeholder="Cari semua menu..."
                    :value="vm.searchQuery"
                    @input="vm.setSearch(($event.target as HTMLInputElement).value)"
                />
                <button v-if="vm.searchQuery" class="inv-search-clear" @click="vm.setSearch('')">
                    <i class="fa-solid fa-xmark" />
                </button>
            </div>
        </div>

        <!-- Search Results (lintas tab) -->
        <template v-if="vm.isSearching">
            <div v-if="vm.searchResults.length === 0" class="inv-empty">
                <i class="fa-solid fa-magnifying-glass" />
                <span>Tidak ada menu yang cocok dengan "<strong>{{ vm.searchQuery }}</strong>"</span>
            </div>
            <template v-else>
                <div class="inv-search-info">
                    Ditemukan <strong>{{ vm.searchResults.length }}</strong> menu untuk "<strong>{{ vm.searchQuery }}</strong>"
                </div>
                <div class="inv-menu-grid">
                    <a
                        v-for="result in vm.searchResults"
                        :key="`${result.tabKey}-${result.item.key}`"
                        :href="result.item.url !== '#' ? result.item.url : undefined"
                        class="inv-menu-card"
                    >
                        <div class="inv-menu-icon">
                            <i :class="['fa-solid', result.item.icon]" />
                        </div>
                        <div class="inv-menu-info">
                            <div class="inv-menu-label">{{ result.item.label }}</div>
                            <div class="inv-menu-caption">{{ result.item.caption }}</div>
                            <div class="inv-menu-tab-badge">{{ result.tabLabel }}</div>
                        </div>
                    </a>
                </div>
            </template>
        </template>

        <!-- Normal Menu Grid (per tab) -->
        <template v-else>
            <div class="inv-menu-grid">
                <template v-for="item in vm.activeMenuItems" :key="item.key">

                    <!-- Card dengan submenu -->
                    <div v-if="item.items?.length" class="inv-menu-card inv-menu-card--expandable" :class="{ expanded: vm.expandedKeys.has(item.key) }" @click="vm.toggleExpand(item.key)">
                        <div class="inv-menu-icon">
                            <i :class="['fa-solid', item.icon]" />
                        </div>
                        <div class="inv-menu-info">
                            <div class="inv-menu-label">{{ item.label }}</div>
                            <div class="inv-menu-caption">{{ item.caption }}</div>
                        </div>
                        <i class="fa-solid fa-chevron-down inv-menu-chevron" :class="{ rotated: vm.expandedKeys.has(item.key) }" />
                    </div>

                    <!-- Submenu dropdown -->
                    <div v-if="item.items?.length && vm.expandedKeys.has(item.key)" class="inv-submenu-grid">
                        <a
                            v-for="sub in item.items"
                            :key="sub.key"
                            :href="sub.url"
                            class="inv-submenu-card"
                        >
                            <div class="inv-submenu-icon">
                                <i :class="['fa-solid', sub.icon]" />
                            </div>
                            <div class="inv-menu-info">
                                <div class="inv-menu-label">{{ sub.label }}</div>
                                <div class="inv-menu-caption">{{ sub.caption }}</div>
                            </div>
                        </a>
                    </div>

                    <!-- Card biasa tanpa submenu -->
                    <a
                        v-else-if="!item.items?.length"
                        :href="item.url"
                        class="inv-menu-card"
                    >
                        <div class="inv-menu-icon">
                            <i :class="['fa-solid', item.icon]" />
                        </div>
                        <div class="inv-menu-info">
                            <div class="inv-menu-label">{{ item.label }}</div>
                            <div class="inv-menu-caption">{{ item.caption }}</div>
                        </div>
                    </a>

                </template>
            </div>
        </template>

    </div>
</template>

<style scoped>
.inv-page {
    min-height: 100%;
}

.inv-tabs-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid var(--border);
    margin-bottom: 24px;
    gap: 16px;
}

.inv-tabs {
    display: flex;
    gap: 4px;
    flex: 1;
}

.inv-tab-btn {
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

.inv-tab-btn:hover {
    color: var(--primary);
    border-bottom-color: rgba(99, 102, 241, .3);
}

.inv-tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.inv-search {
    display: flex;
    align-items: center;
    position: relative;
    margin-bottom: 2px;
}

.inv-search-icon {
    position: absolute;
    left: 10px;
    color: var(--muted);
    font-size: 13px;
    pointer-events: none;
}

.inv-search-input {
    padding: 7px 32px 7px 30px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--card);
    color: var(--text);
    font-size: 13px;
    font-family: inherit;
    width: 220px;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
}

.inv-search-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
}

.inv-search-input::placeholder {
    color: var(--muted);
}

.inv-search-clear {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--muted);
    font-size: 12px;
    padding: 2px;
    display: flex;
    align-items: center;
    transition: color .15s;
}

.inv-search-clear:hover {
    color: var(--text);
}

.inv-search-info {
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 16px;
}

.inv-menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
}

.inv-menu-card {
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

.inv-menu-card:hover {
    transform: translateY(-2px);
    border-color: rgba(99, 102, 241, .25);
    box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
}

.inv-menu-card--expandable {
    cursor: pointer;
    user-select: none;
}

.inv-menu-card--expandable.expanded {
    border-color: var(--primary);
    grid-column: 1 / -1;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}

.inv-menu-chevron {
    margin-left: auto;
    font-size: 12px;
    color: var(--muted);
    transition: transform .2s;
    flex-shrink: 0;
    align-self: center;
}

.inv-menu-chevron.rotated {
    transform: rotate(180deg);
    color: var(--primary);
}

.inv-submenu-grid {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    padding: 16px;
    background: var(--card);
    border: 1px solid var(--primary);
    border-top: none;
    border-radius: 0 0 var(--radius) var(--radius);
    margin-top: -16px;
}

.inv-submenu-card {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: calc(var(--radius) - 2px);
    text-decoration: none;
    transition: transform .15s, border-color .15s, box-shadow .15s;
}

.inv-submenu-card:hover {
    transform: translateY(-1px);
    border-color: rgba(99, 102, 241, .3);
    box-shadow: 0 4px 12px rgba(15, 23, 42, .06);
}

.inv-menu-icon {
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

.inv-submenu-icon {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary) 0%, #818cf8 100%);
    color: #fff;
    font-size: .9rem;
    flex-shrink: 0;
}

.inv-menu-label {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 3px;
}

.inv-menu-caption {
    font-size: 12px;
    color: var(--muted);
    line-height: 1.4;
}

.inv-menu-tab-badge {
    margin-top: 6px;
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    color: var(--primary);
    background: rgba(99, 102, 241, .1);
    padding: 2px 8px;
    border-radius: 20px;
}

.inv-empty {
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 32px;
    color: var(--muted);
    font-size: 14px;
    justify-content: center;
}

.inv-empty i {
    font-size: 18px;
}
</style>
