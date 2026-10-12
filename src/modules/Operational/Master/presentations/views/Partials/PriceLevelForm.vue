<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { useId } from 'vue';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import BaseModal from '@modules/Core/shared/Components/common/BaseModal.vue';

const props = defineProps<{ vm: any }>();

const helpVm = useFormHelpVM(`form-help-${useId()}`);

useFormShortcuts({
    helpVm,
    canSubmit: () => Boolean(props.vm && !props.vm?.form?.processing && props.vm?.canManage),
    submit: () => props.vm?.submitForm(),
    cancel: () => props.vm?.closeForm(),
  });

</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="helpVm.isVisible" class="drawer-overlay" style="z-index:3100;" @click="helpVm.close()"></div>
        </Transition>
        <Transition name="slide-right">
            <div v-if="helpVm.isVisible" class="help-drawer" style="z-index:3200;">
                <div class="help-drawer-header">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="help-drawer-icon"><i class="fa-solid fa-circle-info"></i></div>
                        <div>
                            <h4 style="margin:0;font-size:16px;font-weight:800;color:#1e293b;">Bantuan: Level Harga</h4>
                            <p style="margin:0;font-size:12px;color:#64748b;">Panduan pengisian form</p>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" title="Tutup (Esc)" @click="helpVm.close()">
                        <i class="fa-solid fa-xmark" style="color:#64748b;"></i>
                    </button>
                </div>
                <div class="help-drawer-body">
                    <div style="padding:16px;">
                        <div class="help-section-title">Panduan Pengisian</div>
                        <p style="font-size:13px;color:#475569;line-height:1.6;">
                            <strong>Kode</strong> � identifikasi unik level harga (contoh: RETAIL, GROSIR).<br>
                            <strong>Nama</strong> � label yang tampil di form penjualan.<br>
                            <strong>Keterangan</strong> � deskripsi opsional.
                        </p>
                        <div style="margin-top:16px;font-size:12px;color:#94a3b8;">
                            <kbd>Ctrl+S</kbd> Simpan &nbsp; <kbd>Esc</kbd> Tutup &nbsp; <kbd>F1</kbd> Bantuan
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <BaseModal :show="vm?.showForm" @close="vm?.closeForm()">
        <template #title>
            <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
                <span>{{ vm?.editingItem ? 'Ubah Level Harga' : 'Tambah Level Harga' }}</span>
                <InfoIcon @click="helpVm.toggle()" style="cursor:pointer;" />
            </div>
        </template>

        <form @submit.prevent="vm?.submitForm()">
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Kode <span style="color:#ef4444;">*</span></label>
                <input v-model="vm.form.code" type="text" class="form-control" placeholder="Contoh: RETAIL" required autofocus />
                <div v-if="vm.form.errors?.code" class="form-error">{{ vm.form.errors.code }}</div>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Nama <span style="color:#ef4444;">*</span></label>
                <input v-model="vm.form.name" type="text" class="form-control" placeholder="Contoh: Harga Retail" required />
                <div v-if="vm.form.errors?.name" class="form-error">{{ vm.form.errors.name }}</div>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Keterangan</label>
                <textarea v-model="vm.form.description" class="form-control" rows="3" placeholder="Keterangan opsional..." />
            </div>
            <div class="form-group" style="margin-bottom:24px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" v-model="vm.form.is_active" />
                    <span>Aktif</span>
                </label>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:12px;">
                <button type="button" class="btn btn-outline" @click="vm?.closeForm()">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="vm.form.processing">
                    <i v-if="vm.form.processing" class="fa-solid fa-spinner fa-spin"></i>
                    {{ vm?.editingItem ? 'Simpan Perubahan' : 'Tambah Data' }}
                </button>
            </div>
        </form>
    </BaseModal>
</template>
