<script setup lang="ts">
import { useFarmacyRecipeVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_recipe.vm';
import { PelayananStatus } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';
import RecipeAssessment from '@modules/Clinical/Pharmacy/presentations/views/Partials/RecipeAssessment.vue';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';

defineProps<{
    show: boolean;
}>();

const emit = defineEmits(['close']);

const vm = useFarmacyRecipeVM();
</script>

<template>
    <Teleport to="body">
        <div v-if="show && vm.selectedProfile" class="full-modal-overlay">
            <div class="full-modal-container">
                <div class="full-modal-header">
                    <div class="header-left">
                        <button class="btn-close-modal" @click="emit('close')">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <div>
                            <div class="modal-category">Resep Farmasi</div>
                            <div class="modal-title-main">
                                {{ vm.selectedProfile.recipe_no }} 
                                <span class="badge badge-warning" style="margin-left:8px; font-size:12px;">AKTIF</span>
                            </div>
                        </div>
                    </div>
                    <div class="header-right">
                        <!-- Header actions remain commented as per instruction -->
                    </div>
                </div>

                <!-- Tab Navigation (from VM) -->
                <div class="modal-tabs">
                    <button 
                        v-for="tab in vm.recipeDetailTabs" 
                        :key="tab.value"
                        class="tab-item" 
                        :class="{ active: vm.activeDetailTab === tab.value }" 
                        @click="vm.setActiveTab(tab.value)"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div class="full-modal-body">
                    <!-- Tab Resep -->
                    <div v-if="vm.activeDetailTab === 'resep'" class="detail-container">
                        <!-- Top Info Cards -->
                        <div class="info-cards-grid">
                            <div class="info-card">
                                <div class="card-title-row">
                                    <i class="fa-solid fa-user-circle"></i>
                                    <span>Informasi Pasien</span>
                                </div>
                                <div class="card-body-content">
                                    <div class="patient-hero">
                                        <div class="p-name">{{ vm.selectedProfile.name }}</div>
                                        <div class="p-meta">NIK: {{ vm.selectedProfile.nik }} | {{ vm.selectedProfile.gender }}</div>
                                    </div>
                                    <div class="p-details">
                                        <div class="p-item"><span>Usia:</span> {{ vm.selectedProfile.age }}</div>
                                        <div class="p-item"><span>Tipe:</span> {{ vm.selectedProfile.admission_type }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="info-card">
                                <div class="card-title-row">
                                    <i class="fa-solid fa-file-prescription"></i>
                                    <span>Informasi Resep</span>
                                </div>
                                <div class="card-body-content">
                                    <div class="pres-grid-modern">
                                        <div class="pres-item">
                                            <label>Apoteker</label>
                                            <Select2ClientSide
                                                v-model="vm.recipeForm.pharmacist_id"
                                                :get-data="vm.pharmacists"
                                                label="name"
                                                placeholder="Pilih Apoteker"
                                            />
                                        </div>
                                        <div class="pres-item">
                                            <label>Unit Usaha</label>
                                            <input type="text" v-model="vm.recipeForm.unit_name" list="unit-suggestions" class="form-control-sm" />
                                            <datalist id="unit-suggestions">
                                                <option v-for="s in vm.businessUnitSuggestions" :key="s" :value="s" />
                                            </datalist>
                                        </div>
                                        <div class="pres-item">
                                            <label>Gudang</label>
                                            <Select2ClientSide
                                                v-model="vm.recipeForm.warehouse_id"
                                                :get-data="vm.warehouses"
                                                label="name"
                                                placeholder="Pilih Gudang"
                                            />
                                        </div>
                                        <div class="pres-item">
                                            <label>Dokter Peresep</label>
                                            <div class="val" style="margin-top: 8px; font-weight: 700;">{{ vm.selectedProfile.doctor }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Medication Table -->
                        <div class="section-container" style="margin-top: 32px;">
                            <div class="section-header-row">
                                <h3 class="section-label">Daftar Obat & Aturan Pakai</h3>
                                <button class="btn-text"><i class="fa-solid fa-plus"></i> Tambah Obat</button>
                            </div>
                            <div class="medication-table-wrap">
                                <table class="med-table">
                                    <thead>
                                        <tr>
                                            <th>Nama Obat</th>
                                            <th>Batch</th>
                                            <th style="width: 80px;">Jumlah</th>
                                            <th style="text-align: right;">Harga</th>
                                            <th style="text-align: right;">Subtotal</th>
                                            <th style="min-width: 200px;">Aturan Pakai</th>
                                            <th style="min-width: 200px;">Keterangan</th>
                                            <th style="text-align: center;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="med in vm.selectedMedications" :key="med.uuid">
                                            <td><div class="m-name">{{ med.name }}</div></td>
                                            <td><code>{{ med.batch }}</code></td>
                                            <td>{{ med.qty }}</td>
                                            <td style="text-align: right;">{{ vm.formatIDR(med.price) }}</td>
                                            <td style="text-align: right;" class="font-bold">{{ vm.formatIDR(med.subtotal) }}</td>
                                            <td>
                                                <textarea v-model="med.usage" class="editable-area" rows="2"></textarea>
                                            </td>
                                            <td>
                                                <textarea v-model="med.note" class="editable-area" rows="2"></textarea>
                                            </td>
                                            <td>
                                                <div class="table-action-cell" style="justify-content: center;">
                                                    <button class="btn btn-outline btn-sm" title="Print Etiket" @click="vm.printMedication(med)">
                                                        <i class="fa-solid fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Action Buttons Below Table -->
                            <div class="table-footer-actions">
                                <button class="btn btn-outline">
                                    <i class="fa-solid fa-print"></i> Cetak Resep
                                </button>
                                <button class="btn btn-outline">
                                    <i class="fa-solid fa-ticket"></i> Cetak E-Tiket
                                </button>
                                <button class="btn btn-outline" style="color: #25d366; border-color: #25d366;">
                                    <i class="fa-brands fa-whatsapp"></i> Kirim WhatsApp
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pelayanan Tab (Pengkajian Resep) -->
                    <div v-else-if="vm.activeDetailTab === 'pelayanan'" class="detail-container">
                         <!-- Sub-Tabs for Pelayanan could be here, but using direct form based on guide -->
                         <RecipeAssessment />
                    </div>

                    <!-- Empty State -->
                    <div v-else class="empty-state">
                        <i class="fa-solid fa-folder-open"></i>
                        <p>Data tidak tersedia untuk tab ini.</p>
                    </div>
                </div>

                <div class="full-modal-footer">
                    <!-- Footer actions remain commented as per instruction -->
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.full-modal-body { flex: 1; overflow-y: auto; padding: 32px; background: #f8fafc; }
.detail-container { max-width: 1400px; margin: 0 auto; }

.editable-area {
    width: 100%;
    padding: 8px;
    border: 1px solid var(--line);
    border-radius: 6px;
    font-family: inherit;
    font-size: 12px;
    resize: none;
    background: #fdfdfd;
}
.editable-area:focus { border-color: var(--primary); outline: none; background: #fff; }

.pres-grid-modern { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.pres-item label { display: block; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin-bottom: 2px; }

.table-footer-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

@media (max-width: 640px) { .pres-grid-modern { grid-template-columns: 1fr; } }

:deep(.vs__dropdown-toggle) {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid var(--line);
    min-height: 38px;
}
</style>
