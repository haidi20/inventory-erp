<script setup lang="ts">
import { useLookupValueVM } from '@modules/Operational/Master/presentations/viewmodels/lookup_value.vm';

const vm = useLookupValueVM();
</script>

<template>
    <div class="filter-card">
        <div class="filter-container">
            <div class="search-input-group">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input
                    v-model="vm.filters.value.search"
                    type="text"
                    class="search-input"
                    placeholder="Cari berdasarkan nama atau kode..."
                    @keyup.enter="vm.handleSearch"
                    data-testid="input-search-value"
                >
            </div>
            <div class="filter-actions">
                <button type="button" class="btn btn-primary" @click="vm.handleSearch" data-testid="btn-apply-filter-value">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.filter-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.filter-container {
    display: flex;
    gap: 16px;
    align-items: center;
    flex-wrap: wrap;
}

.search-input-group {
    position: relative;
    flex: 1;
    min-width: 300px;
}

.search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    font-size: 14px;
}

.search-input {
    width: 100%;
    padding: 12px 16px 12px 46px;
    border: 1px solid var(--border);
    border-radius: 12px;
    font-size: 14px;
    background: #f8fafc;
    transition: all 0.2s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
}

.filter-actions {
    display: flex;
    gap: 12px;
}

@media (max-width: 640px) {
    .search-input-group { min-width: 100%; }
    .filter-actions { width: 100%; }
    .filter-actions .btn { flex: 1; }
}
</style>
