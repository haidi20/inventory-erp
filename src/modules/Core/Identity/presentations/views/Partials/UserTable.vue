<script setup lang="ts">
import type { UserModel } from '@modules/Core/Identity/domain/models/user.model';
import { useUserVM } from '@modules/Core/Identity/presentations/viewmodels/user.vm';

defineProps<{
    users: UserModel[];
}>();

const vm = useUserVM();
</script>

<template>
    <div class="table-wrap" style="position: relative;">
        <!-- Loading Overlay -->
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
                    <th style="width: 70px;">Avatar</th>
                    <th style="width: 25%;">
                        <button type="button" class="sortable-header" @click="vm.toggleSort('name')" data-testid="sort-user-name">
                            Nama
                            <span class="sort-indicator">{{ vm.sortBy === 'name' ? (vm.sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                        </button>
                    </th>
                    <th style="width: 25%;">
                        <button type="button" class="sortable-header" @click="vm.toggleSort('email')" data-testid="sort-user-email">
                            Email
                            <span class="sort-indicator">{{ vm.sortBy === 'email' ? (vm.sortDirection === 'asc' ? '↑' : '↓') : '↕' }}</span>
                        </button>
                    </th>
                    <th>Role</th>
                    <th>Employee</th>
                    <th style="width: 130px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.uuid">
                    <td>
                        <div :style="{ background: user.avatar_color }" class="user-avatar-circle">
                            {{ user.avatar_initial }}
                        </div>
                    </td>
                    <td>
                        <div class="user-main-name">{{ user.name }}</div>
                        <div v-if="user.is_current_user" class="badge badge-info" style="font-size: 9px; transform: scale(0.9); margin-top: 4px; origin-left: true;">
                            <i class="fa-solid fa-circle-user"></i> Anda
                        </div>
                    </td>
                    <td>
                        <div class="user-email-text">{{ user.email }}</div>
                    </td>
                    <td>
                        <template v-if="user.roles && user.roles.length">
                            <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                <span
                                    v-for="r in user.roles"
                                    :key="r.uuid"
                                    class="badge badge-secondary"
                                    style="font-size: 9px;"
                                >{{ r.name.toUpperCase() }}</span>
                            </div>
                            <div class="user-role-label">{{ vm.formatRoleLabels(user) }}</div>
                        </template>
                        <template v-else-if="user.role_uuid">
                            <span class="badge badge-secondary" style="font-size: 9px;">
                                {{ user.role_name.toUpperCase() }}
                            </span>
                            <div class="user-role-label">{{ user.role_label }}</div>
                        </template>
                        <span v-else class="badge badge-warning">
                            <i class="fa-solid fa-triangle-exclamation"></i> No Role
                        </span>
                    </td>
                    <td>
                        <span v-if="user.employee_uuid" class="badge badge-info" style="text-transform: none; background: transparent;">
                            <i class="fa-solid fa-link"></i> {{ user.employee_name }}
                        </span>
                        <span v-else style="color: var(--muted); opacity: 0.5;">-</span>
                    </td>
                    <td>
                        <div class="table-action-cell" style="justify-content: center;">
                            <button v-if="vm.canManageUsers" type="button" class="btn btn-outline btn-sm" @click="vm.openEdit(user)" title="Edit User" data-testid="btn-edit-user">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button 
                                v-if="vm.canManageUsers && !user.is_current_user" 
                                type="button" 
                                class="btn btn-outline btn-sm btn-danger-soft" 
                                @click="vm.deleteUser(user)"
                                title="Hapus User"
                                data-testid="btn-delete-user"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="users.length === 0">
                    <td colspan="6">
                        <div class="empty-state" style="border:none;background:transparent;padding:60px;">
                            <i class="fa-solid fa-user-slash" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                            Tidak ada user ditemukan
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.user-avatar-circle {
    width: 38px; height: 38px; border-radius: 12px;
    color: #fff; display: grid; place-items: center;
    font-weight: 800; font-size: 14px;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);
}
.user-main-name { font-weight: 700; color: var(--text); font-size: 13.5px; }
.user-email-text { font-size: 12.5px; color: var(--muted); }
.user-role-label { font-size: 11px; color: var(--muted); margin-top: 2px; }
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
