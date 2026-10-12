<script setup lang="ts">
import { useBackupVM } from '@modules/Core/Platform/presentations/viewmodels/backup.vm';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';

const vm = useBackupVM();
</script>

<template>
    <div class="card" style="padding: 16px 20px; margin-bottom: 24px; border-style: dashed;">
        <div class="filter-row">
            <div class="filter-group-main">
                <label class="filter-label">Cari Riwayat</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-magnifying-glass icon-left"></i>
                    <input
                        v-model="vm.search"
                        type="text"
                        class="form-control"
                        style="padding-left: 38px;"
                        placeholder="Ketik nama file untuk mencari..."
                        autocomplete="off"
                        data-testid="input-search-history"
                    >
                </div>
            </div>
            <div class="filter-group-side">
                <label class="filter-label">Jenis Riwayat</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-clock-rotate-left icon-left"></i>
                    <Select2ClientSide
                        v-model="vm.historyType"
                        :get-data="vm.historyTypes"
                        :clearable="false"
                        placeholder="Pilih Jenis"
                        data-testid="select-history-type"
                    />
                </div>
            </div>
            <div style="min-width: 120px;">
                <label class="filter-label">Per Halaman</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-list-numeric icon-left"></i>
                    <Select2ClientSide
                        v-model="vm.perPage"
                        :get-data="vm.perPageOptions"
                        :clearable="false"
                        placeholder="10"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.filter-row { display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; }
.filter-group-main { flex: 1; min-width: 300px; }
.filter-group-side { min-width: 220px; }

.input-with-icon { position: relative; display: flex; align-items: center; width: 100%; }
.icon-left { position: absolute; left: 14px; color: var(--muted); font-size: 13px; pointer-events: none; opacity: 0.6; z-index: 1; }

.filter-label {
    display: block; font-size: 11px; font-weight: 800;
    text-transform: uppercase; letter-spacing: 1px;
    color: var(--muted); margin-bottom: 8px;
    padding-left: 2px;
}

:deep(.ps-select .vs__dropdown-toggle) {
    padding-left: 32px;
    min-height: 38px;
}
</style>
