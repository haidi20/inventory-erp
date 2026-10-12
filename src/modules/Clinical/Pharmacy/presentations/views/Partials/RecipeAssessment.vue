<script setup lang="ts">
import { useFarmacyRecipeVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_recipe.vm';
import Select2ClientSide from '@modules/Core/shared/Components/common/Select2ClientSide.vue';

const vm = useFarmacyRecipeVM();
</script>

<template>
    <div class="assessment-container">
        <div v-for="section in vm.assessmentSections" :key="section.key" class="assessment-section card">
            <div class="section-header">
                <i :class="['fa-solid', section.icon]"></i>
                <span>{{ section.label }}</span>
            </div>
            <div class="table-wrap">
                <table class="assessment-table">
                    <thead>
                        <tr>
                            <th>Parameter Pengkajian</th>
                            <th style="width: 80px; text-align: center;">Ya</th>
                            <th style="width: 80px; text-align: center;">Tidak</th>
                            <th>Keterangan / Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in vm.assessmentForm[section.key]" :key="index">
                            <td class="field-label">{{ item.field }}</td>
                            <td style="text-align: center;">
                                <input type="radio" :name="section.key + index" value="ya" v-model="item.result" />
                            </td>
                            <td style="text-align: center;">
                                <input type="radio" :name="section.key + index" value="tidak" v-model="item.result" />
                            </td>
                            <td>
                                <input 
                                    type="text" 
                                    v-model="item.note" 
                                    class="form-control-sm" 
                                    :placeholder="item.result === section.target ? 'Wajib diisi...' : '-'"
                                    :required="item.result === section.target"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Health Worker Info -->
        <div class="assessment-section card" style="margin-top: 32px;">
            <div class="section-header">
                <i class="fa-solid fa-users-gear"></i>
                <span>Informasi Tenaga Kesehatan</span>
            </div>
            <div class="card-body-content" style="padding: 20px;">
                <div class="pres-grid-modern">
                    <div class="pres-item">
                        <label>Apoteker Pengkaji</label>
                        <Select2ClientSide
                            v-model="vm.recipeForm.pharmacist_id"
                            :get-data="vm.pharmacists"
                            label="name"
                            placeholder="Pilih Apoteker"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Assessment Footer Actions -->
        <div class="assessment-footer">
            <button class="btn btn-outline"><i class="fa-solid fa-save"></i> Simpan Draft</button>
            <button class="btn btn-outline" style="color: var(--warning); border-color: var(--warning);"><i class="fa-solid fa-reply"></i> Kirim Balik ke Dokter</button>
            <button class="btn btn-primary btn-lg"><i class="fa-solid fa-check-circle"></i> Selesaikan Pengkajian</button>
        </div>
    </div>
</template>

<style scoped>
.assessment-container { display: flex; flex-direction: column; gap: 24px; }
.assessment-section { overflow: hidden; }
.section-header { padding: 12px 20px; background: #f8fafc; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 800; color: var(--primary); }
.assessment-table { width: 100%; border-collapse: collapse; }
.assessment-table th { text-align: left; padding: 12px; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; border-bottom: 2px solid var(--border); }
.assessment-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.field-label { font-weight: 600; color: var(--text); }

.assessment-footer { display: flex; gap: 16px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border); }

.pres-grid-modern { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.pres-item label { display: block; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin-bottom: 4px; }

input[type="radio"] { width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; }

:deep(.vs__dropdown-toggle) {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid var(--border);
    min-height: 38px;
}
</style>
