<script setup lang="ts">
import type { BackupModel } from '@modules/Core/Platform/domain/models/backup.model';
import { useBackupVM } from '@modules/Core/Platform/presentations/viewmodels/backup.vm';

defineProps<{
    backups: BackupModel[];
}>();

const vm = useBackupVM();
</script>

<template>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th style="width: 35%; cursor: pointer;" @click="vm.toggleSort(1)">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            Nama File
                            <i v-if="vm.orderColumn === 1" :class="vm.orderDirection === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down'" style="color: var(--primary);"></i>
                            <i v-else class="fa-solid fa-sort" style="opacity: 0.2;"></i>
                        </div>
                    </th>
                    <th style="width: 15%; cursor: pointer;" @click="vm.toggleSort(2)">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            Ukuran
                            <i v-if="vm.orderColumn === 2" :class="vm.orderDirection === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down'" style="color: var(--primary);"></i>
                            <i v-else class="fa-solid fa-sort" style="opacity: 0.2;"></i>
                        </div>
                    </th>
                    <th style="cursor: pointer;" @click="vm.toggleSort(4)">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            Status
                            <i v-if="vm.orderColumn === 4" :class="vm.orderDirection === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down'" style="color: var(--primary);"></i>
                            <i v-else class="fa-solid fa-sort" style="opacity: 0.2;"></i>
                        </div>
                    </th>
                    <th>Tipe</th>
                    <th style="width: 25%; cursor: pointer;" @click="vm.toggleSort(0)">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            Tanggal Dibuat
                            <i v-if="vm.orderColumn === 0" :class="vm.orderDirection === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down'" style="color: var(--primary);"></i>
                            <i v-else class="fa-solid fa-sort" style="opacity: 0.2;"></i>
                        </div>
                    </th>
                    <th style="width: 100px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(backup, index) in backups" :key="backup.name">
                    <td>{{ (vm.meta.from || 1) + index }}</td>
                    <td>
                        <div class="backup-name">{{ backup.name }}</div>
                    </td>
                    <td>
                        <span class="badge badge-info" style="background: transparent; text-transform: none; font-weight: 700;">
                            {{ backup.size_label }}
                        </span>
                    </td>
                    <td>
                        <div class="backup-status-wrapper">
                            <span class="badge" :class="backup.status === 'ready' ? 'badge-info' : 'badge-warning'" style="font-size: 10px;">
                                {{ backup.status_label.toUpperCase() }}
                            </span>
                            <div class="backup-source-text">{{ backup.source_label }}</div>
                        </div>
                    </td>
                    <td>
                        <span class="badge" :class="backup.history_type === 'system' ? 'badge-secondary' : 'badge-primary'" style="font-size: 9px;">
                            {{ backup.history_type_label }}
                        </span>
                    </td>
                    <td>
                        <div class="backup-date-text">{{ backup.updated_at_label }}</div>
                    </td>
                    <td>
                        <div class="table-action-cell" style="justify-content: center;">
                            <button 
                                type="button" 
                                class="btn btn-outline btn-sm" 
                                @click="vm.downloadBackupFile(backup.name)" 
                                title="Unduh Cadangan"
                            >
                                <i class="fa-solid fa-download"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="backups.length === 0">
                    <td colspan="7">
                        <div class="empty-state" style="border:none;background:transparent;padding:60px;">
                            <i class="fa-solid fa-database" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                            Tidak ada riwayat backup ditemukan
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.backup-name { font-weight: 700; color: var(--text); font-size: 13px; word-break: break-all; }
.backup-date-text { font-size: 12.5px; color: var(--muted); }
.backup-source-text { font-size: 10px; color: var(--muted); margin-top: 2px; }
.backup-status-wrapper { display: flex; flex-direction: column; align-items: flex-start; }
</style>
