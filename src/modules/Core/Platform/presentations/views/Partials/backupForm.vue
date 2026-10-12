<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { useId } from 'vue';
import { useBackupVM } from '@modules/Core/Platform/presentations/viewmodels/backup.vm';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import BaseModal from '@modules/Core/shared/Components/common/BaseModal.vue';

const vm = useBackupVM();
const helpVm = useFormHelpVM(`form-help-${useId()}`);

useFormShortcuts({
    helpVm,
    canSubmit: () => !vm.isRestoring && vm.canManageBackups && Boolean(vm.form.backup_file),
    submit: () => vm.restoreBackup(),
    cancel: () => vm.closeRestore(),
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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Restore Data</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Pemulihan Database & Dokumen</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                        Restore digunakan untuk memulihkan data dari file cadangan yang telah dibuat sebelumnya.
                    </p>

                    <!-- Langkah-langkah -->
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Pilih file cadangan (.sql atau .zip) dari komputer Anda.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Centang <strong>Buat Cadangan Keamanan</strong> sebelum memproses.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Klik <strong>Mulai Restore</strong> atau tekan <kbd>Ctrl + S</kbd>.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-text">Tunggu hingga proses sinkronisasi selesai sepenuhnya.</div>
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
                                <span>Jalankan restore</span>
                            </div>
                            <div class="shortcut-item">
                                <kbd>Esc</kbd>
                                <span>Tutup bantuan / Batal</span>
                            </div>
                            <div class="shortcut-item">
                                <kbd>F1</kbd>
                                <span>Tampilkan bantuan ini</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </Teleport>
    <BaseModal @close="vm.closeRestore">
                <div class="modal-header">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h4>
                            <i class="fa-solid fa-database-import" style="color:var(--primary);"></i>
                            Restore Cadangan
                        </h4>
                        <InfoIcon @click="helpVm.toggle()" title="Bantuan Restore (F1)" />
                    </div>
                    <button type="button" class="modal-close" @click="vm.closeRestore" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="vm.restoreBackup">
                    <div class="modal-body" style="display:flex; flex-direction:column; gap:24px; padding: 24px;">
                        <div class="alert alert-warning" style="margin: 0; padding: 12px 16px; font-size: 13px; line-height: 1.6;">
                            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i>
                            <strong>Peringatan:</strong> Proses restore akan <strong>menghapus data saat ini</strong> dan menggantinya dengan data cadangan.
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Pilih File Cadangan <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="Gunakan file .sql atau .zip hasil backup sistem ini">?</span>
                            </label>
                            <input 
                                type="file" 
                                class="form-control" 
                                :class="{'is-invalid': vm.form.errors.backup_file}" 
                                accept=".sql,.zip"
                                required
                                @change="vm.setRestoreFile"
                                data-testid="input-backup-file"
                            >
                            <div v-if="vm.form.errors.backup_file" class="error-text">{{ vm.form.errors.backup_file }}</div>
                            <small class="form-text text-muted" style="margin-top: 6px; display: block;">Sistem mendukung pemulihan data aplikasi dan dokumen pendukung.</small>
                        </div>

                        <div class="form-group">
                            <div style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer;" @click="vm.form.create_pre_restore_backup = !vm.form.create_pre_restore_backup">
                                <input type="checkbox" v-model="vm.form.create_pre_restore_backup" style="margin-top: 4px;" data-testid="checkbox-pre-restore-backup">
                                <div>
                                    <div style="display: flex; align-items: center;">
                                        <div style="font-size: 13px; font-weight: 700;">Buat Cadangan Keamanan</div>
                                        <span class="field-help" title="Mencegah kehilangan data jika proses restore gagal">?</span>
                                    </div>
                                    <div style="font-size: 11.5px; color: var(--muted); margin-top: 2px;">Secara otomatis mencadangkan database saat ini sebelum proses restore dimulai.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="vm.closeRestore" title="Batal (Esc)">Batal (Esc)</button>
                        <button type="submit" class="btn btn-primary" :disabled="vm.isRestoring || !vm.canManageBackups || !vm.form.backup_file" data-testid="btn-submit-restore" title="Mulai Restore (Ctrl+S)">
                            <i class="fa-solid fa-rotate-left"></i>
                            {{ vm.isRestoring ? 'Memproses Restore...' : 'Mulai Restore (Ctrl+S)' }}
                        </button>
                    </div>
                </form>
    </BaseModal>
</template>

<style scoped>
.error-text { color: var(--danger); font-size: 11px; margin-top: 4px; font-weight: 600; }
.form-control.is-invalid { border-color: var(--danger); }
.alert { border-radius: 8px; background: rgba(245, 158, 11, 0.1); color: #92400e; border: 1px solid rgba(245, 158, 11, 0.2); }

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
