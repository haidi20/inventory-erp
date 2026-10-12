<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { useId } from 'vue';
import { useLookupValueVM } from '@modules/Operational/Master/presentations/viewmodels/lookup_value.vm';
import { LOOKUP_VALUE_PRIMARY_OPTIONS, LOOKUP_VALUE_STATUS_OPTIONS } from '@modules/Operational/Master/presentations/viewmodels/lookup_value.state';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import BaseModal from '@modules/Core/shared/Components/common/BaseModal.vue';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';

const vm = useLookupValueVM();
const helpVm = useFormHelpVM(`form-help-${useId()}`);

const statusOptions = LOOKUP_VALUE_STATUS_OPTIONS;
const primaryOptions = LOOKUP_VALUE_PRIMARY_OPTIONS;

useFormShortcuts({
    helpVm,
    canSubmit: () => !vm.isSaving,
    submit: () => vm.save(),
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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Nilai Lookup</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Panduan Penggunaan & Shortcut</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                        Nilai lookup adalah pilihan data yang akan muncul di dropdown sistem (misal: Status Aktif, Kategori, dll).
                    </p>

                    <!-- Langkah-langkah -->
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Isi <strong>Nama Tampilan</strong> yang akan dilihat oleh pengguna.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Isi <strong>Kode Program</strong> untuk identitas internal sistem.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Tentukan <strong>Urutan</strong> pemunculan (kecil ke besar).</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-text">Klik <strong>Simpan Nilai</strong> atau tekan <kbd>Ctrl + S</kbd>.</div>
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

    <BaseModal @close="vm.showForm.value = false">
                <div class="modal-header">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="modal-title">{{ vm.formMode === 'edit' ? 'Ubah Nilai Lookup' : 'Tambah Nilai Lookup' }}</h3>
                        <InfoIcon @click="helpVm.toggle()" title="Bantuan Nilai Lookup (F1)" />
                    </div>
                    <button type="button" class="btn-close" @click="vm.showForm.value = false" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="vm.save">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">
                            Nama Tampilan <span class="required">*</span>
                            <span class="field-help" title="Nama yang akan muncul di pilihan dropdown">?</span>
                        </label>
                        <input
                            v-model="vm.formData.value.display_name"
                            type="text"
                            class="form-input"
                            placeholder="Contoh: Aktif, Menikah, dll"
                            required
                            data-testid="input-value-display-name"
                        >
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Kode Program (Opsional)
                            <span class="field-help" title="Kode unik untuk kebutuhan integrasi sistem">?</span>
                        </label>
                        <input
                            v-model="vm.formData.value.program_code"
                            type="text"
                            class="form-input"
                            placeholder="Contoh: ACTIVE, MARRIED"
                            data-testid="input-value-program-code"
                        >
                        <small class="form-help">Kode unik internal untuk integrasi modul.</small>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label">
                                Urutan
                                <span class="field-help" title="Nomor urut penampilan (kecil ke besar)">?</span>
                            </label>
                            <input
                                v-model="vm.formData.value.seq"
                                type="number"
                                class="form-input"
                                data-testid="input-value-seq"
                            >
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Status
                                <span class="field-help" title="Menentukan apakah nilai ini bisa dipilih atau tidak">?</span>
                            </label>
                            <Select2ClientSide
                                v-model="vm.formData.value.active_flag"
                                :get-data="statusOptions"
                                :clearable="false"
                                placeholder="Pilih Status"
                                data-testid="select-value-status"
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            Keterangan
                            <span class="field-help" title="Catatan tambahan mengenai nilai ini">?</span>
                        </label>
                        <textarea
                            v-model="vm.formData.value.description"
                            class="form-input"
                            rows="2"
                            placeholder="Tambahkan catatan jika perlu..."
                            data-testid="input-value-description"
                        ></textarea>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label">
                                Sejak Tanggal
                                <span class="field-help" title="Tanggal mulai berlaku nilai ini">?</span>
                            </label>
                            <input
                                v-model="vm.formData.value.start_date"
                                type="date"
                                class="form-input"
                                data-testid="input-value-start-date"
                            >
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Sampai Tanggal
                                <span class="field-help" title="Tanggal berakhir berlaku (kosong = tidak ada batas)">?</span>
                            </label>
                            <input
                                v-model="vm.formData.value.end_date"
                                type="date"
                                class="form-input"
                                data-testid="input-value-end-date"
                            >
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label class="form-label">
                                Stok Maksimum
                                <span class="field-help" title="Batas maksimum stok untuk nilai ini">?</span>
                            </label>
                            <input
                                v-model="vm.formData.value.max_stock"
                                type="number"
                                step="0.001"
                                class="form-input"
                                placeholder="0"
                                data-testid="input-value-max-stock"
                            >
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Default
                                <span class="field-help" title="Tandai sebagai nilai default/primary">?</span>
                            </label>
                            <Select2ClientSide
                                v-model="vm.formData.value.primary_flag"
                                :get-data="primaryOptions"
                                :clearable="false"
                                placeholder="Pilih"
                                data-testid="select-value-primary-flag"
                            />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" @click="vm.showForm.value = false" title="Batal (Esc)">Batal (Esc)</button>
                    <button type="submit" class="btn btn-primary" :disabled="vm.isSaving.value" data-testid="btn-save-value" title="Simpan Perubahan (Ctrl+S)">
                        <i class="fa-solid fa-save"></i>
                        {{ vm.isSaving.value ? 'Menyimpan...' : (vm.formMode === 'edit' ? 'Simpan Perubahan' : 'Simpan Nilai') + ' (Ctrl+S)' }}
                    </button>
                </div>
            </form>
    </BaseModal>
</template>

<style scoped>
.modal-header {
    padding: 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title { font-size: 18px; font-weight: 800; color: var(--text); }

.modal-body { padding: 24px; display: grid; gap: 20px; }

.modal-footer {
    padding: 24px;
    background: #f8fafc;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.form-group { display: grid; gap: 8px; }
.form-label { font-size: 13px; font-weight: 700; color: var(--text); }
.required { color: #ef4444; }
.form-help { font-size: 11px; color: var(--muted); }

.form-input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid var(--border);
    border-radius: 12px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
}

.btn-close {
    background: transparent;
    border: none;
    font-size: 20px;
    color: var(--muted);
    cursor: pointer;
    transition: color 0.15s ease;
}

.btn-close:hover { color: var(--text); }

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

:deep(.vs__dropdown-toggle) {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid var(--border);
    min-height: 42px;
}
</style>
