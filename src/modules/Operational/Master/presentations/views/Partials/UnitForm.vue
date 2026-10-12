<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { useId } from 'vue';
import { useUnitVM } from '@modules/Operational/Master/presentations/viewmodels/unit.vm';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import BaseModal from '@modules/Core/shared/Components/common/BaseModal.vue';

const vm = useUnitVM();
const helpVm = useFormHelpVM(`form-help-${useId()}`);

useFormShortcuts({
    helpVm,
    canSubmit: () => !vm.form.processing && vm.canManageUnits,
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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Manajemen Satuan</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Panduan Penggunaan & Shortcut</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                        Satuan digunakan untuk mendefinisikan unit pengukuran barang atau jasa di sistem.
                    </p>

                    <!-- Langkah-langkah -->
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Isi <strong>Kode Satuan</strong> (misal: PCS, BOX, STRIP).</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Isi <strong>Nama Satuan</strong> secara lengkap (misal: Pieces).</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Tambahkan <strong>Simbol</strong> jika diperlukan (misal: pcs).</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-text">Klik <strong>Simpan</strong> atau tekan <kbd>Ctrl + S</kbd>.</div>
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
                                <span>Tutup / Batal</span>
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
                            <i class="fa-solid" :class="vm.formMode === 'edit' ? 'fa-pen-to-square' : 'fa-ruler-combined'" style="color:var(--primary);"></i>
                            {{ vm.formMode === 'edit' ? 'Ubah Satuan' : 'Tambah Satuan Baru' }}
                        </h4>
                        <InfoIcon @click="helpVm.toggle()" title="Bantuan Manajemen Satuan (F1)" />
                    </div>
                    <button type="button" class="modal-close" @click="vm.closeForm" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="vm.submitForm">
                    <div class="modal-body" style="display:flex; flex-direction:column; gap:20px;">
                        <div class="form-group">
                            <label class="form-label">
                                Kode Satuan <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="Kode singkat satuan (maks 10 karakter)">?</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-hashtag icon-left"></i>
                                <input v-model="vm.form.code" type="text" class="form-control" :class="{ 'is-invalid': vm.form.errors.code }" style="padding-left:40px;" placeholder="Contoh: PCS" required autocomplete="off" data-testid="input-unit-code">
                            </div>
                            <div v-if="vm.form.errors.code" class="error-text">{{ vm.form.errors.code }}</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Nama Satuan <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="Nama lengkap satuan yang mudah dimengerti">?</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-signature icon-left"></i>
                                <input v-model="vm.form.name" type="text" class="form-control" :class="{ 'is-invalid': vm.form.errors.name }" style="padding-left:40px;" placeholder="Contoh: Pieces" required data-testid="input-unit-name">
                            </div>
                            <div v-if="vm.form.errors.name" class="error-text">{{ vm.form.errors.name }}</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Simbol
                                <span class="field-help" title="Simbol matematis atau singkat (misal: kg, L, mg)">?</span>
                            </label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-tag icon-left"></i>
                                <input v-model="vm.form.symbol" type="text" class="form-control" :class="{ 'is-invalid': vm.form.errors.symbol }" style="padding-left:40px;" placeholder="Contoh: pcs" data-testid="input-unit-symbol">
                            </div>
                            <div v-if="vm.form.errors.symbol" class="error-text">{{ vm.form.errors.symbol }}</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Keterangan
                                <span class="field-help" title="Catatan tambahan mengenai penggunaan satuan ini">?</span>
                            </label>
                            <textarea v-model="vm.form.description" class="form-control" :class="{ 'is-invalid': vm.form.errors.description }" rows="3" placeholder="Keterangan tambahan satuan..." data-testid="input-unit-description"></textarea>
                            <div v-if="vm.form.errors.description" class="error-text">{{ vm.form.errors.description }}</div>
                        </div>

                        <label style="display:flex; align-items:center; gap:10px; font-size:13px; font-weight:700; color:var(--text);">
                            <input v-model="vm.form.is_active" type="checkbox" data-testid="checkbox-unit-active">
                            Aktif
                        </label>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="vm.closeForm" data-testid="btn-cancel-unit" title="Batal (Esc)">Batal (Esc)</button>
                        <button type="submit" class="btn btn-primary" :disabled="vm.form.processing || !vm.canManageUnits" data-testid="btn-submit-unit" title="Simpan Perubahan (Ctrl+S)">
                            <i class="fa-solid fa-save"></i>
                            {{ vm.form.processing ? 'Menyimpan...' : (vm.formMode === 'edit' ? 'Simpan Perubahan' : 'Buat Satuan') + ' (Ctrl+S)' }}
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

/* KUI Styles */

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
