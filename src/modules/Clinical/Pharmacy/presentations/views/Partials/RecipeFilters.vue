<script setup lang="ts">
import { useFarmacyRecipeVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_recipe.vm';
import { RECIPE_FILTER_STATUS_OPTIONS } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_recipe.state';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';

const vm = useFarmacyRecipeVM();

</script>

<template>
    <div class="card" style="padding: 20px; margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 100px; gap: 16px; align-items: end;">
            <div class="field">
                <label>Status</label>
                <Select2ClientSide
                    v-model="vm.filters.status"
                    :get-data="RECIPE_FILTER_STATUS_OPTIONS"
                    :clearable="false"
                    placeholder="Semua Status"
                />
            </div>
            <div class="field">
                <label>Tanggal</label>
                <input v-model="vm.filters.date" type="date" class="form-control" />
            </div>
            <div class="field">
                <label>Pencarian</label>
                <input 
                    v-model="vm.filters.search" 
                    type="text" 
                    class="form-control" 
                    placeholder="Nama pasien / No. Resep..." 
                    @keyup.enter="vm.applyFilters" 
                />
            </div>
            <button 
                class="btn btn-primary" 
                style="height: 42px; font-weight: 800; text-transform: uppercase; font-size: 13px;" 
                @click="vm.applyFilters"
                :disabled="vm.isLoading"
            >
                <span v-if="!vm.isLoading">Cari</span>
                <i v-else class="fa-solid fa-spinner fa-spin"></i>
            </button>
        </div>
    </div>
</template>

<style scoped>
label { display: block; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin-bottom: 8px; }

:deep(.vs__dropdown-toggle) {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    min-height: 42px;
}
</style>
