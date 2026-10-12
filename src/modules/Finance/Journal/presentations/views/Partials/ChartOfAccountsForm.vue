<script setup lang="ts">
import { useId } from 'vue';
import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import BaseModal from '@modules/Core/shared/Components/common/BaseModal.vue';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import { useChartOfAccountsVM } from '@modules/Finance/Journal/presentations/viewmodels/chart_of_accounts.vm';
import { ACCOUNT_TYPE_OPTIONS } from '@modules/Finance/Journal/presentations/viewmodels/chart_of_accounts.state';

const vm = useChartOfAccountsVM();
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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Bagan Akun</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Panduan Penggunaan & Shortcut</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                        Bagan akun digunakan untuk mengelompokkan transaksi keuangan berdasarkan kategori aset, kewajiban, ekuitas, pendapatan, dan beban.
                    </p>
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Isi <strong>Kode Akun</strong> dengan nomor akun yang sesuai.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Isi <strong>Nama Akun</strong> dan pilih <strong>Tipe Akun</strong>.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Klik <strong>Simpan</strong> untuk membuat akun baru.</div>
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
    <BaseModal size="md" @close="vm.closeForm">
        <form class="account-form" novalidate @submit.prevent="vm.submitForm">
            <div class="modal-header">
                <div>
                    <h3>{{ vm.formMode === 'edit' ? 'Edit Akun' : 'Tambah Akun' }} <InfoIcon @click="helpVm.toggle()" title="Bantuan Bagan Akun (F1)" /></h3>
                    <p>Isi kode, nama, dan tipe akun.</p>
                </div>
                <button type="button" class="modal-close" @click="vm.closeForm" aria-label="Tutup">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="account-code">
                        Kode Akun <span class="required">*</span>
                    </label>
                    <input
                        id="account-code"
                        v-model="vm.form.code"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': vm.formErrors.code }"
                        placeholder="Contoh: 10000"
                        autocomplete="off"
                        data-testid="input-account-code"
                    >
                    <div v-if="vm.formErrors.code" class="error-text">{{ vm.formErrors.code }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="account-name">
                        Nama Akun <span class="required">*</span>
                    </label>
                    <input
                        id="account-name"
                        v-model="vm.form.name"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': vm.formErrors.name }"
                        placeholder="Contoh: KAS"
                        autocomplete="off"
                        data-testid="input-account-name"
                    >
                    <div v-if="vm.formErrors.name" class="error-text">{{ vm.formErrors.name }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="account-type">
                        Tipe Akun <span class="required">*</span>
                    </label>
                    <select
                        id="account-type"
                        v-model="vm.form.type"
                        class="form-control"
                        data-testid="select-account-type"
                    >
                        <option v-for="option in ACCOUNT_TYPE_OPTIONS" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <label class="active-check">
                    <input v-model="vm.form.is_active" type="checkbox" data-testid="checkbox-account-active">
                    Aktif
                </label>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" @click="vm.closeForm" data-testid="btn-cancel-account">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary" :disabled="vm.processing || !vm.canManageAccounts" data-testid="btn-submit-account">
                    {{ vm.processing ? 'Menyimpan...' : (vm.formMode === 'edit' ? 'Simpan Perubahan' : 'Buat Akun') }}
                </button>
            </div>
        </form>
    </BaseModal>
</template>

<style scoped>
.account-form h3 {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
}

.account-form p {
    margin: 3px 0 0;
    color: var(--muted, #64748b);
    font-size: 12px;
}

.form-group {
    margin-bottom: 14px;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    color: var(--text, #334155);
    font-size: 12px;
    font-weight: 800;
}

.required {
    color: var(--danger, #dc2626);
}

.form-control {
    width: 100%;
    min-height: 38px;
    padding: 8px 12px;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 10px;
    background: var(--card, #fff);
    color: var(--text, #334155);
    font: inherit;
    font-size: 13px;
}

.form-control.is-invalid {
    border-color: var(--danger, #dc2626);
}

.error-text {
    margin-top: 5px;
    color: var(--danger, #dc2626);
    font-size: 11px;
    font-weight: 700;
}

.active-check {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text, #334155);
    font-size: 13px;
    font-weight: 700;
}
</style>
