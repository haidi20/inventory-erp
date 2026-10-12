<script setup lang="ts">
import { useItemConversionFiltersVM } from '@modules/Operational/Master/presentations/viewmodels/item_conversion_filters.vm';

defineProps<{ loading: boolean }>();
const emit = defineEmits(['search']);
const vm = useItemConversionFiltersVM();
</script>

<template>
    <div class="flex gap-4 items-center">
        <div class="relative flex-1">
            <input 
                v-model="vm.query"
                type="text" 
                placeholder="Cari berdasarkan produk atau satuan..."
                class="w-full pl-10 pr-4 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 outline-none"
                @keyup.enter="emit('search', vm.search())"
            >
            <span class="absolute left-3 top-2.5 text-gray-400">
                <i class="fas fa-search"></i>
            </span>
        </div>
        <button 
            @click="emit('search', vm.search())"
            :disabled="loading"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 disabled:opacity-50"
        >
            {{ loading ? 'Mencari...' : 'Cari' }}
        </button>
    </div>
</template>
