<script setup lang="ts">
import { onMounted, useId } from 'vue';
import { Head } from '@inertiajs/vue3';
import AsyncSelect2 from '@modules/Core/shared/Components/common/AsyncSelect2.vue';
import InfoIcon from '@modules/Core/shared/Components/common/InfoIcon.vue';
import { useFormShortcuts } from '@modules/Core/shared/Components/common/form_shortcuts.vm';
import { useFormHelpVM } from '@modules/Core/shared/Components/common/form_help.vm';
import { useProductFormVM } from '@modules/Operational/Master/presentations/viewmodels/product_form.vm';
import type { ProductFormOptions } from '@modules/Operational/Master/presentations/viewmodels/product.state';
import type { ProductModel } from '@modules/Operational/Master/domain/models/product.model';

const props = defineProps<{
    product: ProductModel | null;
    options: ProductFormOptions;
}>();

const formVm = useProductFormVM();
const vm = formVm.productVm;

const helpVm = useFormHelpVM(`form-help-${useId()}`);

useFormShortcuts({
    helpVm,
  });


onMounted(() => formVm.initialize(props.product, props.options));
</script>

<template>
    <Head :title="vm.isEditing ? 'Edit Produk - Inventory' : 'Tambah Produk - Inventory'" />

    <div class="page-header product-form-header">
        <div>
            <div class="breadcrumb">
                <a href="/" class="breadcrumb-link"><i class="fa-solid fa-house"></i></a>
                <span>/</span>
                <a href="/inventory?tab=utama" class="breadcrumb-link">Master</a>
                <span>/</span>
                <a href="/master/products" class="breadcrumb-link">Produk</a>
                <span>/</span>
                <span>{{ vm.isEditing ? 'Edit' : 'Baru' }}</span>
            </div>
            <div class="page-title">{{ vm.isEditing ? 'Edit Produk' : 'Tambah Produk' }}</div>
            <div class="page-sub">Lengkapi data produk sesuai form master ninesoft.</div>
        </div>
        <div class="header-actions">
            <a href="/master/products" class="btn btn-outline btn-back" data-testid="btn-back-product">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <InfoIcon @click="helpVm.toggle()" title="Bantuan Form Produk (F1)" />
        </div>
    </div>

    <form class="product-form" data-testid="product-form" novalidate @submit.prevent="vm.submitProduct">
        <section class="form-panel">

            <label class="form-field required" :class="{ 'has-error': vm.form.errors.code }">
                <span>Kode Produk</span>
                <input v-model="vm.form.code" type="text" maxlength="50" data-testid="input-code">
                <small v-if="vm.form.errors.code" class="field-error">{{ vm.form.errors.code }}</small>
            </label>

            <label class="form-field required" :class="{ 'has-error': vm.form.errors.name }">
                <span>Nama Produk</span>
                <input v-model="vm.form.name" type="text" maxlength="255" data-testid="input-name">
                <small v-if="vm.form.errors.name" class="field-error">{{ vm.form.errors.name }}</small>
            </label>

            <label class="form-field" :class="{ 'has-error': vm.form.errors.item_name_alt }">
                <span>Kemasan</span>
                <input v-model="vm.form.item_name_alt" type="text" maxlength="255" data-testid="input-kemasan">
                <small v-if="vm.form.errors.item_name_alt" class="field-error">{{ vm.form.errors.item_name_alt }}</small>
            </label>

            <label class="form-field" :class="{ 'has-error': vm.form.errors.description }">
                <span>Deskripsi</span>
                <input v-model="vm.form.description" type="text" maxlength="500" data-testid="input-description">
                <small v-if="vm.form.errors.description" class="field-error">{{ vm.form.errors.description }}</small>
            </label>

            <label class="form-field" :class="{ 'has-error': vm.form.errors.barcode }">
                <span>Barcode</span>
                <input v-model="vm.form.barcode" type="text" maxlength="100" data-testid="input-barcode">
                <small v-if="vm.form.errors.barcode" class="field-error">{{ vm.form.errors.barcode }}</small>
            </label>

            <div class="form-field select-option" :class="{ 'has-error': vm.form.errors.base_unit_uuid }">
                <span>Satuan</span>
                <div class="select-with-action">
                    <AsyncSelect2
                        v-model="vm.form.base_unit_uuid"
                        endpoint="/products/options?type=unit"
                        :initial-options="formVm.options.units"
                        label="name"
                        value-key="uuid"
                        data-testid="select-unit"
                    />
                    <a class="btn-add-option" href="/master/units" target="_blank" rel="noopener" title="Tambah Satuan" data-testid="btn-add-unit">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                </div>
