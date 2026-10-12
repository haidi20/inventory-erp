<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { useId } from 'vue';
import { useLookupVM } from '@modules/Operational/Master/presentations/viewmodels/lookup.vm';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import BaseModal from '@modules/Core/shared/Components/common/BaseModal.vue';

const vm = useLookupVM();
const helpVm = useFormHelpVM(`form-help-${useId()}`);

useFormShortcuts({
    helpVm,
    submit: () => vm.submitForm(),
    cancel: () => vm.closeForm(),
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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Manajemen Lookup</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Panduan Penggunaan & Shortcut</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                        Lookup Set digunakan sebagai wadah untuk daftar nilai (dropdown) di berbagai bagian aplikasi.
                    </p>

                    <!-- Langkah-langkah -->
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Isi <strong>Nama Lookup</strong> (misal: Status Pasien).</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Isi <strong>Kode Program</strong> jika diperlukan (misal: STATUS_PASIEN).</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Klik <strong>Simpan</strong> atau tekan <kbd>Ctrl + S</kbd>.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-text">Setelah disimpan, Anda bisa menambahkan nilai di dalam set ini.</div>
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

    <BaseModal @close="vm.closeForm">
                <div class="modal-header">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h4>
                            <i class="fa-solid" :class="vm.formMode === 'edit' ? 'fa-pen-to-square' : 'fa-folder-plus'" style="color:var(--primary);"></i>
                            {{ vm.formMode === 'edit' ? 'Ubah Tipe Lookup' : 'Tambah Tipe Lookup' }}
                        </h4>
                        <InfoIcon @click="helpVm.toggle()" title="Bantuan Manajemen Lookup (F1)" />
                    </div>
                    <button type="button" class="modal-close" @click="vm.closeForm" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="vm.submitForm">
                    <div class="modal-body" style="display:flex; flex-direction:column; gap:20px;">
                        <div class="form-group">
                            <label class="form-label">
                                Nama Lookup <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="Nama deskriptif untuk set data ini">?</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-folder-tree icon-left"></i>
                                <input v-model="vm.form.name" type="text" class="form-control" :class="{'is-invalid': vm.form.errors.name}" style="padding-left:40px;" placeholder="Masukkan nama lookup..." required data-testid="input-lookup-name">
                            </div>
                            <div v-if="vm.form.errors.name" class="error-text">{{ vm.form.errors.name }}</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Kode Program
                                <span class="field-help" title="ID unik untuk integrasi kode program (tanpa spasi)">?</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-code icon-left"></i>
                                <input v-model="vm.form.program_code" type="text" class="form-control" :class="{'is-invalid': vm.form.errors.program_code}" style="padding-left:40px;" placeholder="Contoh: MASTER-BARANG" data-testid="input-lookup-program-code">
                            </div>
                            <div v-if="vm.form.errors.program_code" class="error-text">{{ vm.form.errors.program_code }}</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Keterangan
                                <span class="field-help" title="Deskripsi singkat mengenai set lookup ini">?</span>
                            </label>
                            <textarea v-model="vm.form.description" class="form-control" rows="2" placeholder="Tambahkan keterangan jika perlu..." data-testid="input-lookup-description"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Mode Edit
                                <span class="field-help" title="EDITABLE: user bisa tambah/edit nilai. READONLY: nilai dikunci sistem">?</span>
                            </label>
                            <select v-model="vm.form.user_can_edit_flag" class="form-control" data-testid="select-lookup-edit-flag">
                                <option value="Y">EDITABLE</option>
                                <option value="N">READONLY</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="vm.closeForm" data-testid="btn-cancel-lookup" title="Batal (Esc)">Batal (Esc)</button>
                        <button type="submit" class="btn btn-primary" :disabled="vm.form.processing || !vm.canManageLookups" data-testid="btn-submit-lookup" title="Simpan Perubahan (Ctrl+S)">
                            <i class="fa-solid fa-save"></i>
                            {{ vm.form.processing ? 'Menyimpan...' : (vm.formMode === 'edit' ? 'Simpan Perubahan' : 'Buat Lookup') + ' (Ctrl+S)' }}
                        </button>
                    </div>
                </form>
    </BaseModal>
</template>

<style scoped>
.input-with-icon { position: relative; display: flex; align-items: center; }
.icon-left { position: absolute; left: 16px; color: var(--muted); font-size: 14px; opacity: 0.5; }
.error-text { color: var(--danger); font-size: 11px; margin-top: 4px; font-weight: 600; }
.form-control.is-invalid { border-color: var(--danger); }

.field-help {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    font-size: 9px;
    font-weight: bold;
    cursor: help;
    margin-left: 4px;
    vertical-align: middle;
}

@media (max-width: 640px) {
    .help-grid { grid-template-columns: 1fr; gap: 10px; }
}
</style>
