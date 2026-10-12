<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { useId } from 'vue';
import { useFarmacyVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy.vm';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import type { FarmacyRoleItem } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import BaseModal from '@modules/Core/shared/Components/common/BaseModal.vue';

const props = defineProps<{
    roles: FarmacyRoleItem[];
}>();

const vm = useFarmacyVM();
vm.initRoleOptions(props.roles);

const helpVm = useFormHelpVM(`form-help-${useId()}`);

useFormShortcuts({
    helpVm,
    canSubmit: () => !vm.form.processing && vm.canManageFarmacy,
    submit: () => vm.submitForm(),
    cancel: () => vm.closeForm(),
  });

</script>

<template>
    <!-- Side Help Drawer — Teleport terpisah agar tidak tergabung dengan modal -->
    <Teleport to="body">
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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Manajemen Farmacy</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Panduan Penggunaan & Shortcut</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                        Farmacy adalah akun yang digunakan personil klinik untuk mengakses sistem dengan hak akses tertentu sesuai jabatan/fungsi.
                    </p>

                    <!-- Langkah-langkah -->
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Isi <strong>Nama Lengkap</strong> sesuai identitas resmi personil.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Masukkan <strong>Alamat Email</strong> aktif (digunakan sebagai Farmacyname).</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Tentukan <strong>Password</strong> akun (minimal 8 karakter).</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-text">Pilih <strong>Role Akses</strong> untuk membatasi izin fitur.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">5</div>
                                <div class="step-text">Klik <strong>Simpan Akun</strong> atau tekan <kbd>Ctrl + S</kbd>.</div>
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
                                <kbd>Ctrl + S</kbd>
                                <span>Simpan data</span>
                            </div>
                            <div class="shortcut-item">
                                <kbd>Esc</kbd>
                                <span>Tutup bantuan / Batal</span>
                            </div>
                            <div class="shortcut-item">
                                <kbd>F1</kbd>
                                <span>Tampilkan bantuan ini</span>
                            </div>
                            <div class="shortcut-item">
                                <kbd>Tab</kbd>
                                <span>Pindah antar field</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Modal utama via BaseModal -->
    <BaseModal @close="vm.closeForm">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <h4>
                    <i class="fa-solid" :class="vm.formMode === 'edit' ? 'fa-pen-to-square' : 'fa-Farmacy-plus'" style="color:var(--primary);"></i>
                    {{ vm.formMode === 'edit' ? 'Ubah Akun Pengguna' : 'Tambah Pengguna Baru' }}
                </h4>
                <InfoIcon @click="helpVm.toggle()" title="Bantuan Manajemen Farmacy (F1)" />
            </div>
            <button type="button" class="modal-close" @click="vm.closeForm" title="Tutup (Esc)">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
                <form @submit.prevent="vm.submitForm">
                    <div class="modal-body" style="display:flex; flex-direction:column; gap:20px;">
                        <div class="form-group">
                            <label class="form-label">
                                Nama Lengkap <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="Nama lengkap personil yang akan tampil di sistem">?</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-signature icon-left"></i>
                                <input v-model="vm.form.name" type="text" class="form-control" :class="{'is-invalid': vm.form.errors.name}" style="padding-left:40px;" placeholder="Masukkan nama lengkap..." required data-testid="input-Farmacy-name">
                            </div>
                            <div v-if="vm.form.errors.name" class="error-text">{{ vm.form.errors.name }}</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                Alamat Email <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="Email digunakan sebagai ID unik untuk login (Farmacyname)">?</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-envelope icon-left"></i>
                                <input v-model="vm.form.email" type="email" class="form-control" :class="{'is-invalid': vm.form.errors.email}" style="padding-left:40px;" placeholder="contoh: Farmacy@klinik.local" required autocomplete="off" data-testid="input-Farmacy-email">
                            </div>
                            <div v-if="vm.form.errors.email" class="error-text">{{ vm.form.errors.email }}</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Password
                                <span v-if="vm.editingFarmacy" style="font-size:11px; color:var(--muted); font-weight:normal; margin-left:4px;">(Kosongkan jika tidak ingin diubah)</span>
                                <span v-else style="color:var(--danger);">*</span>
                                <span class="field-help" title="Gunakan kombinasi huruf dan angka (Min 8 karakter)">?</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-lock icon-left"></i>
                                <input v-model="vm.form.password" type="password" class="form-control" :class="{'is-invalid': vm.form.errors.password}" style="padding-left:40px;" :required="!vm.editingFarmacy" placeholder="••••••••" autocomplete="new-password" data-testid="input-Farmacy-password">
                            </div>
                            <div v-if="vm.form.errors.password" class="error-text">{{ vm.form.errors.password }}</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="display:flex; justify-content:space-between; align-items:center;">
                                <span>
                                    Role Akses <span style="color:var(--danger);">*</span>
                                    <span class="field-help" title="Pilih satu atau lebih role untuk Farmacy ini">?</span>
                                </span>
                                <span v-if="vm.form.role_uuids.length" style="font-size:11px; color:var(--primary); font-weight:700;">
                                    {{ vm.form.role_uuids.length }} dipilih
                                </span>
                            </label>

                            <!-- Search box -->
                            <div class="role-search-wrap">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input
                                    v-model="vm.roleSearch"
                                    type="text"
                                    placeholder="Cari role..."
                                    class="role-search-input"
                                    data-testid="input-search-role"
                                    autocomplete="off"
                                />
                                <button
                                    v-if="vm.roleSearch"
                                    type="button"
                                    class="role-search-clear"
                                    @click="vm.roleSearch = ''"
                                    title="Hapus pencarian"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <!-- Checkbox list -->
                            <div class="role-checkbox-list" :class="{'is-invalid': vm.form.errors.role_uuids}" data-testid="select-Farmacy-role">
                                <template v-if="vm.filteredRoles.length">
                                    <label
                                        v-for="role in vm.filteredRoles"
                                        :key="role.uuid"
                                        class="role-checkbox-item"
                                        :class="{ 'selected': vm.form.role_uuids.includes(role.uuid) }"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="role.uuid"
                                            v-model="vm.form.role_uuids"
                                            :data-testid="`role-checkbox-${role.uuid}`"
                                        />
                                        <i class="fa-solid fa-shield-halved"></i>
                                        <span>{{ role.label }}</span>
                                        <span style="margin-left:auto; font-size:10px; color:var(--muted); font-weight:600; text-transform:uppercase; opacity:0.6;">{{ role.name }}</span>
                                    </label>
                                </template>
                                <div v-else class="role-empty">
                                    <i class="fa-solid fa-search" style="opacity:0.3;"></i>
                                    <span>Tidak ada role yang cocok</span>
                                </div>
                            </div>
                            <div v-if="vm.form.errors.role_uuids" class="error-text">{{ vm.form.errors.role_uuids }}</div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="vm.closeForm" data-testid="btn-cancel-Farmacy" title="Batal (Esc)">Batal (Esc)</button>
                        <button type="submit" class="btn btn-primary" :disabled="vm.form.processing || !vm.canManageFarmacy" data-testid="btn-submit-Farmacy" title="Simpan Perubahan (Ctrl+S)">
                            <i class="fa-solid fa-save"></i>
                            {{ vm.form.processing ? 'Menyimpan...' : (vm.formMode === 'edit' ? 'Simpan Perubahan' : 'Buat Akun') + ' (Ctrl+S)' }}
                        </button>
                    </div>
                </form>
    </BaseModal>
</template>

<style src="../../styles/farmacy-form.css"></style>