<small v-if="vm.form.errors.base_unit_uuid" class="field-error">{{ vm.form.errors.base_unit_uuid }}</small>
            </div>

            <div class="form-field select-option" :class="{ 'has-error': vm.form.errors.group_uuid }">
                <span>Kelompok</span>
                <div class="select-with-action">
                    <AsyncSelect2
                        v-model="vm.form.group_uuid"
                        endpoint="/products/options?type=KELOMPOK"
                        :initial-options="formVm.options.groups"
                        label="label"
                        value-key="uuid"
                        data-testid="select-group"
                    />
                    <a class="btn-add-option" href="/master/product-groups" target="_blank" rel="noopener" title="Tambah Kelompok" data-testid="btn-add-group">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                </div>
<small v-if="vm.form.errors.group_uuid" class="field-error">{{ vm.form.errors.group_uuid }}</small>
            </div>

            <label class="form-field" :class="{ 'has-error': vm.form.errors.location }">
                <span>Lokasi</span>
                <input v-model="vm.form.location" type="text" maxlength="100" data-testid="input-location">
                <small v-if="vm.form.errors.location" class="field-error">{{ vm.form.errors.location }}</small>
            </label>

            <label class="form-field" :class="{ 'has-error': vm.form.errors.min_stock }">
                <span>Minimum Stok</span>
                <input v-model.number="vm.form.min_stock" type="number" min="0" data-testid="input-min-stock">
                <small v-if="vm.form.errors.min_stock" class="field-error">{{ vm.form.errors.min_stock }}</small>
            </label>

            <div class="form-field select-option" :class="{ 'has-error': vm.form.errors.kind }">
                <span>Jenis</span>
                <div class="select-with-action">
                    <AsyncSelect2
                        v-model="vm.form.kind"
                        endpoint="/products/options?type=JENIS"
                        :initial-options="formVm.options.kinds"
                        label="label"
                        value-key="uuid"
                        data-testid="select-kind"
                    />
                    <a class="btn-add-option" href="/master/lookup?search=JENIS" target="_blank" rel="noopener" title="Tambah Jenis" data-testid="btn-add-kind">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                </div>
<small v-if="vm.form.errors.kind" class="field-error">{{ vm.form.errors.kind }}</small>
            </div>

            <div class="form-field select-option" :class="{ 'has-error': vm.form.errors.type_uuid }">
                <span>Tipe Produk</span>
                <div class="select-with-action">
                    <AsyncSelect2
                        v-model="vm.form.type_uuid"
                        endpoint="/products/options?type=TIPE"
                        :initial-options="formVm.options.types"
                        label="label"
                        value-key="uuid"
                        data-testid="select-type"
                    />
                    <a class="btn-add-option" href="/master/lookup?search=TIPE" target="_blank" rel="noopener" title="Tambah Tipe" data-testid="btn-add-type">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                </div>
<small v-if="vm.form.errors.type_uuid" class="field-error">{{ vm.form.errors.type_uuid }}</small>
            </div>

            <div class="form-field select-option" :class="{ 'has-error': vm.form.errors.brand_uuid }">
                <span>Merek</span>
                <div class="select-with-action">
                    <AsyncSelect2
                        v-model="vm.form.brand_uuid"
                        endpoint="/products/options?type=MERK"
                        :initial-options="formVm.options.brands"
                        label="label"
                        value-key="uuid"
                        data-testid="select-brand"
                    />
                    <a class="btn-add-option" href="/master/brands" target="_blank" rel="noopener" title="Tambah Merek" data-testid="btn-add-brand">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                </div>
<small v-if="vm.form.errors.brand_uuid" class="field-error">{{ vm.form.errors.brand_uuid }}</small>
            </div>

            <div class="form-field select-option" :class="{ 'has-error': vm.form.errors.origin_uuid }">
                <span>Asal Produksi</span>
                <div class="select-with-action">
                    <AsyncSelect2
                        v-model="vm.form.origin_uuid"
                        endpoint="/products/options?type=MADE_IN"
                        :initial-options="formVm.options.origins"
                        label="label"
                        value-key="uuid"
                        data-testid="select-origin"
                    />
                    <a class="btn-add-option" href="/master/lookup?search=MADE_IN" target="_blank" rel="noopener" title="Tambah Asal Produksi" data-testid="btn-add-origin">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                </div>
<small v-if="vm.form.errors.origin_uuid" class="field-error">{{ vm.form.errors.origin_uuid }}</small>
            </div>

            <div class="form-field select-option" :class="{ 'has-error': vm.form.errors.grade_uuid }">
                <span>Grade</span>
                <div class="select-with-action">
                    <AsyncSelect2
                        v-model="vm.form.grade_uuid"
                        endpoint="/products/options?type=GRADE"
                        :initial-options="formVm.options.grades"
                        label="label"
                        value-key="uuid"
                        data-testid="select-grade"
                    />
                    <a class="btn-add-option" href="/master/lookup?search=GRADE" target="_blank" rel="noopener" title="Tambah Grade" data-testid="btn-add-grade">
                        <i class="fa-solid fa-plus"></i>
                    </a>
                </div>
