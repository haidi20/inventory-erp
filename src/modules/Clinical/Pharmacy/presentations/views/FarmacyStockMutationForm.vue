<script setup lang="ts">import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';

import { onMounted, useId } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import { useFarmacyStockMutationVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_stock_mutation.vm';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import { FormMode } from '@modules/Core/shared/core/domain/models/form_mode.model';
import '@modules/Clinical/Pharmacy/presentations/styles/farmacy-stock-adjustment-form.css';

const props = defineProps<{
    mode: FormMode | 'create' | 'edit';
    uuid?: string;
}>();

const vm = useFarmacyStockMutationVM();
const helpVm = useFormHelpVM(`form-help-${useId()}`);

onMounted(async () => {
    await vm.fetch();
    await vm.initForm(props.mode, props.uuid);
});

useFormShortcuts({
    helpVm,
    cancel: () => vm.backToList(),
  });

</script>

<template>
    <Head :title="`${mode === FormMode.EDIT ? 'Ubah' : 'Tambah'} Mutasi Stok - Inventory`" />

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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Mutasi Stok</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Panduan & Shortcut</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #6366f1;">
                        Modul ini digunakan untuk memindahkan stok antar gudang di dalam unit usaha.
                    </p>

                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Pilih <strong>Gudang Asal</strong> dan <strong>Gudang Tujuan</strong>.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Tambah produk yang akan dimutasi.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Input <strong>Jumlah</strong> yang akan dipindahkan.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <div class="step-text">Pastikan stok di gudang asal mencukupi.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">5</div>
                                <div class="step-text">Simpan data atau tekan <kbd>Ctrl + S</kbd>.</div>
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

    <section class="adjustment-form-page" aria-labelledby="stock-mutation-form-title">
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
                    <Link href="/farmasi/inventory/mutations" class="breadcrumb-link">Mutasi Stok</Link>
                    <span>/</span>
                    <button type="button" class="breadcrumb-link is-active">{{ mode === FormMode.EDIT ? 'Ubah' : 'Tambah' }}</button>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                    <div id="stock-mutation-form-title" class="page-title" style="margin-bottom: 0;">
                        {{ mode === FormMode.EDIT ? 'Ubah' : 'Tambah' }} Mutasi Stok
                    </div>
                    <InfoIcon @click="helpVm.toggle()" title="Bantuan & Shortcut (F1)" />
                </div>
                <div class="page-sub">Kelola pemindahan stok antar gudang dengan detail produk dan batch.</div>
            </div>
            <Link href="/farmasi/inventory/mutations" class="btn btn-outline" title="Kembali (Esc)">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali (Esc)
            </Link>
        </div>

        <section class="adjustment-form-card" aria-labelledby="stock-mutation-general-info">
            <div class="adjustment-section-heading">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                    <h3 id="stock-mutation-general-info">Informasi Umum</h3>
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
                        :append-to-body="false"
                    />
                </label>
                <label class="form-field">
                    <span>Gudang Asal <strong>*</strong></span>
                    <Select2ClientSide
                        v-model="vm.form.sourceWarehouseId"
                        :get-data="vm.warehouseSelectOptions"
                        placeholder="Cari dan Pilih Gudang Asal"
                        :clearable="false"
                        :append-to-body="false"
                    />
                </label>
                <label class="form-field">
                    <span>Gudang Tujuan <strong>*</strong></span>
                    <Select2ClientSide
                        v-model="vm.form.destinationWarehouseId"
                        :get-data="vm.warehouseSelectOptions"
                        placeholder="Cari dan Pilih Gudang Tujuan"
                        :clearable="false"
                        :append-to-body="false"
                    />
                </label>
                <label class="form-field">
                    <span>Tanggal <strong>*</strong></span>
                    <input v-model="vm.form.transactionDate" type="text" />
                </label>
                <label class="form-field">
                    <span>No. Mutasi <strong>*</strong></span>
                    <input v-model="vm.form.referenceNo" type="text" readonly />
                </label>
                <label class="form-field form-field-wide">
                    <span>Deskripsi</span>
                    <input v-model="vm.form.description" type="text" placeholder="Contoh: Mutasi stok ke gudang apotek" />
                </label>
            </div>
        </section>

        <section class="adjustment-form-card" aria-labelledby="stock-mutation-items">
            <div class="adjustment-section-heading">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                    <h3 id="stock-mutation-items">Daftar Item Mutasi</h3>
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
                            <th style="min-width: 280px;">Nama Produk</th>
                            <th style="width: 200px;">Batch Number</th>
                            <th style="width: 140px;">Stok</th>
                            <th style="width: 140px;">Jumlah</th>
                            <th style="width: 140px;">Sisa Stok</th>
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
                                    :append-to-body="false"
                                    @change="(value) => vm.handleProductChange(item, value)"
                                />
                            </td>
                            <td>
                                <Select2ClientSide
                                    v-model="item.batchId"
                                    :get-data="vm.batchSelectOptionsForProduct(item.productId)"
                                    placeholder="Cari dan Pilih Batch"
                                    :disabled="!item.productId"
                                    :append-to-body="false"
                                    @change="(value) => vm.handleBatchChange(item, value)"
                                />
                            </td>
                            <td>
                                <input v-model.number="item.stock" type="number" readonly class="table-form-control" />
                            </td>
                            <td>
                                <input v-model.number="item.quantity" type="number" class="table-form-control" />
                            </td>
                            <td class="subtotal-cell">{{ vm.remainingStock(item) }}</td>
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

        <section class="adjustment-form-card summary-card" aria-labelledby="stock-mutation-summary">
            <div class="adjustment-section-heading">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:4px; height:18px; background:var(--primary); border-radius:4px;"></div>
                    <h3 id="stock-mutation-summary">Ringkasan</h3>
                </div>
            </div>
            <div class="summary-grid">
                <div>
                    <span>Total Item</span>
                    <strong>{{ vm.form.items.length }} item</strong>
                </div>
                <div>
                    <span>Total Kuantitas</span>
                    <strong>{{ vm.totalQuantity }}</strong>
                </div>
            </div>
        </section>

        <footer class="adjustment-form-footer">
            <div class="save-dropdown">
                <button type="button" class="btn btn-primary save-main-button" title="Simpan">
                    <i class="fa-solid fa-paper-plane"></i>
                    Simpan
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

