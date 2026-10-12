<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { onMounted, useId } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useFarmacyOpeningBalanceVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_opening_balance.vm';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import { FormMode } from '@modules/Core/shared/core/domain/models/form_mode.model';
import '@modules/Clinical/Pharmacy/presentations/styles/farmacy-stock-adjustment-form.css';

const props = defineProps<{
    mode: FormMode | 'create' | 'edit';
    uuid?: string;
}>();

const vm = useFarmacyOpeningBalanceVM();
const helpVm = useFormHelpVM(`form-help-${useId()}`);

onMounted(async () => {
    await vm.fetch();
    await vm.initForm(props.mode, props.uuid);
});

useFormShortcuts({
    helpVm,
    submit: () => vm.submitForm(),
    cancel: () => vm.cancelForm(),
  });

</script>

<template>
    <Head :title="`${mode === FormMode.EDIT ? 'Ubah' : 'Tambah'} Saldo Awal - Inventory`" />

    <!-- Side Help Drawer -->
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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Saldo Awal</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Panduan & Shortcut</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #6366f1;">
                        Modul ini digunakan untuk mencatat saldo awal persediaan produk saat pertama kali menggunakan sistem.
                    </p>

                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Pilih <strong>Unit Usaha</strong>, <strong>Produk</strong>, dan <strong>Gudang</strong>.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Input <strong>Harga Beli</strong> (satuan) dan <strong>Total Stok</strong> awal.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">(Opsional) Input <strong>Batch Number</strong> dan <strong>Tanggal Kadaluarsa</strong>.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-text">Klik <strong>Simpan</strong> atau tekan <kbd>Ctrl + S</kbd>.</div>
                            </div>
                        </div>
                    </div>

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
                                <span>Tutup bantuan / Kembali</span>
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

    <section class="adjustment-form-page" aria-labelledby="opening-balance-form-title">
        <div class="page-header" style="margin-bottom: 32px;">
            <div>
                <div class="breadcrumb">
                    <Link href="/" class="breadcrumb-link">
                        <i class="fa-solid fa-house"></i>
                    </Link>
                    <span>/</span>
                    <Link href="/farmasi/dashboard" class="breadcrumb-link">Farmasi</Link>
                    <span>/</span>
                    <Link href="/farmasi/inventory" class="breadcrumb-link">Inventori</Link>
                    <span>/</span>
                    <Link href="/farmasi/inventory/opening-balance" class="breadcrumb-link">Saldo Awal Persediaan</Link>
                    <span>/</span>
                    <button type="button" class="breadcrumb-link is-active">{{ mode === FormMode.EDIT ? 'Ubah' : 'Tambah' }}</button>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                    <div id="opening-balance-form-title" class="page-title" style="margin-bottom: 0;">
                        {{ mode === FormMode.EDIT ? 'âœï¸ Ubah' : 'âœï¸ Tambah' }} Saldo Awal Produk
                    </div>
                    <InfoIcon @click="helpVm.toggle()" title="Bantuan & Shortcut (F1)" />
                </div>
                <div class="page-sub">Input saldo awal per produk sebelum sistem digunakan.</div>
            </div>
            <Link href="/farmasi/inventory/opening-balance" class="btn btn-outline" title="Kembali (Esc)">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali (Esc)
            </Link>
        </div>

        <section class="adjustment-form-card" aria-labelledby="form-fields-title">
            <div class="adjustment-section-heading">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                    <h3 id="form-fields-title">Data Saldo Awal</h3>
                </div>
            </div>

            <div class="adjustment-form-grid">
                <label class="form-field">
                    <span>Unit Usaha <strong>*</strong></span>
                    <Select2ClientSide
                        v-model="vm.form.businessUnit"
                        :get-data="vm.businessUnitSelectOptions"
                        placeholder="Pilih Unit Usaha"
                        :clearable="false"
                    />
                </label>

                <label class="form-field">
                    <span>Batch Number</span>
                    <input v-model="vm.form.batchNumber" type="text" placeholder="Masukkan nomor batch" />
                </label>

                <label class="form-field">
                    <span>Produk <strong>*</strong></span>
                    <Select2ClientSide
                        v-model="vm.form.productId"
                        :get-data="vm.productSelectOptions"
                        placeholder="Cari dan Pilih Produk"
                        :clearable="false"
                    />
                </label>

                <label class="form-field">
                    <span>Gudang <strong>*</strong></span>
                    <Select2ClientSide
                        v-model="vm.form.warehouseId"
                        :get-data="vm.warehouseSelectOptions"
                        placeholder="Cari dan Pilih Gudang"
                        :clearable="false"
                    />
                </label>

                <label class="form-field">
                    <span>Tanggal Kadaluarsa</span>
                    <input v-model="vm.form.expiryDate" type="date" />
                </label>

                <label class="form-field">
                    <span>Harga Beli <strong>*</strong></span>
                    <div class="input-with-icon">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--muted); pointer-events: none;">Rp</span>
                        <input v-model.number="vm.form.purchasePrice" type="number" style="padding-left: 40px;" min="0" />
                    </div>
                </label>

                <label class="form-field">
                    <span>Total Stok <strong>*</strong></span>
                    <input v-model.number="vm.form.totalStock" type="number" min="0" />
                </label>
            </div>
        </section>

        <section class="adjustment-form-card summary-card" aria-labelledby="summary-title">
            <div class="adjustment-section-heading">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                    <h3 id="summary-title">ðŸ’° Ringkasan</h3>
                </div>
            </div>
            <div class="summary-grid">
                <div>
                    <span>Total Stok</span>
                    <strong>{{ vm.form.totalStock }}</strong>
                </div>
                <div>
                    <span>Total Asset</span>
                    <strong>{{ vm.formatCurrency(vm.totalAsset) }}</strong>
                </div>
            </div>
        </section>

        <footer class="adjustment-form-footer">
            <div class="save-dropdown">
                <button type="button" class="btn btn-primary save-main-button" title="Simpan (Ctrl+S)" @click="vm.submitForm">
                    <i class="fa-solid fa-save"></i>
                    ðŸ’¾ Simpan (Ctrl+S)
                </button>
                <button type="button" class="btn btn-primary save-caret-button" @click.stop="vm.toggleSaveMenu">
                    <i class="fa-solid fa-caret-up"></i>
                </button>
                <div v-if="vm.showSaveMenu" class="save-dropdown-menu" @click.stop>
                    <button type="button" @click="vm.closeSaveMenu">Simpan Draft</button>
                    <button type="button" @click="vm.closeSaveMenu">Simpan & Selesai</button>
                </div>
            </div>
            <Link href="/farmasi/inventory/opening-balance" class="btn btn-outline" title="Batal (Esc)">
                âŒ Batal (Esc)
            </Link>
        </footer>
    </section>
</template>

<style scoped>
.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}
</style>

