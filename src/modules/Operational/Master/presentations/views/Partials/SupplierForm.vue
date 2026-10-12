<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { useId } from 'vue';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import BaseModal from "@modules/Core/shared/Components/common/BaseModal.vue";
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import { useSupplierVM } from "@modules/Operational/Master/presentations/viewmodels/supplier.vm";

const vm = useSupplierVM();
const helpVm = useFormHelpVM(`form-help-${useId()}`);

useFormShortcuts({
    helpVm,
    cancel: () => vm.closeForm(),
  });

</script>

<template>
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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Supplier</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Panduan Penggunaan & Shortcut</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                        Supplier digunakan untuk mencatat pemasok barang beserta kontak dan termin pembayarannya.
                    </p>
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Isi <strong>Nama Supplier</strong> secara lengkap.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Lengkapi <strong>Telepon</strong>, <strong>Email</strong>, dan <strong>Alamat</strong>.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Isi <strong>Termin (Hari)</strong>; isi 0 untuk Cash/COD.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-text">Klik <strong>Simpan</strong> untuk menyimpan data.</div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-keyboard"></i>Keyboard Shortcuts:
                        </h6>
                        <div class="shortcut-list">
                            <div class="shortcut-item">
                                <kbd>Esc</kbd>
                                <span>Tutup / Batal</span>
                            </div>
                            <div class="shortcut-item">
                                <kbd>F1</kbd>
                                <span>Buka/tutup bantuan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
    <BaseModal @close="vm.closeForm">
        <div class="modal-header">
            <h4>
                <i class="fa-solid fa-truck" style="color:var(--primary);"></i>
                {{ vm.editingItem ? "Edit Supplier" : "Tambah Supplier" }}
                <InfoIcon @click="helpVm.toggle()" title="Bantuan Manajemen Supplier (F1)" />
            </h4>
            <button type="button" class="modal-close" @click="vm.closeForm"><i class="fa-solid fa-times"></i></button>
        </div>
        <form @submit.prevent="vm.submitForm">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Supplier <span class="required">*</span></label>
                    <input v-model="vm.form.name" type="text" class="form-input" :class="{ 'is-error': vm.form.errors.name }" placeholder="Contoh: PT Maju Jaya" data-testid="input-supplier-name" />
                    <span v-if="vm.form.errors.name" class="form-error">{{ vm.form.errors.name }}</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Telepon</label>
                    <input v-model="vm.form.phone" type="text" class="form-input" placeholder="Contoh: 021-5551234" data-testid="input-supplier-phone" />
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input v-model="vm.form.email" type="email" class="form-input" data-testid="input-supplier-email" />
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <textarea v-model="vm.form.address" class="form-input" rows="2" placeholder="Alamat lengkap supplier"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Termin (Hari)</label>
                    <input v-model.number="vm.form.term_days" type="number" min="0" class="form-input" :class="{ 'is-error': vm.form.errors.term_days }" placeholder="0 = Cash/COD" data-testid="input-supplier-term-days" />
                    <span v-if="vm.form.errors.term_days" class="form-error">{{ vm.form.errors.term_days }}</span>
                </div>
                <div class="checkbox-row">
                    <label class="checkbox-label"><input type="checkbox" v-model="vm.form.is_active" /><span>Aktif</span></label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" @click="vm.closeForm">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="vm.form.processing" data-testid="btn-submit-supplier">
                    <i class="fa-solid fa-floppy-disk"></i> {{ vm.form.processing ? "Menyimpan..." : "Simpan" }}
                </button>
            </div>
        </form>
    </BaseModal>
</template>
