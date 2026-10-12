<script setup lang="ts">
import { useRoleVM } from '@modules/Core/Identity/presentations/viewmodels/role.vm';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';

const vm = useRoleVM();
</script>

<template>
    <div class="card" style="padding: 16px 20px; margin-bottom: 24px; border-style: dashed;">
        <div class="filter-row">
            <div class="filter-group-main">
                <label class="filter-label">Cari Role</label>
                <div class="search-input-group">
                    <div class="input-with-icon" style="width: 140px;">
                        <i class="fa-solid fa-filter icon-left"></i>
                        <Select2ClientSide
                            v-model="vm.searchColumn"
                            :get-data="vm.searchColumns"
                            :clearable="false"
                            placeholder="Pilih Kolom"
                            data-testid="select-search-column-role"
                        />
                    </div>
                    <div class="input-with-icon" style="flex: 1;">
                        <i class="fa-solid fa-magnifying-glass icon-left"></i>
                        <input
                            v-model="vm.search"
                            type="text"
                            class="form-control"
                            style="padding-left: 38px; border-top-left-radius: 0; border-bottom-left-radius: 0; border-left: none;"
                            placeholder="Ketik kata kunci untuk mencari..."
                            autocomplete="off"
                            data-testid="input-search-role"
                        >
                    </div>
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
                        data-testid="select-per-page-role"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped src="../../styles/role-filters.css"></style>
<style scoped>
:deep(.ps-select .vs__dropdown-toggle) {
    padding-left: 32px;
    min-height: 38px;
}

.search-input-group :deep(.ps-select .vs__dropdown-toggle) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
</style>
