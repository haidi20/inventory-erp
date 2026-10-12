<script setup lang="ts">
import { useFarmacyVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy.vm';
import type { FarmacyRoleItem } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';

const props = defineProps<{
    roles: FarmacyRoleItem[];
}>();

const vm = useFarmacyVM();
</script>

<template>
    <div class="card" style="padding: 16px 20px; margin-bottom: 24px; border-style: dashed;">
        <div class="filter-row">
            <div class="filter-group-main">
                <label class="filter-label">Cari Pengguna</label>
                <div class="search-input-group">
                    <div class="input-with-icon" style="width: 140px;">
                        <i class="fa-solid fa-filter icon-left"></i>
                        <Select2ClientSide
                            v-model="vm.searchColumn"
                            :get-data="vm.searchColumns"
                            :clearable="false"
                            placeholder="Pilih Kolom"
                            data-testid="select-search-column-Farmacy"
                        />
                    </div>
                    <div class="input-with-icon" style="flex: 1;">
                        <i class="fa-solid fa-magnifying-glass icon-left"></i>
                        <input
                            v-model="vm.search"
                            type="text"
                            class="form-control"
                            style="padding-left: 38px; border-top-left-radius: 0; border-bottom-left-radius: 0; border-left: none;"
                            placeholder="Ketik nama atau email untuk mencari..."
                            autocomplete="off"
                            data-testid="input-search-Farmacy"
                        >
                    </div>
                </div>
            </div>
            <div class="filter-group-side">
                <label class="filter-label">Berdasarkan Role</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-id-badge icon-left"></i>
                    <Select2ClientSide
                        v-model="vm.roleFilter"
                        :get-data="roles"
                        label="label"
                        :reduce="(r: any) => r.uuid"
                        placeholder="Semua Role"
                        data-testid="select-role-filter-Farmacy"
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
                        data-testid="select-per-page-Farmacy"
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
.search-input-group { display: flex; align-items: stretch; }

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

.search-input-group :deep(.ps-select .vs__dropdown-toggle) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
</style>
