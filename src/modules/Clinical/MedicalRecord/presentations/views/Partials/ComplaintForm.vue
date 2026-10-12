<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { useId } from 'vue';
import { useComplaintVM } from '@modules/Clinical/MedicalRecord/presentations/viewmodels/complaint.vm';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import BaseModal from '@modules/Core/shared/Components/common/BaseModal.vue';
import { COMPLAINT_TYPES } from '@modules/Clinical/MedicalRecord/presentations/viewmodels/complaint.state';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';

const vm = useComplaintVM();
const helpVm = useFormHelpVM(`form-help-${useId()}`);

useFormShortcuts({
    helpVm,
    canSubmit: () => !vm.form.processing,
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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Pengaduan</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Aspirasi & Saran Pelayanan</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                        Layanan ini digunakan untuk menampung aspirasi, keluhan, atau saran demi perbaikan kualitas pelayanan klinik.
                    </p>

                    <!-- Tipe Pesan -->
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-tags"></i>Tipe Pesan:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">P</div>
                                <div class="step-text"><strong>Pengaduan:</strong> Masalah atau ketidakpuasan terhadap layanan.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">S</div>
                                <div class="step-text"><strong>Saran:</strong> Ide atau usulan perbaikan layanan.</div>
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
                                <span>Kirim pesan</span>
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
    <BaseModal size="lg" @close="vm.closeForm">
                <div class="modal-header">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h4>
                            <i class="fa-solid fa-bullhorn" style="color:var(--primary);"></i>
                            Kirim Pengaduan & Saran
                        </h4>
                        <InfoIcon @click="helpVm.toggle()" title="Bantuan (F1)" />
                    </div>
                    <button type="button" class="modal-close" @click="vm.closeForm">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="vm.submitForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">
                                Tipe Pesan <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="Pilih kategori pesan Anda">?</span>
                            </label>
                            <Select2ClientSide
                                v-model="vm.form.type"
                                :get-data="COMPLAINT_TYPES"
                                :clearable="false"
                                placeholder="Pilih Tipe"
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Subjek <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="Ringkasan singkat pesan (misal: Antrian Terlalu Lama)">?</span>
                            </label>
                            <input v-model="vm.form.subject" type="text" class="form-control" placeholder="Tuliskan subjek..." required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Isi Pesan <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="Detail kejadian atau usulan Anda">?</span>
                            </label>
                            <textarea v-model="vm.form.content" class="form-control" rows="4" placeholder="Ceritakan detailnya di sini..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="perm-item" style="cursor:pointer; display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" v-model="vm.form.is_anonymous">
                                <div>
                                    <div class="perm-name">Kirim secara anonim</div>
                                    <div class="perm-label">Identitas Anda tidak akan ditampilkan ke staf.</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="vm.closeForm">Batal (Esc)</button>
                        <button type="submit" class="btn btn-primary" :disabled="vm.form.processing">
                            <i class="fa-solid fa-paper-plane"></i>
                            {{ vm.form.processing ? 'Mengirim...' : 'Kirim Pesan (Ctrl+S)' }}
                        </button>
                    </div>
                </form>
    </BaseModal>
</template>

<style scoped>
/* Mewarisi gaya KUI dari RoleForm */
.field-help { display: inline-flex; align-items: center; justify-content: center; width: 14px; height: 14px; border-radius: 50%; background: #f1f5f9; color: #64748b; font-size: 9px; font-weight: bold; cursor: help; margin-left: 4px; vertical-align: middle; }
.perm-item { padding: 10px; border: 1px solid var(--border); border-radius: 8px; background: #f8fafc; }
.perm-name { font-size: 12px; font-weight: 800; color: var(--text); }
.perm-label { font-size: 10px; color: var(--muted); }

@media (max-width: 640px) {
    .help-grid { grid-template-columns: 1fr; gap: 10px; }
}

:deep(.vs__dropdown-toggle) {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid var(--border);
    min-height: 42px;
}
</style>
