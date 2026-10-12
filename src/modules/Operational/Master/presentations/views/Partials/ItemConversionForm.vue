<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { useId } from 'vue';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import BaseModal from '@modules/Core/shared/Components/common/BaseModal.vue';

const props = defineProps<{
    vm: any;
}>();

const vm = props.vm;
const helpVm = useFormHelpVM(`form-help-${useId()}`);

useFormShortcuts({
    helpVm,
    submit: () => vm.submitConversionForm(),
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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Konversi</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Hubungan Antar Satuan</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                        Konversi mendefinisikan hubungan antar satuan untuk memudahkan pengelolaan stok (misal: 1 BOX = 10 PCS).
                    </p>

                    <!-- Langkah-langkah -->
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Pilih <strong>Produk</strong> (opsional jika berlaku global).</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Tentukan <strong>Satuan Asal</strong> (lebih besar) dan <strong>Tujuan</strong>.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Isi <strong>Rasio</strong> (jumlah tujuan di dalam 1 asal).</div>
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
    <BaseModal @close="emit('close')">
                <div class="modal-header">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h4>
                            <i class="fa-solid fa-arrows-rotate" style="color:var(--primary);"></i>
                            {{ vm.formMode === 'edit' ? 'Ubah Konversi' : 'Tambah Konversi Baru' }}
                        </h4>
                        <InfoIcon @click="helpVm.toggle()" title="Bantuan Konversi (F1)" />
                    </div>
                        <button type="button" class="modal-close" @click="vm.closeForm()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form @submit.prevent="vm.submitConversionForm">
                    <div class="modal-body" style="display:flex; flex-direction:column; gap:20px;">
                        <div class="form-group">
                            <label class="form-label">
                                Pilih Produk (Opsional)
                                <span class="field-help" title="Jika dikosongkan, konversi berlaku untuk semua produk dengan satuan ini">?</span>
                            </label>
                            <input v-model="vm.form.item_uuid" type="text" class="form-control" placeholder="ID Produk (UUID) - Kosongkan untuk Global" data-testid="input-conversion-item">
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                            <div class="form-group">
                                <label class="form-label">
                                    Satuan Asal <span style="color:var(--danger);">*</span>
                                    <span class="field-help" title="Satuan yang lebih besar (misal: BOX)">?</span>
                                </label>
                                <input v-model="vm.form.from_unit_uuid" type="text" class="form-control" required placeholder="UUID Satuan" data-testid="input-conversion-from">
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    Satuan Tujuan <span style="color:var(--danger);">*</span>
                                    <span class="field-help" title="Satuan yang lebih kecil (misal: PCS)">?</span>
                                </label>
                                <input v-model="vm.form.to_unit_uuid" type="text" class="form-control" required placeholder="UUID Satuan" data-testid="input-conversion-to">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Rasio Konversi <span style="color:var(--danger);">*</span>
                                <span class="field-help" title="Jumlah satuan tujuan dalam 1 satuan asal">?</span>
                            </label>
                            <input v-model.number="vm.form.conv_ratio" type="number" step="0.00001" class="form-control" required data-testid="input-conversion-ratio">
                            <div style="font-size:11px; color:var(--muted); margin-top:4px;">Contoh: 1 Box = 10 Strip, maka rasionya adalah 10.</div>
                        </div>

                        <label style="display:flex; align-items:center; gap:10px; font-size:13px; font-weight:700; color:var(--text);">
                            <input v-model="vm.form.is_active" type="checkbox" data-testid="checkbox-conversion-active">
                            Status Aktif
                        </label>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" @click="vm.closeForm()" title="Batal (Esc)">Batal (Esc)</button>
                        <button type="submit" class="btn btn-primary" title="Simpan Konversi (Ctrl+S)">
                            <i class="fa-solid fa-save"></i>
                            {{ vm.formMode === 'edit' ? 'Simpan Perubahan' : 'Simpan Konversi' }} (Ctrl+S)
                        </button>
                    </div>
                    </form>
    </BaseModal>
</template>

                    <style scoped>
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
                    </style>
