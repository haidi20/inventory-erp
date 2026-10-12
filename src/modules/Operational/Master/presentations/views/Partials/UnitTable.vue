<script setup lang="ts">
import type { UnitModel } from '@modules/Operational/Master/domain/models/unit.model';
import { useUnitVM } from '@modules/Operational/Master/presentations/viewmodels/unit.vm';

defineProps<{
    units: UnitModel[];
}>();

const vm = useUnitVM();
</script>

<template>
    <div class="table-wrap" style="position: relative;">
        <Transition name="fade">
            <div v-if="vm.isLoading" class="loading-overlay" data-testid="loading-indicator">
                <div class="loading-spinner">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </div>
            </div>
        </Transition>
        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">
                        <button type="button" class="sortable-header" @click="vm.toggleSort('code')" data-testid="sort-unit-code">
                            Kode
                            <span class="sort-indicator">{{ vm.sortBy === 'code' ? (vm.sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                        </button>
                    </th>
                    <th style="width: 26%;">
                        <button type="button" class="sortable-header" @click="vm.toggleSort('name')" data-testid="sort-unit-name">
                            Nama
                            <span class="sort-indicator">{{ vm.sortBy === 'name' ? (vm.sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                        </button>
                    </th>
                    <th style="width: 16%;">Simbol</th>
                    <th>Keterangan</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 130px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="unit in units" :key="unit.uuid">
                    <td><span class="badge badge-secondary">{{ unit.code }}</span></td>
                    <td><div class="unit-main-name">{{ unit.name }}</div></td>
                    <td>{{ unit.symbol || '-' }}</td>
                    <td>{{ unit.description || '-' }}</td>
                    <td>
                        <span class="badge" :class="unit.is_active ? 'badge-info' : 'badge-warning'">
                            {{ unit.is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <div class="table-action-cell" style="justify-content: center;">
                            <button v-if="vm.canManageUnits" type="button" class="btn btn-outline btn-sm" @click="vm.openEdit(unit)" title="Edit Satuan" data-testid="btn-edit-unit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button v-if="vm.canManageUnits" type="button" class="btn btn-outline btn-sm btn-danger-soft" @click="vm.deleteUnit(unit)" title="Hapus Satuan" data-testid="btn-delete-unit">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="units.length === 0">
                    <td colspan="6">
                        <div class="empty-state" style="border:none;background:transparent;padding:60px;">
                            <i class="fa-solid fa-ruler-combined" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                            Tidak ada satuan ditemukan
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.unit-main-name { font-weight: 700; color: var(--text); font-size: 13.5px; }
.sortable-header {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    border: none;
    padding: 0;
    font: inherit;
    font-weight: 800;
    color: inherit;
    cursor: pointer;
}
.sort-indicator { font-size: 11px; color: var(--muted); }
.btn-danger-soft { color: #ef4444; border-color: rgba(239, 68, 68, 0.1); }
.btn-danger-soft:hover { background: #fee2e2; border-color: #ef4444; color: #b91c1c; }
.loading-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255, 255, 255, 0.7);
    display: grid;
    place-items: center;
    z-index: 50;
    backdrop-filter: blur(2px);
}
.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    color: var(--primary);
    font-size: 32px;
}
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
