<script setup lang="ts">
import type { RecipeModel } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';
import { RECIPE_STATUS_BADGES, RECIPE_STATUS_LABELS } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_recipe.state';

defineProps<{
    recipes: RecipeModel[];
    isLoading: boolean;
}>();

const emit = defineEmits(['view', 'print']);

</script>

<template>
    <div class="card">
        <div class="table-wrap">
            <Transition name="fade">
                <div v-if="isLoading" class="loading-overlay">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </div>
            </Transition>
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pasien</th>
                        <th>Pelayanan</th>
                        <th>Dokter / Pengirim</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="recipe in recipes" :key="recipe.uuid">
                        <td>{{ recipe.created_at }}</td>
                        <td class="font-bold">{{ recipe.patient_name }}</td>
                        <td>
                            <span class="badge badge-info">{{ recipe.service_type }}</span>
                            <div style="font-size: 11px; color: var(--muted); margin-top: 4px; font-weight: 600;">
                                — {{ recipe.recipe_no }}
                            </div>
                        </td>
                        <td>{{ recipe.doctor_name }}</td>
                        <td>{{ recipe.recipe_type }}</td>
                        <td>
                            <span class="badge" :class="RECIPE_STATUS_BADGES[recipe.status] ?? 'badge-secondary'">
                                {{ RECIPE_STATUS_LABELS[recipe.status] ?? recipe.status }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons-container">
                                <button class="action-btn btn-view" @click="emit('view', recipe)" title="Lihat Detail Profil">
                                    <i class="fa-solid fa-eye"></i>
                                    <span>Detail</span>
                                </button>
                                <button class="action-btn btn-print" @click="emit('print', recipe)" title="Cetak E-tiket Farmasi">
                                    <i class="fa-solid fa-ticket"></i>
                                    <span>E-Tiket</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
