<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useFarmacyRecipeVM } from '@modules/Clinical/Pharmacy/presentations/viewmodels/farmacy_recipe.vm';
import RecipeFilters from '@modules/Clinical/Pharmacy/presentations/views/Partials/RecipeFilters.vue';
import RecipeTable from '@modules/Clinical/Pharmacy/presentations/views/Partials/RecipeTable.vue';
import RecipeDetailModal from '@modules/Clinical/Pharmacy/presentations/views/Partials/RecipeDetailModal.vue';
import '@modules/Clinical/Pharmacy/presentations/styles/farmacy_recipe.css';

const vm = useFarmacyRecipeVM();

onMounted(() => {
    vm.fetch();
});

</script>

<template>
    <Head title="Resep Farmasi - Inventory" />

    <div class="page-header" style="margin-bottom: 32px;">
        <div>
            <div class="breadcrumb">
                <Link href="/" class="breadcrumb-link">
                    <i class="fa-solid fa-house"></i>
                </Link>
                <span>/</span> Operationals <span>/</span> <Link href="/farmasi/dashboard" class="breadcrumb-link">Farmacy</Link> <span>/</span> Resep
            </div>
            <div class="page-title" style="margin-bottom: 4px;">Daftar Resep</div>
            <div class="page-sub">Kelola dan proses resep obat dari poli/layanan kesehatan.</div>
        </div>
        <div style="display:flex; gap:12px; align-items: center;">
            <button type="button" class="btn btn-primary">
                <i class="fa-solid fa-plus-circle"></i> Antrian
            </button>
        </div>
    </div>

    <!-- Filter Component -->
    <RecipeFilters @filter="vm.applyFilters" />

    <!-- Table Component -->
    <RecipeTable 
        :recipes="vm.recipes" 
        :isLoading="vm.isLoading" 
        @view="vm.openRecipeDetail"
        @print="vm.printRecipe"
    />

    <!-- Detail Modal Component -->
    <RecipeDetailModal 
        :show="vm.showDetailModal"
        @close="vm.closeRecipeDetail"
    />
</template>