<small v-if="vm.form.errors.grade_uuid" class="field-error">{{ vm.form.errors.grade_uuid }}</small>
            </div>

            <label class="form-field" :class="{ 'has-error': vm.form.errors.opening_cost }">
                <span>HPP Awal</span>
                <input v-model.number="vm.form.opening_cost" type="number" min="0" step="0.01" data-testid="input-opening-cost">
                <small v-if="vm.form.errors.opening_cost" class="field-error">{{ vm.form.errors.opening_cost }}</small>
            </label>

            <label class="form-check">
                <input v-model="vm.form.is_assembly" type="checkbox" data-testid="input-assembly">
                <span>Rakitan (Otomatis)</span>
            </label>

            <label class="form-check">
                <input v-model="vm.form.is_active" type="checkbox" data-testid="input-active">
                <span>Status Aktif</span>
            </label>

            <label class="form-field" :class="{ 'has-error': vm.form.errors.max_stock }">
                <span>Max Stok</span>
                <input v-model.number="vm.form.max_stock" type="number" min="0" data-testid="input-max-stock">
                <small v-if="vm.form.errors.max_stock" class="field-error">{{ vm.form.errors.max_stock }}</small>
            </label>

            <label class="form-field" :class="{ 'has-error': vm.form.errors.max_order }">
                <span>Max Order</span>
                <input v-model.number="vm.form.max_order" type="number" min="0" data-testid="input-max-order">
                <small v-if="vm.form.errors.max_order" class="field-error">{{ vm.form.errors.max_order }}</small>
            </label>

            <label class="form-field form-field-three" :class="{ 'has-error': vm.form.errors.remarks }">
                <span>Keterangan</span>
                <textarea v-model="vm.form.remarks" rows="3" data-testid="input-note"></textarea>
                <small v-if="vm.form.errors.remarks" class="field-error">{{ vm.form.errors.remarks }}</small>
            </label>

            <div class="form-field form-field-image form-field-three">
                <span>Gambar / PDF</span>
                <input
                    id="product-file-input"
                    type="file"
                    class="upload-input-hidden"
                    accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf"
                    multiple
                    data-testid="input-image"
                    @change="vm.handleImageChange"
                >
                <label
                    for="product-file-input"
                    class="upload-card"
                    data-testid="upload-image"
                    tabindex="0"
                    role="button"
                    aria-label="Tambah atau ubah file produk"
                    :aria-busy="vm.isUploadingFiles"
                    @dragover.prevent
                    @drop.prevent="formVm.handleImageDrop"
                >
                    <div v-if="vm.imageEntries.length" class="upload-grid">
                        <div v-for="(entry, index) in vm.imageEntries" :key="entry.preview" class="upload-item">
                            <a
                                v-if="vm.isPdfEntry(entry)"
                                :href="entry.preview"
                                target="_blank"
                                rel="noopener"
                                class="upload-pdf-preview"
                                title="Buka file PDF"
                            >
                                <i class="fa-solid fa-file-pdf"></i>
                                <span>PDF</span>
                            </a>
                            <img v-else :src="entry.preview" alt="Preview file produk" class="upload-preview">
                            <span v-if="index === 0" class="upload-primary-badge">
                                <i class="fa-solid fa-star"></i> File utama
                            </span>
                            <button
                                v-if="index > 0"
                                type="button"
                                class="btn-upload-primary"
                                title="Jadikan file utama"
                                data-testid="btn-make-primary-image"
                                @click.stop="vm.makeImagePrimary(index)"
                            >
                                <i class="fa-solid fa-star"></i>
                            </button>
                            <button type="button" class="btn-upload-remove" :title="'Hapus file ' + (index + 1)" data-testid="btn-remove-image" @click.stop="vm.removeImage(index)">
                                Hapus
                            </button>
                            <div class="upload-file-info" :title="vm.getProductFileName(entry)">
                                <span>{{ vm.getProductFileName(entry) }}</span>
                                <small>{{ vm.formatProductFileSize(entry.file?.size) }}</small>
                            </div>
                        </div>
                        <span class="upload-add-tile" data-testid="btn-add-image" aria-label="Tambah gambar produk">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah</span>
                        </span>
                    </div>
                    <div v-else class="upload-placeholder">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <strong>Tarik gambar atau PDF ke sini, atau klik area ini</strong>
                        <small>PNG, JPG, WEBP, atau PDF â€¢ maksimal 2 MB per file</small>
                    </div>
                    <div v-if="vm.imageEntries.length" class="upload-meta">
                        <span>{{ vm.imageEntries.length }} file dipilih</span>
                        <span class="upload-meta-actions">
                            <small>Maksimal 10 file</small>
                            <button type="button" class="btn-upload-clear" data-testid="btn-clear-image" @click.stop="vm.clearImages">
                                <i class="fa-solid fa-trash-can"></i> Hapus semua
                            </button>
                        </span>
                    </div>
                    <div class="upload-note">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>File pertama menjadi file utama dan akan ditampilkan pada daftar produk.</span>
                    </div>
                </label>
            </div>
        </section>

        <div class="tab-container">
            <ul class="product-tabs">
                <li v-for="tab in formVm.tabs" :key="tab.id" :class="{ active: formVm.activeTab === tab.id }">
                    <button type="button" :data-testid="'tab-' + tab.id" @click="formVm.selectTab(tab.id)">{{ tab.label }}</button>
                </li>
            </ul>

            <div v-if="formVm.activeTab === 'detail'" class="tab-panel">
                <div class="tab-panel-head">
                    <button type="button" class="btn btn-primary btn-sm" data-testid="btn-add-unit-row" @click="vm.addUnitRow"><i class="fa-solid fa-plus"></i> Tambah Data</button>
                </div>
                <div class="table-responsive">
                <table class="table table-striped table-bordered editable-table">
                    <thead><tr><th>SATUAN LAIN</th><th>KONVERSI</th><th>KETERANGAN</th><th>DEFAULT</th><th>AKTIF</th><th>AKSI</th></tr></thead>
                    <tbody>
                        <tr v-for="(row, index) in vm.unitRows" :key="row.uuid ?? `unit-${index}`">
                            <td><AsyncSelect2 v-model="row.unit_uuid" endpoint="/products/options?type=unit" label="name" value-key="uuid" data-testid="unit-row-select" /></td>
                            <td><input v-model.number="row.conversion_ratio" type="number" min="0.0001" step="0.0001" data-testid="unit-row-ratio"></td>
                            <td><input v-model="row.note" type="text" data-testid="unit-row-note"></td>
                            <td><input type="checkbox" :checked="row.is_default" data-testid="unit-row-default" @change="vm.toggleUnitDefault(index, $event)"></td>
                            <td><input v-model="row.is_active" type="checkbox" data-testid="unit-row-active"></td>
                            <td><button type="button" class="btn btn-outline btn-sm btn-danger-soft" data-testid="btn-remove-unit-row" @click="vm.removeUnitRow(index)">Hapus</button></td>
                        </tr>
                        <tr v-if="!vm.unitRows.length"><td colspan="6" class="empty-row">Belum ada data</td></tr>
                    </tbody>
                </table>
                </div>
            </div>

            <div v-else-if="formVm.activeTab === 'account'" class="tab-panel">
                <div class="account-grid">
                    <div v-for="mapping in vm.accountMappings" :key="mapping.role" class="account-item">
                        <label class="account-label">{{ mapping.label }}</label>
                        <div class="account-control">
                            <AsyncSelect2
                                :model-value="mapping.row.account_uuid"
                                endpoint="/products/options?type=account"
                                :initial-options="formVm.options.accounts ?? []"
                                label="label"
                                value-key="uuid"
                                :data-testid="`account-row-select-${mapping.role}`"
                                @update:model-value="(value: string) => vm.setAccountByRole(mapping.role, value)"
                            />
                        </div>
                        <div class="account-preview" :data-testid="`account-row-label-${mapping.role}`">
                            <span>Nomor - Nama</span>
                            <strong>{{ vm.accountLabel(mapping.row.account_uuid) }}</strong>
                        </div>
                    </div>
                </div>
                <div class="account-actions">
                    <button type="button" class="btn btn-primary btn-sm" data-testid="btn-set-default-account" @click="vm.setDefaultAccounts">Set as Default</button>
                    <button type="button" class="btn btn-primary btn-sm" data-testid="btn-sync-account" @click="vm.syncAccounts">Sync Acc</button>
                </div>
            </div>

            <div v-else-if="formVm.activeTab === 'saldo'" class="tab-panel">
                <div class="tab-panel-head">
                    <button type="button" class="btn btn-primary btn-sm" data-testid="btn-add-opening-balance-row" @click="vm.addOpeningBalanceRow"><i class="fa-solid fa-plus"></i> Tambah Data</button>
                </div>
                <div class="table-responsive"><table class="table table-striped table-bordered editable-table">
                    <thead><tr><th>GUDANG</th><th>PERIODE</th><th>STOK AWAL</th><th>AKSI</th></tr></thead>
                    <tbody>
                        <tr v-for="(row, index) in vm.openingBalanceRows" :key="row.uuid ?? `opening-${index}`">
                            <td><AsyncSelect2 v-model="row.location_uuid" endpoint="/products/options?type=warehouse" :initial-options="formVm.options.warehouses ?? []" label="name" value-key="uuid" data-testid="opening-row-location" /></td>
                            <td><input v-model="row.period" type="month" data-testid="opening-row-period"></td>
                            <td><input v-model.number="row.opening_qty" type="number" min="0" step="0.01" data-testid="opening-row-qty"></td>
                            <td><button type="button" class="btn btn-outline btn-sm btn-danger-soft" data-testid="btn-remove-opening-row" @click="vm.removeOpeningBalanceRow(index)">Hapus</button></td>
                        </tr>
                        <tr v-if="!vm.openingBalanceRows.length"><td colspan="4" class="empty-row">Belum ada data</td></tr>
                    </tbody>
                </table></div>
            </div>

            <div v-else-if="formVm.activeTab === 'harga'" class="tab-panel">
                <div class="tab-panel-head">
                    <button type="button" class="btn btn-primary btn-sm" data-testid="btn-add-price-row" @click="vm.addPriceRow"><i class="fa-solid fa-plus"></i> Tambah Data</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered editable-table">
                        <thead>
                            <tr>
                                <th rowspan="2">SATUAN</th><th rowspan="2">HARGA BELI</th>
                                <th rowspan="2">HARGA JUAL</th><th colspan="2">DISKON</th>
                                <th rowspan="2">AKTIF</th><th rowspan="2">QTY DASAR</th><th rowspan="2">AKSI</th>
                            </tr>
                            <tr>
                                <th>Rp</th><th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in vm.priceRows" :key="row.uuid ?? `price-${index}`">
                                <td><AsyncSelect2 v-model="row.price_level_uuid" endpoint="/products/options?type=price_level" :initial-options="formVm.options.priceLevels ?? []" label="name" value-key="uuid" data-testid="price-row-level" /></td>
                                <td><input v-model.number="row.price_buy" type="number" min="0" step="0.01" data-testid="price-row-buy"></td>
                                <td><input v-model.number="row.price_sell" type="number" min="0" step="0.01" data-testid="price-row-sell"></td>
                                <td><input v-model.number="row.discount_amount" type="number" min="0" step="0.01" data-testid="price-row-discount-amount"></td>
                                <td><input v-model.number="row.discount_percent" type="number" min="0" max="100" step="0.01" data-testid="price-row-discount-percent"></td>
                                <td><input v-model="row.is_active" type="checkbox" data-testid="price-row-active"></td>
                                <td><input v-model.number="row.base_qty" type="number" min="1" step="0.01" data-testid="price-row-base-qty"></td>
                                <td><button type="button" class="btn btn-outline btn-sm btn-danger-soft" data-testid="btn-remove-price-row" @click="vm.removePriceRow(index)">Hapus</button></td>
                            </tr>
                            <tr v-if="!vm.priceRows.length"><td colspan="8" class="empty-row">Belum ada data</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="tab-panel">
                <div class="tab-panel-head">
                    <button type="button" class="btn btn-primary btn-sm" data-testid="btn-add-discount-row" @click="vm.addDiscountRow"><i class="fa-solid fa-plus"></i> Tambah Data</button>
                </div>
                <div class="table-responsive">
                <table class="table table-striped table-bordered editable-table">
                    <thead><tr><th>JUMLAH</th><th>SATUAN</th><th>DISKON %</th><th>DISKON Rp</th><th>QTY DASAR</th><th>AKSI</th></tr></thead>
                    <tbody>
                        <tr v-for="(row, index) in vm.discountRows" :key="row.uuid ?? `discount-${index}`">
                            <td><input v-model.number="row.quantity" type="number" min="1" step="0.01" data-testid="discount-row-quantity"></td>
                            <td><AsyncSelect2 v-model="row.unit_uuid" endpoint="/products/options?type=unit" label="name" value-key="uuid" data-testid="discount-row-unit" /></td>
                            <td><input v-model.number="row.discount_percent" type="number" min="0" max="100" step="0.01" data-testid="discount-row-percent"></td>
                            <td><input v-model.number="row.discount_amount" type="number" min="0" step="0.01" data-testid="discount-row-amount"></td>
                            <td><input v-model.number="row.base_qty" type="number" min="1" step="0.01" data-testid="discount-row-base-qty"></td>
                            <td><button type="button" class="btn btn-outline btn-sm btn-danger-soft" data-testid="btn-remove-discount-row" @click="vm.removeDiscountRow(index)">Hapus</button></td>
                        </tr>
                        <tr v-if="!vm.discountRows.length"><td colspan="6" class="empty-row">Belum ada data</td></tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div v-if="Object.keys(vm.form.errors ?? {}).length" class="form-errors" data-testid="product-form-errors">
            <p v-for="(error, key) in vm.form.errors" :key="key">{{ error }}</p>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-outline" data-testid="btn-cancel-product" @click="vm.cancelProduct">Batal</button>
            <button type="submit" class="btn btn-primary" data-testid="btn-submit-product" :disabled="vm.isSubmittingProduct || vm.isUploadingFiles">
                {{ vm.isSubmittingProduct ? 'Menyimpan...' : 'Simpan' }}
            </button>
        </div>
    </form>
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
                            <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: Form Produk</h4>
                            <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Panduan Penggunaan & Shortcut</div>
                        </div>
                    </div>
                    <button type="button" class="help-drawer-close" @click="helpVm.close()" title="Tutup (Esc)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="help-drawer-body">
                    <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                        Produk digunakan untuk mencatat barang atau jasa yang dijual di klinik beserta harga dan stoknya.
                    </p>
                    <div style="margin-bottom: 28px;">
                        <h6 class="help-section-title">
                            <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                        </h6>
                        <div class="step-list">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <div class="step-text">Isi <strong>Kode Produk</strong> dan <strong>Nama Produk</strong>.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <div class="step-text">Pilih <strong>Satuan</strong> dan <strong>Kelompok</strong> produk.</div>
                            </div>
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <div class="step-text">Lengkapi <strong>Harga</strong> dan <strong>Minimum Stok</strong>.</div>
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
                                <kbd>F1</kbd>
                                <span>Buka/tutup bantuan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.product-form {
    max-width: none;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-panel {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    align-items: end;
    padding: 22px 24px;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    background: var(--card);
}

.form-panel h3 {
    grid-column: 1 / -1;
    align-self: start;
    margin: 0 0 14px;
    font-size: 15px;
    font-weight: 800;
}

.tab-container {
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 14px;
    overflow: hidden;
    background: var(--card);
}

.product-tabs {
    display: flex;
    overflow-x: auto;
    overflow-y: hidden;
    margin: 0;
    padding: 0;
    border-bottom: 1px solid var(--border);
    list-style: none;
    background: var(--bg);
}

.product-tabs li {
    margin: 0;
}

.product-tabs li button {
    min-width: 132px;
    min-height: 48px;
    padding: 12px 16px;
    border: 0;
    border-right: 1px solid var(--border);
    background: transparent;
    color: var(--muted);
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    transition: color .18s ease, background-color .18s ease, box-shadow .18s ease;
}

.product-tabs li button:hover {
    background: var(--lv-background);
    color: var(--primary, #6366f1);
}

.product-tabs li.active button {
    color: var(--primary, #6366f1);
    background: var(--card);
    box-shadow: inset 0 -2px 0 var(--primary, #6366f1);
}

.tab-panel {
    min-width: 0;
    padding: 20px;
}

.tab-panel-head {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.account-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px 24px;
}

.account-item {
    display: grid;
    grid-template-columns: minmax(150px, 210px) minmax(0, 1fr) minmax(210px, 280px);
    gap: 12px;
    align-items: center;
    min-height: 68px;
    padding: 12px 14px;
    border: 1px solid color-mix(in srgb, var(--border, #e2e8f0) 78%, transparent);
    border-radius: 12px;
    background: color-mix(in srgb, var(--bg, #f8fafc) 42%, var(--card, #ffffff));
}

.account-label {
    overflow: hidden;
    color: var(--muted, #64748b);
    font-size: 11px;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: .035em;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.account-preview {
    display: grid;
    min-width: 0;
    padding: 8px 10px;
    border: 1px dashed var(--border, #e2e8f0);
    border-radius: 8px;
    background: var(--card, #ffffff);
}

.account-preview span {
    color: var(--muted, #64748b);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
}

.account-preview strong {
    overflow: hidden;
    color: var(--text, #334155);
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.account-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid var(--border, #e2e8f0);
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 10px 12px;
    border: 1px solid var(--border);
    font-size: 12px;
    text-align: left;
}

.table thead th {
    background: var(--bg);
    color: var(--muted);
    font-weight: 800;
    white-space: nowrap;
}

.editable-table {
    min-width: 760px;
}

.editable-table select,
.editable-table input:not([type='checkbox']) {
    width: 100%;
    min-height: 36px;
    padding: 7px 10px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--card);
    color: var(--text);
    font: inherit;
    line-height: 1.25;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.editable-table select {
    min-width: 150px;
    cursor: pointer;
}

.editable-table select:focus,
.editable-table input:not([type='checkbox']):focus {
    outline: none;
    border-color: var(--primary, #6366f1);
    box-shadow: 0 0 0 3px var(--primary-light, rgba(99, 102, 241, .12));
}

.editable-table input[type='checkbox'] {
    width: 17px;
    height: 17px;
    accent-color: var(--primary, #6366f1);
    cursor: pointer;
}

.editable-table td:first-child,
.editable-table td:last-child {
    white-space: nowrap;
}

.editable-table td:first-child {
    width: 92px;
    color: var(--muted);
    font-size: 11px;
    font-weight: 700;
}

.editable-table td:last-child {
    width: 92px;
}

.editable-table .btn {
    min-height: 34px;
    padding: 7px 11px;
    border-radius: 8px;
    font-size: 12px;
}

.tab-panel :deep(.vs__dropdown-toggle) {
    min-height: 36px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--card);
}
.table-responsive {
    overflow-x: auto;
}

.empty-row {
    color: var(--muted, #64748b);
    text-align: center !important;
}

@media (max-width: 991px) {
    .form-panel {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .account-grid {
        grid-template-columns: 1fr;
    }

    .account-item {
        grid-template-columns: 1fr;
        align-items: stretch;
    }

    .account-preview {
        min-height: 54px;
    }
}

.product-form-header .btn-back {
    min-height: 36px;
    padding: 8px 14px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-field {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 5px;
    margin-bottom: 0;
}

.form-field-three {
    grid-column: span 3;
}

.form-field-image {
    grid-column: span 2;
}

.select-with-action {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 40px;
    gap: 8px;
    align-items: stretch;
}

.btn-add-option {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    border: 0;
    border-radius: 10px;
    background: var(--primary, #6366f1);
    color: var(--on-primary);
    font-size: 13px;
    text-decoration: none;
}

.btn-add-option:hover {
    background: var(--primary-dark, #4f46e5);
    color: var(--on-primary);
}

.form-field > span {
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
}

.form-field :deep(.vs__dropdown-toggle) {
    min-height: 38px;
    border-radius: 10px;
}

.form-field input,
.form-field select,
.form-field textarea {
    min-height: 38px;
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 13px;
    font-family: inherit;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
    outline: none;
    border-color: var(--primary, #6366f1);
    box-shadow: 0 0 0 3px var(--primary-light, rgba(99, 102, 241, .1));
}

.form-check {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    align-self: end;
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    color: var(--muted);
}

.upload-input-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
}

.upload-card {
    display: grid;
    grid-template-rows: 1fr auto;
    min-height: 144px;
    padding: 8px;
    border: 1px dashed var(--lv-border, #E8EAF0);
    border-radius: 16px;
    background: linear-gradient(180deg, var(--lv-surface, #FFFFFF) 0%, var(--lv-background, #F5F6FA) 100%);
    cursor: pointer;
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}

.upload-card:hover {
    border-color: var(--lv-primary, #003D79);
    box-shadow: 0 10px 24px color-mix(in srgb, var(--lv-primary, #003D79) 12%, transparent);
    transform: translateY(-1px);
}

.upload-card:focus-visible {
    border-color: var(--lv-primary, #003D79);
    outline: none;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--lv-primary, #003D79) 18%, transparent);
}

.upload-preview {
    width: 100%;
    height: 92px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid var(--lv-border, #E8EAF0);
}

.upload-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(92px, 1fr));
    gap: 10px;
}

.upload-item {
    position: relative;
}

.upload-item:first-child .upload-preview {
    border: 2px solid var(--lv-primary, #003D79);
}

.upload-pdf-preview {
    display: grid;
    place-items: center;
    gap: 5px;
    width: 100%;
    height: 92px;
    border: 1px solid var(--lv-border, #E8EAF0);
    border-radius: 12px;
    background: var(--lv-danger-soft, #FEF2F2);
    color: var(--lv-danger, #DC2626);
    font-size: 10px;
    font-weight: 800;
    text-decoration: none;
}

.upload-pdf-preview i {
    font-size: 24px;
}

.upload-primary-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    max-width: calc(100% - 44px);
    padding: 3px 7px;
    border-radius: 999px;
    background: color-mix(in srgb, var(--lv-primary, #003D79) 88%, transparent);
    color: var(--lv-surface, #FFFFFF);
    font-size: 10px;
    font-weight: 800;
    line-height: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.btn-upload-primary,
.btn-upload-remove {
    position: absolute;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    padding: 0;
    border: 0;
    border-radius: 999px;
    color: var(--lv-surface, #FFFFFF);
    font-size: 12px;
    cursor: pointer;
    z-index: 2;
}

.btn-upload-primary {
    bottom: 52px;
    left: 8px;
    background: var(--lv-info, #2563EB);
}

.btn-upload-primary:hover {
    background: color-mix(in srgb, var(--lv-info, #2563EB) 85%, var(--lv-ink, #1A1A2E));
}

.upload-file-info {
    display: grid;
    gap: 2px;
    margin-top: 5px;
    font-size: 10px;
    line-height: 1.2;
}

.upload-file-info span {
    overflow: hidden;
    color: var(--lv-ink, #1A1A2E);
    font-weight: 800;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.upload-file-info small {
    color: var(--lv-neutral, #6B7280);
    font-weight: 700;
}

.upload-meta-actions {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-upload-clear {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 8px;
    border: 1px solid color-mix(in srgb, var(--lv-danger, #DC2626) 25%, transparent);
    border-radius: 999px;
    background: var(--lv-danger-soft, #FEF2F2);
    color: var(--lv-danger, #DC2626);
    font-size: 10px;
    font-weight: 800;
    cursor: pointer;
}

.upload-add-tile {
    display: grid;
    place-items: center;
    gap: 4px;
    min-height: 92px;
    border: 1px dashed color-mix(in srgb, var(--lv-primary, #003D79) 25%, transparent);
    border-radius: 12px;
    background: color-mix(in srgb, var(--lv-info-soft, #EFF6FF) 55%, var(--lv-surface, #FFFFFF));
    color: var(--lv-primary, #003D79);
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
    transition: background-color .18s ease, border-color .18s ease, transform .18s ease;
}

.upload-add-tile i {
    font-size: 16px;
}

.upload-add-tile:hover {
    background: var(--lv-info-soft, #EFF6FF);
    border-color: var(--lv-primary, #003D79);
    transform: translateY(-1px);
}

.upload-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: 8px;
    padding: 6px 10px;
    border-radius: 10px;
    background: var(--lv-surface, #FFFFFF);
    border: 1px solid var(--lv-border, #E8EAF0);
    color: var(--lv-ink, #1A1A2E);
    font-size: 12px;
    font-weight: 700;
}

.upload-meta small {
    color: var(--lv-neutral, #6B7280);
    font-weight: 600;
}

.upload-note {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    margin-top: 8px;
    padding: 8px 10px;
    border: 1px solid color-mix(in srgb, var(--lv-info, #2563EB) 16%, transparent);
    border-radius: 10px;
    background: var(--lv-info-soft, #EFF6FF);
    color: var(--lv-info, #2563EB);
    font-size: 11px;
    font-weight: 700;
    line-height: 1.35;
}

.upload-note i {
    margin-top: 1px;
    font-size: 11px;
}

.upload-placeholder {
    display: grid;
    place-items: center;
    gap: 4px;
    min-height: 92px;
    padding: 12px;
    border-radius: 12px;
    background: var(--lv-surface, #FFFFFF);
    color: var(--lv-neutral, #6B7280);
    text-align: center;
}

.upload-placeholder i {
    font-size: 26px;
    color: var(--lv-primary, #003D79);
}

.upload-placeholder strong {
    font-size: 13px;
    color: var(--lv-ink, #1A1A2E);
}

.upload-placeholder small {
    font-size: 11px;
}

.btn-upload-remove {
    position: absolute;
    top: 8px;
    right: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: auto;
    min-width: 48px;
    height: 24px;
    padding: 0 8px;
    border: 0;
    border-radius: 999px;
    background: var(--lv-danger, #DC2626);
    color: var(--lv-surface, #FFFFFF);
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
    transition: color .18s ease, background-color .18s ease, box-shadow .18s ease;
    z-index: 2;
}

.btn-upload-remove:hover {
    background: color-mix(in srgb, var(--lv-danger, #DC2626) 80%, var(--lv-ink, #1A1A2E));
}

.form-field.required > span::after,
.form-field.select-option.required > span::after {
    content: ' *';
    color: var(--lv-danger, #DC2626);
    font-weight: 700;
}

.form-field.has-error input,
.form-field.has-error textarea,
.form-field.has-error :deep(.vs__dropdown-toggle) {
    border-color: var(--lv-danger, #DC2626) !important;
}

.form-field.has-error > span {
    color: var(--lv-danger, #DC2626);
}

.field-error {
    display: block;
    margin-top: 4px;
    color: var(--lv-danger, #DC2626);
    font-size: 11px;
    font-weight: 600;
}

.form-errors {
    padding: 12px 14px;
    border: 1px solid color-mix(in srgb, var(--lv-danger) 28%, transparent);
    border-radius: 10px;
    background: var(--lv-danger-soft);
}

.form-errors p {
    margin: 2px 0;
    color: var(--lv-danger);
    font-size: 13px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.form-actions .btn {
    min-height: 40px;
}

@media (max-width: 640px) {
    .product-form-header {
        flex-direction: column;
        gap: 16px;
    }

    .product-form-header .btn-back {
        align-self: flex-start;
    }

    .header-actions {
        width: 100%;
        justify-content: flex-start;
    }

    .form-panel {
        grid-template-columns: 1fr;
    }

    .form-field-image {
        grid-column: auto;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
