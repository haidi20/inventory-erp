<script setup lang="ts">
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import Pagination from '@modules/Core/shared/Components/Pagination.vue';
import { useItemConversionVM } from '@modules/Operational/Master/presentations/viewmodels/item_conversion.vm';
import ItemConversionTable from '@modules/Operational/Master/presentations/views/Partials/ItemConversionTable.vue';
import ItemConversionFilters from '@modules/Operational/Master/presentations/views/Partials/ItemConversionFilters.vue';
import ItemConversionForm from '@modules/Operational/Master/presentations/views/Partials/ItemConversionForm.vue';

const vm = useItemConversionVM();

onMounted(() => {
    vm.fetch();
});

</script>

<template>
    <Head title="Konversi Satuan Barang" />

    <div class="p-6 bg-white rounded-lg shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Konversi Satuan Barang</h1>
            <button 
            @click="vm.openCreate"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
            >
                Tambah Konversi
            </button>
        </div>

        <ItemConversionFilters 
            @search="handleSearch"
            :loading="vm.loading"
        />

        <div class="mt-4">
            <ItemConversionTable 
                :items="vm.items"
                :loading="vm.loading"
                :sort-by="vm.filters.sort_by"
                :sort-dir="vm.filters.sort_dir"
                @sort="vm.handleSort"
                @edit="vm.openEdit"
                @delete="vm.remove"
            />
        </div>

        <div class="mt-4 flex justify-end">
            <Pagination 
                :links="vm.paginationLinks"
                :manual="true"
                @change-page="(url: string) => handlePageChange(parseInt(url))"
            />
        </div>

        <ItemConversionForm 
            v-if="vm.showForm"
            :vm="vm"
            @close="vm.closeForm"
        />
    </div>
</template>
