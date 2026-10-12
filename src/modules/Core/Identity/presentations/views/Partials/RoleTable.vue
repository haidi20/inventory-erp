<script setup lang="ts">
import type { RoleModel } from '@modules/Core/Identity/domain/models/role.model';
import { useRoleVM } from '@modules/Core/Identity/presentations/viewmodels/role.vm';

const vm = useRoleVM();
const props = defineProps<{
    roles: RoleModel[];
}>();

</script>

<template>
    <div class="table-wrap" style="overflow-x: auto; width: 100%; position: relative;">
        <!-- Loading Overlay -->
        <Transition name="fade">
            <div v-if="vm.isLoading" class="loading-overlay" data-testid="loading-indicator">
                <div class="loading-spinner">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </div>
            </div>
        </Transition>

        <table style="table-layout: fixed; min-width: 900px; width: 100%;">
            <thead>
                <tr>
                    <th style="width: 70px;">Avatar</th>
                    <th style="width: 25%;">
                        <button type="button" class="sortable-header" @click="vm.toggleSort('label')" data-testid="sort-role-label">
                            Label / Nama Role
                            <span class="sort-indicator">{{ vm.sortBy === 'label' ? (vm.sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                        </button>
                    </th>
                    <th style="width: 150px;">Users</th>
                    <th style="width: auto;">Permissions</th>
                    <th style="width: 120px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="role in (roles || [])" :key="role.uuid">
                    <td>
                        <div :style="{ background: vm.getAvatarColor(role.label) }" class="role-avatar-circle" data-testid="role-avatar">
                            {{ vm.getAvatarInitial(role.label) }}
                        </div>
                    </td>
                    <td>
                        <div class="role-main-name" data-testid="role-label-text">{{ role.label || '-' }}</div>
                    </td>
                    <td>
                        <span class="badge" :class="(role.users_count || 0) > 0 ? 'badge-success' : 'badge-secondary'">
                            {{ role.users_count || 0 }} User
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-info" style="font-size: 10px;">
                            {{ role.permissions_count }} Permissions
                        </span>
                    </td>
                    <td>
                        <div class="table-action-cell" style="justify-content: center;">
                            <button v-if="vm.canManageRoles" type="button" class="btn btn-outline btn-sm" @click="vm.openEdit(role)" title="Edit Role" data-testid="btn-edit-role">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button 
                                v-if="vm.canManageRoles && role.name !== 'admin' && Number(role.users_count || 0) === 0" 
                                type="button" 
                                class="btn btn-outline btn-sm btn-danger-soft" 
                                @click="vm.deleteRole(role)"
                                title="Hapus Role"
                                data-testid="btn-delete-role"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <button 
                                v-else-if="vm.canManageRoles"
                                disabled 
                                class="btn btn-outline btn-sm" 
                                style="opacity:.3;cursor:not-allowed;" 
                                title="Role digunakan / protected"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="roles?.length === 0">
                    <td colspan="5">
                        <div class="empty-state" style="border:none;background:transparent;padding:60px;">
                            <i class="fa-solid fa-shield-slash" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                            Tidak ada role ditemukan
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped src="../../styles/role-table.css"></style>
