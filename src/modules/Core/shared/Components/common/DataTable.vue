<script setup lang="ts">
import Select2 from '@modules/Core/shared/Components/common/Select2.vue';
import { createDataTableVMStore } from '@modules/Core/shared/Components/common/DataTable.vm';
import { DATATABLE_PER_PAGE_OPTIONS } from '@modules/Core/shared/Components/common/DataTable.state';
import { computed, watch } from 'vue';
import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';
import type { DataTableConfig, DataTableProps } from '@modules/Core/shared/Components/common/DataTable-types';
export type { DataTableColumn };

const props = withDefaults(defineProps<DataTableProps>(), {
    storeId: 'datatable-default',
    perPageOptions: () => [...DATATABLE_PER_PAGE_OPTIONS],
    defaultPerPage: 10,
    searchable: true,
    searchPlaceholder: 'Cari...',
    queryParams: () => ({}),
    items: () => [],
    searchColumns: () => [],
    defaultSortBy: '',
    defaultSortDirection: 'asc',
});

const useVM = createDataTableVMStore(props.storeId, {
    defaultSortBy: props.defaultSortBy,
    defaultSortDirection: props.defaultSortDirection,
    defaultPerPage: props.defaultPerPage,
});
const vm = useVM();

const vmConfig = computed<DataTableConfig>(() => ({
    url: props.url,
    queryParams: props.queryParams,
    items: props.items,
    perPageOptions: props.perPageOptions,
    searchColumns: props.searchColumns,
}));

watch(vmConfig, vm.syncConfig, { deep: true, immediate: true });

defineExpose({ vm, reload: vm.reload });
</script>

<template>
    <div class="dt-wrapper">
        <!-- Toolbar -->
        <div v-if="searchable || vm.hasSearchColumns" class="dt-toolbar">
            <div class="dt-search-area">
                <div v-if="vm.hasSearchColumns" class="dt-col-select-wrap" :data-testid="searchColumnTestId">
                    <i class="fa-solid fa-filter dt-col-icon"></i>
                    <Select2
                        v-model="vm.currentSearchColumn"
                        :options="vm.searchColumnOptions"
                        mode="client"
                        :clearable="false"
                        label="text"
                        value-key="id"
                        placeholder="Pilih Kolom"
                        class="dt-col-select2"
                    />
                </div>
                <div v-if="searchable" class="dt-search-wrap" :class="{ 'dt-search-joined': vm.hasSearchColumns }">
                    <i class="fa-solid fa-magnifying-glass dt-search-icon"></i>
                    <input
                        v-model="vm.currentSearch"
                        @input="vm.onSearchInput"
                        type="text"
                        :placeholder="searchPlaceholder"
                        class="dt-search-input"
                        :data-testid="searchTestId"
                        autocomplete="off"
                    />
                </div>
            </div>
            <div class="dt-perpage-wrap">
                <span class="dt-perpage-label">Baris:</span>
                <select v-model="vm.currentPerPage" @change="vm.onPerPageChange" class="dt-perpage-select">
                    <option v-for="opt in vm.perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="dt-table-wrap">
            <div v-if="vm.isLoading" class="dt-loading">
                <i class="fa-solid fa-spinner fa-spin"></i>
            </div>
            <table class="dt-table">
                <colgroup>
                    <col v-for="col in columns" :key="col.key" :style="col.width ? { width: col.width, minWidth: col.width } : {}" />
                </colgroup>
                <thead>
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :style="{ width: col.width, textAlign: col.align ?? 'left' }"
                            :class="['dt-th', col.sortable ? 'dt-th-sortable' : '']"
                            @click="vm.toggleSort(col)"
                        >
                            <span class="dt-th-inner">
                                {{ col.label }}
                                <span v-if="col.sortable" class="dt-sort-icon">
                                    <i v-if="vm.sortBy === col.key && vm.sortDirection === 'asc'" class="fa-solid fa-arrow-up"></i>
                                    <i v-else-if="vm.sortBy === col.key && vm.sortDirection === 'desc'" class="fa-solid fa-arrow-down"></i>
                                    <i v-else class="fa-solid fa-arrows-up-down dt-sort-idle"></i>
                                </span>
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="vm.pagedItems.length === 0 && !vm.isLoading">
                        <td :colspan="columns.length" class="dt-empty">
                            <i class="fa-solid fa-folder-open" style="font-size:24px;display:block;margin-bottom:8px;opacity:0.3;"></i>
                            Tidak ada data ditemukan
                        </td>
                    </tr>
                    <tr
                        v-for="(row, rowIdx) in vm.pagedItems"
                        :key="rowIdx"
                        class="dt-row"
                        @click="$emit('row-click', row)"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            :style="{ textAlign: col.align ?? 'left', width: col.width }"
                            :class="col.class"
                        >
                            <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]">
                                {{ row[col.key] ?? '-' }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="dt-pagination">
            <span class="dt-pagination-info">
                Menampilkan {{ vm.displayStart }}-{{ vm.displayEnd }} dari {{ vm.totalItems }} data
            </span>
            <div class="dt-pagination-btns">
                <button @click="vm.goToPage(1)" :disabled="vm.page === 1" class="pg-btn" title="Pertama">
                    <i class="fa-solid fa-angles-left"></i>
                </button>
                <button @click="vm.goToPage(vm.page - 1)" :disabled="vm.page === 1" class="pg-btn" title="Sebelumnya">
                    <i class="fa-solid fa-angle-left"></i>
                </button>
                <button
                    v-for="p in vm.pageNumbers"
                    :key="p"
                    @click="vm.goToPage(p)"
                    class="pg-btn"
                    :class="{ active: p === vm.page }"
                >{{ p }}</button>
                <button @click="vm.goToPage(vm.page + 1)" :disabled="vm.page === vm.currentLastPage" class="pg-btn" title="Berikutnya">
                    <i class="fa-solid fa-angle-right"></i>
                </button>
                <button @click="vm.goToPage(vm.currentLastPage)" :disabled="vm.page === vm.currentLastPage" class="pg-btn" title="Terakhir">
                    <i class="fa-solid fa-angles-right"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped src="./DataTable.css"></style>
