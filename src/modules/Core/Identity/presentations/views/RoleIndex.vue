<script setup lang="ts">
import { onBeforeUnmount, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useRoleVM } from '@modules/Core/Identity/presentations/viewmodels/role.vm';
import type { RoleFilters } from '@modules/Core/Identity/domain/models/role.model';
import RoleTable from '@modules/Core/Identity/presentations/views/Partials/RoleTable.vue';
import RoleFiltersComp from '@modules/Core/Identity/presentations/views/Partials/RoleFilters.vue';
import RoleForm from '@modules/Core/Identity/presentations/views/Partials/RoleForm.vue';
import Pagination from '@modules/Core/shared/Components/Pagination.vue';

const props = defineProps<{
    filters: RoleFilters;
}>();

const vm = useRoleVM();
onMounted(() => {
    vm.initFilters(props.filters);
    vm.initImportMenuDismiss();
});

onBeforeUnmount(() => {
    vm.disposeImportMenuDismiss();
});

</script>

<template>
    <Head title="Management Roles - Inventory" />

    <div v-if="vm.flash.success" class="alert-banner alert-success">
        <i class="fa-solid fa-circle-check"></i>
        {{ vm.flash.success }}
    </div>
    <div v-else-if="vm.flash.error" class="alert-banner alert-error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        {{ vm.flash.error }}
    </div>

    <div class="page-header" style="margin-bottom: 32px;">
        <div>
            <div class="breadcrumb">
                <i class="fa-solid fa-house"></i>
                <span>/</span> Management <span>/</span> <span style="color:var(--text);">Roles</span>
            </div>
            <div class="page-title" style="margin-bottom: 4px;">Manajemen Role & Hak Akses</div>
            <div class="page-sub">Kelola level otorisasi, hak akses fitur, dan izin pengguna sistem klinik.</div>
        </div>
        <div style="display:flex; gap:12px; align-items: center;">
            <button type="button" @click="vm.exportRoles()" class="btn btn-outline" :disabled="vm.isExporting" title="Download Excel" data-testid="btn-export-role">
                <i class="fa-solid fa-file-excel" style="color:#16a34a;"></i> 
                {{ vm.isExporting ? 'Mengekspor...' : 'Ekspor' }}
            </button>
            <div v-if="vm.canManageRoles" class="import-menu" @click.stop>
                <button type="button" class="btn btn-outline import-menu-trigger" :disabled="vm.isImporting" @click="vm.toggleImportMenu" data-testid="btn-import-menu">
                    <i class="fa-solid fa-file-import"></i>
                    {{ vm.isImporting ? 'Mengimpor...' : 'Impor' }}
                    <i class="fa-solid fa-chevron-down import-menu-caret" :class="{ open: vm.showImportMenu }"></i>
                </button>
                <div v-if="vm.showImportMenu" class="import-menu-dropdown">
                    <button type="button" class="import-menu-item" @click="vm.openImportData" data-testid="btn-import-data">
                        <i class="fa-solid fa-file-import"></i>
                        Import Data
                    </button>
                    <button type="button" class="import-menu-item" @click="vm.downloadImportTemplate" data-testid="btn-import-template">
                        <i class="fa-solid fa-file-arrow-down"></i>
                        Download Template
                    </button>
                </div>
            </div>
            <button v-if="vm.canManageRoles" type="button" @click="vm.openCreate" class="btn btn-primary" data-testid="btn-create-role">
                <i class="fa-solid fa-plus-circle"></i> Buat Role Baru
            </button>
        </div>
    </div>

    <!-- Filters -->
    <RoleFiltersComp 
        @filter="vm.fetchRoles" 
    />

    <!-- Table Section -->
    <div style="margin-top: 32px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; padding: 0 4px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--text);">Daftar Role</h3>
            </div>
            <div class="badge badge-secondary">{{ vm.meta?.total || 0 }} total data</div>
        </div>
        <RoleTable 
            :roles="vm.roles" 
            @edit="vm.openEdit" 
            @delete="vm.deleteRole" 
        />
        
        <div v-if="vm.meta && (vm.meta.total ?? 0) > 0" class="table-footer">
            <div class="footer-stats">
                Menampilkan {{ vm.meta?.from || 0 }} - {{ vm.meta?.to || 0 }} dari {{ vm.meta?.total || 0 }} data
            </div>
            <Pagination 
            v-if="vm.meta?.links" 
            :links="vm.meta.links" 
            manual 
            @change-page="vm.fetchRolesByUrl" 
        />
        </div>
    </div>

    <RoleForm 
        v-if="vm.showForm" 
        :available-permissions="vm.availablePermissions"
        @close="vm.closeForm" 
    />

    <input
        id="importInput"
        type="file"
        accept=".xlsx,.xls"
        style="display:none;"
        @change="vm.handleImport"
    >
</template>

<style scoped src="../styles/role-index.css"></style>

