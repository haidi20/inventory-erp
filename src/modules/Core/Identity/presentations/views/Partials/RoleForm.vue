<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { useId } from 'vue';
import { useRoleVM } from '@modules/Core/Identity/presentations/viewmodels/role.vm';
import { createRoleFormUIState } from '@modules/Core/Identity/presentations/viewmodels/role.state';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import BaseModal from '@modules/Core/shared/Components/common/BaseModal.vue';
import type { PermissionModel } from '@modules/Core/Identity/domain/models/role.model';

const props = defineProps<{
    availablePermissions: PermissionModel[];
}>();

const vm = useRoleVM();
const { searchInputRef } = createRoleFormUIState();
const helpVm = useFormHelpVM(`form-help-${useId()}`);

useFormShortcuts({
    helpVm,
    canSubmit: () => !vm.form.processing && vm.canManageRoles,
    submit: () => vm.submitForm(),
    cancel: (input) => vm.closeFormFromEscape(input),
    focusSearch: (input) => vm.focusPermissionSearch(input),
    searchInput: () => searchInputRef.value,
  });


</script>

<template>
    <Teleport to="body">
        <!-- Side Help Drawer (Body Level for True Fixed Positioning) -->
        <Transition name="fade">
            <div v-if="helpVm.isVisible" class="drawer-overlay" style="z-index: 3100;" @click="helpVm.close()"></div>
        </Transition>
        <Transition name="slide-right">
            <div v-if="helpVm.isVisible" class="help-drawer" style="z-index: 3200;">
                <div class="help-drawer-header">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="help-drawer-icon">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>
                        <div>
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Manajemen Role</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Panduan Penggunaan & Shortcut</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                
                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                        Role digunakan untuk mengelompokkan izin (permissions) yang akan diberikan kepada pengguna. Setiap role dapat memiliki kombinasi permissions yang berbeda sesuai kebutuhan operasional.
                    </p>

                    <!-- Langkah-langkah -->
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Isi <strong>Label</strong> dengan nama role (contoh: <em>Admin Gudang</em>).</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Isi <strong>Role ID</strong> sebagai identitas unik sistem tanpa spasi.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Pilih satu atau lebih <strong>Permissions</strong> dari daftar di bawah.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-text">Klik <strong>Simpan Role</strong> atau tekan <kbd>Ctrl + S</kbd>.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Keyboard Shortcuts -->
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-keyboard"></i>Keyboard Shortcuts:
                        </h6>
                        <div class="shortcut-list">
                            <div class="shortcut-item">
                                <kbd>Ctrl + F</kbd>
                                <span>Fokus ke search permission</span>
                            </div>
                            <div class="shortcut-item">
                                <kbd>Ctrl + S</kbd>
                                <span>Simpan role</span>
                            </div>
                            <div class="shortcut-item">
                                <kbd>Esc</kbd>
                                <span>Bersihkan search / Tutup form</span>
                            </div>
                            <div class="shortcut-item">
                                <kbd>F1</kbd>
                                <span>Tampilkan bantuan ini</span>
                            </div>
                            <div class="shortcut-item">
                                <kbd>Tab</kbd>
                                <span>Pindah antar field</span>
                            </div>
                            <div class="shortcut-item">
                                <kbd>Space / Enter</kbd>
                                <span>Pilih permission (saat fokus)</span>
                            </div>
                        </div>
                    </div>

                    <div class="help-footer-info">
                        <i class="fa-solid fa-lightbulb"></i>
                        Tips: Gunakan fitur pencarian (Ctrl+F) untuk menemukan permission dengan cepat berdasarkan kategori atau nama.
                    </div>
                </div>
            </div>
        </Transition>

    </Teleport>
    <BaseModal size="xl" @close="vm.closeForm">
                <div class="modal-header">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h4>
                            <i class="fa-solid" :class="vm.formMode === 'edit' ? 'fa-pen-to-square' : 'fa-plus-circle'" style="color:var(--primary);"></i>
                            {{ vm.formMode === 'edit' ? 'Ubah Role' : 'Tambah Role Baru' }}
                        </h4>
                        <InfoIcon @click="helpVm.toggle()" title="Bantuan Manajemen Role (F1)" />
                    </div>
                    <button type="button" class="modal-close" @click="vm.closeForm" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="vm.submitForm">
                    <div class="modal-body" style="display:grid !important; grid-template-columns: 1fr !important; gap:20px !important;">
                        <!-- Bagian Header Form: Label (Deskripsi) di atas, ID (Slug) di bawah -->
                        <div class="form-group" style="display:block !important;">
                            <label class="form-label" style="font-weight:800; color:#000; font-size:14px;">
                                Label / Nama Role <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="Nama yang akan ditampilkan di menu dan laporan">?</span>
                            </label>
                            <input v-model="vm.form.label" type="text" class="form-control form-control-prominent" :class="{'is-invalid': vm.form.errors.label}" placeholder="misal: Perawat Utama" required data-testid="input-role-label">
                            <div v-if="vm.form.errors.label" class="error-text">{{ vm.form.errors.label }}</div>
                            <div style="font-size:11px; color:var(--muted); margin-top:4px;">Nama ini yang akan tampil di seluruh aplikasi.</div>
                        </div>

                        <div class="form-group" style="display:block !important;">
                            <label class="form-label">
                                Role ID (slug/sistem) <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="ID unik untuk kebutuhan internal sistem (tidak boleh ada spasi)">?</span>
                            </label>
                            <input v-model="vm.form.name" type="text" class="form-control" :class="{'is-invalid': vm.form.errors.name}" placeholder="misal: perawat_utama" required :disabled="!!vm.editingRole" data-testid="input-role-name">
                            <div v-if="vm.form.errors.name" class="error-text">{{ vm.form.errors.name }}</div>
                            <div style="font-size:11px; color:var(--muted); margin-top:4px;">ID unik sistem. Tidak bisa diubah setelah dibuat.</div>
                        </div>

                        <div class="form-group">
                            <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:16px; gap: 16px;">
                                <label class="form-label" style="margin-bottom:0; flex: 1;">
                                    <i class="fa-solid fa-shield-halved" style="margin-right: 6px; color: var(--primary);"></i>Pilih Permissions
                                    <span class="field-help" title="Tentukan akses apa saja yang dimiliki oleh role ini">?</span>
                                </label>
                                <div class="input-with-icon" style="flex: 0 0 300px;">
                                    <i class="fa-solid fa-magnifying-glass icon-left"></i>
                                    <input
                                        ref="searchInputRef"
                                        v-model="vm.permissionSearch"
                                        type="text"
                                        class="form-control form-control-sm permission-search"
                                        style="padding-left: 32px;"
                                        placeholder="Cari izin... (Ctrl+F)"
                                        data-testid="input-search-permission"
                                        title="Tekan Ctrl+F untuk fokus, Esc untuk bersihkan"
                                    >
                                </div>
                            </div>
                            
                            <div class="permission-grid">
                                <div v-for="(perms, group) in vm.filteredPermissions" :key="group" class="perm-group">
                                    <div class="perm-group-header">
                                        <i class="fa-solid fa-folder-open" style="margin-right:6px; opacity:.5;"></i>
                                        {{ group.toUpperCase() }}
                                    </div>
                                    <div class="perm-items">
                                        <label v-for="p in perms" :key="p.uuid" class="perm-item" :class="{ active: vm.form.permissions.includes(p.uuid) }" tabindex="0" @keydown.space.prevent="vm.togglePermission(p.uuid)" @keydown.enter.prevent="vm.togglePermission(p.uuid)" :title="`Klik untuk memilih ${p.label}`">
                                            <input type="checkbox" :value="p.uuid" v-model="vm.form.permissions" tabindex="-1">
                                            <div class="perm-info">
                                                <div class="perm-name">{{ p.label }}</div>
                                                <div class="perm-label">{{ p.name }}</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div v-if="Object.keys(vm.filteredPermissions).length === 0" class="empty-permissions">
                                    <i class="fa-solid fa-note-sticky" style="font-size:24px; margin-bottom:8px; opacity:.3;"></i>
                                    Tidak ada izin yang sesuai kata kunci.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div style="flex:1; font-size:12px; font-weight:700; color:var(--muted);">
                            <span style="color:var(--primary);">{{ vm.form.permissions.length }}</span> izin dipilih
                        </div>
                        <button type="button" class="btn btn-outline" @click="vm.closeForm" title="Batal (Esc)">Batal (Esc)</button>
                        <button type="submit" class="btn btn-primary" :disabled="vm.form.processing || !vm.canManageRoles" data-testid="btn-submit-role" title="Simpan Perubahan (Ctrl+S)">
                            <i class="fa-solid fa-save"></i>
                            {{ vm.form.processing ? 'Menyimpan...' : (vm.formMode === 'edit' ? 'Simpan Perubahan' : 'Simpan Role') + ' (Ctrl+S)' }}
                        </button>
                    </div>
                </form>
    </BaseModal>
</template>

<style scoped src="../../styles/role-form.css"></style>
