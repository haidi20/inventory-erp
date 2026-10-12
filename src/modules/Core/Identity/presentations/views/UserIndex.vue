<script setup lang="ts">
import { onBeforeUnmount, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useUserVM } from '@modules/Core/Identity/presentations/viewmodels/user.vm';
import type { UserModel, UserSummary, UserFilters } from '@modules/Core/Identity/domain/models/user.model';
import UserTable from '@modules/Core/Identity/presentations/views/Partials/UserTable.vue';
import UserFiltersComp from '@modules/Core/Identity/presentations/views/Partials/UserFilters.vue';
import UserForm from '@modules/Core/Identity/presentations/views/Partials/UserForm.vue';
import Pagination from '@modules/Core/shared/Components/Pagination.vue';

const props = defineProps<{
    users: any; // Initial Inertia data (optional)
    roles: any[];
    summary: UserSummary;
    filters: UserFilters;
}>();

const vm = useUserVM();
onMounted(() => {
    vm.initFilters(props.filters);
    vm.initImportMenuDismiss();
});

onBeforeUnmount(() => {
    vm.disposeImportMenuDismiss();
});

</script>

<template>
    <Head title="Management Users - Inventory" />

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
                <span>/</span> Management <span>/</span> <span style="color:var(--text);">Users</span>
            </div>
            <div class="page-title" style="margin-bottom: 4px;">Pengaturan User</div>
            <div class="page-sub">Kelola akun pengguna, role, dan hak akses sistem klinik secara terpusat.</div>
        </div>
        <div style="display:flex; gap:12px; align-items: center;">
            <div class="header-divider" style="height: 32px; margin: 0 8px;"></div>
            <button type="button" @click="vm.exportUsers()" class="btn btn-outline" :disabled="vm.isExporting" title="Download Excel" data-testid="btn-export-user">
                <i class="fa-solid fa-file-excel" style="color:#16a34a;"></i> 
                {{ vm.isExporting ? 'Mengekspor...' : 'Ekspor' }}
            </button>
            <div v-if="vm.canManageUsers" class="import-menu" @click.stop>
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
            <button v-if="vm.canManageUsers" type="button" @click="vm.openCreate" class="btn btn-primary" data-testid="btn-create-user">
                <i class="fa-solid fa-plus-circle"></i> Tambah User
            </button>
        </div>
    </div>

    <!-- Filters -->
    <UserFiltersComp 
        :roles="roles" 
        @filter="vm.fetchUsers" 
    />

    <!-- Table Section -->
    <div style="margin-top: 32px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; padding: 0 4px;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--text);">Data Pengguna</h3>
            </div>
            <div class="badge badge-secondary">{{ vm.meta?.total || 0 }} total data</div>
        </div>
        <UserTable 
            :users="vm.users" 
            @edit="vm.openEdit" 
            @delete="vm.deleteUser" 
        />
        
        <div v-if="vm.meta?.total > 0" class="table-footer">
            <div class="footer-stats">
                Menampilkan {{ vm.meta?.from || 0 }} - {{ vm.meta?.to || 0 }} dari {{ vm.meta?.total || 0 }} data
            </div>
            <Pagination :links="vm.meta?.links" />
        </div>
    </div>

    <UserForm 
        v-if="vm.showForm" 
        :roles="roles"
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

<style scoped>
.table-footer {
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.footer-stats {
    font-size: 13px;
    font-weight: 700;
    color: var(--muted);
}

.alert-banner {
    margin-bottom: 20px;
    padding: 14px 16px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    font-weight: 700;
}

.alert-success {
    background: #ecfdf5;
    color: #166534;
    border: 1px solid #86efac;
}

.alert-error {
    background: #fff1f2;
    color: #be123c;
    border: 1px solid #fecdd3;
}

.import-menu {
    position: relative;
}

.import-menu-trigger {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.import-menu-caret {
    font-size: 11px;
    transition: transform 0.2s ease;
}

.import-menu-caret.open {
    transform: rotate(180deg);
}

.import-menu-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    min-width: 220px;
    padding: 8px;
    border: 1px solid var(--border);
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 18px 38px rgba(15, 23, 42, 0.12);
    display: grid;
    gap: 4px;
    z-index: 20;
}

.import-menu-item {
    width: 100%;
    border: none;
    background: transparent;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    text-align: left;
}

.import-menu-item:hover {
    background: #f8fafc;
    color: var(--primary);
}

@media (max-width: 640px) {
    .table-footer {
        flex-direction: column;
        text-align: center;
    }

    .import-menu-dropdown {
        left: 0;
        right: auto;
        min-width: 100%;
    }
}
</style>

