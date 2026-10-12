<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { onBeforeUnmount, onMounted, useId } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useFarmacyStockAdjustmentVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_stock_adjustment.vm';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import { FormMode } from '@modules/Core/shared/core/domain/models/form_mode.model';
import '@modules/Clinical/Pharmacy/presentations/styles/farmacy-stock-adjustment-form.css';

const props = defineProps<{
    mode: FormMode | 'create' | 'edit';
    uuid?: string;
}>();

const vm = useFarmacyStockAdjustmentVM();
const helpVm = useFormHelpVM(`form-help-${useId()}`);
onMounted(async () => {
    vm.initSaveMenuDismiss();
    await vm.fetch();
    await vm.initForm(props.mode, props.uuid);
});

onBeforeUnmount(() => {
    vm.disposeSaveMenuDismiss();
});

useFormShortcuts({
    helpVm,
    submit: () => vm.submitForm(),
    cancel: () => vm.backToList(),
  });

</script>

<template>
    <Head :title="`${vm.formTitle} - Inventory`" />

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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Penyesuaian Stok</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Panduan & Shortcut</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #6366f1;">
                        Modul ini digunakan untuk melakukan koreksi jumlah stok barang di gudang tertentu (tambah/kurang).
                    </p>

                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Pilih <strong>Unit Usaha</strong> dan <strong>Gudang</strong> tujuan penyesuaian.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Klik tombol <strong>Tambah</strong> untuk memasukkan baris barang baru.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Cari <strong>Produk</strong> dan pilih <strong>Batch Number</strong> yang sesuai.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-text">Input jumlah. Gunakan <strong>negatif (-)</strong> jika ingin mengurangi stok.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">5</div>
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

    <section class="adjustment-form-page" aria-labelledby="adjustment-form-title">
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
                    <Link href="/farmasi/inventory/adjustments" class="breadcrumb-link">Penyesuaian Persediaan</Link>
                    <span>/</span>
                    <button type="button" class="breadcrumb-link is-active">{{ vm.formModeLabel }}</button>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                    <div id="adjustment-form-title" class="page-title" style="margin-bottom: 0;">
                        {{ vm.formTitle }}
                    </div>
                    <InfoIcon @click="helpVm.toggle()" title="Bantuan & Shortcut (F1)" />
                </div>
                <div class="page-sub">Kelola detail penyesuaian stok dengan memilih gudang, produk, batch, harga satuan, dan jumlah.</div>
            </div>
            <Link href="/farmasi/inventory/adjustments" class="btn btn-outline" title="Kembali (Esc)">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali (Esc)
            </Link>
        </div>

        <section class="adjustment-form-card" aria-labelledby="general-info-title">
            <div class="adjustment-section-heading">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                    <h3 id="general-info-title">Informasi Umum</h3>
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
                    <span>No. Invoice <strong>*</strong></span>
                    <input v-model="vm.form.invoiceNo" type="text" readonly />
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
                    <span>Tanggal <strong>*</strong></span>
                    <input v-model="vm.form.transactionDate" type="text" />
                </label>

                <label class="form-field form-field-wide">
                    <span>Deskripsi</span>
                    <input v-model="vm.form.description" type="text" placeholder="Contoh: Penyesuaian stok bulanan" />
                </label>
            </div>
        </section>

        <div class="adjustment-info-box">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>Apabila ingin mengurangi stok barang maka gunakan negative (-) pada total.</span>
        </div>

        <section class="adjustment-form-card" aria-labelledby="item-list-title">
            <div class="adjustment-section-heading">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                    <h3 id="item-list-title">Daftar Item Penyesuaian</h3>
                </div>
                <button type="button" class="btn btn-outline btn-sm" @click="vm.addItem">
                    <i class="fa-solid fa-plus"></i>
                    Tambah
                </button>
            </div>

            <div class="table-wrap adjustment-item-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="min-width: 300px;">Nama Produk</th>
                            <th style="width: 180px;">Batch Number</th>
                            <th style="width: 150px;">Harga Satuan</th>
                            <th style="width: 130px;">Jumlah</th>
                            <th style="width: 150px;">Subtotal</th>
                            <th style="width: 80px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in vm.form.items" :key="item.uuid">
                            <td>
                                <Select2ClientSide
                                    v-model="item.productId"
                                    :get-data="vm.productSelectOptions"
                                    placeholder="Cari dan Pilih Produk"
                                    @change="vm.handleProductChange(item)"
                                />
                            </td>
                            <td>
                                <Select2ClientSide
                                    v-model="item.batchId"
                                    :get-data="vm.batchSelectOptionsForProduct(item.productId)"
                                    placeholder="Pilih Batch"
                                    :disabled="!item.productId"
                                />
                            </td>
                            <td>
                                <input v-model.number="item.unitPrice" type="number" min="0" class="table-form-control" />
                            </td>
                            <td>
                                <div class="quantity-control">
                                    <button type="button" @click="vm.decrementQuantity(item)">-</button>
                                    <input v-model.number="item.quantity" type="number" />
                                    <button type="button" @click="vm.incrementQuantity(item)">+</button>
                                </div>
                            </td>
                            <td class="subtotal-cell">{{ vm.formatCurrency(vm.getItemSubtotal(item)) }}</td>
                            <td style="text-align: center;">
                                <button type="button" class="btn btn-outline btn-sm btn-danger-soft" title="Hapus item" @click="vm.removeItem(item.uuid)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="adjustment-form-card summary-card" aria-labelledby="summary-title">
            <div class="adjustment-section-heading">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                    <h3 id="summary-title">Ringkasan</h3>
                </div>
            </div>
            <div class="summary-grid">
                <div>
                    <span>Total Item</span>
                    <strong>{{ vm.totalFilledItems }} item</strong>
                </div>
                <div>
                    <span>Total Penyesuaian</span>
                    <strong>{{ vm.formatCurrency(vm.totalAdjustment) }}</strong>
                </div>
            </div>
        </section>

        <footer class="adjustment-form-footer">
            <div :ref="vm.setSaveDropdown" class="save-dropdown">
                <button type="button" class="btn btn-primary save-main-button" title="Simpan (Ctrl+S)">
                    <i class="fa-solid fa-paper-plane"></i>
                    Simpan (Ctrl+S)
                </button>
                <button type="button" class="btn btn-primary save-caret-button" @click.stop="vm.toggleSaveMenu">
                    <i class="fa-solid fa-caret-up"></i>
                </button>
                <div v-if="vm.showSaveMenu" class="save-dropdown-menu" @click.stop>
                    <button type="button" @click="vm.closeSaveMenu">Simpan Draft</button>
                    <button type="button" @click="vm.closeSaveMenu">Simpan</button>
                </div>
            </div>
        </footer>
    </section>
</template>

<style scoped></style>

