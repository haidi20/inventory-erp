<script setup lang="ts">
defineProps<{
    items: any[];
    loading: boolean;
    sortBy?: string;
    sortDir?: string;
}>();

const emit = defineEmits(['sort', 'edit', 'delete']);


</script>

<template>
    <div class="overflow-x-auto border rounded-lg">
        <table class="w-full text-left border-collapse text-sm">
            <thead class="bg-gray-50 text-gray-700 uppercase font-medium border-b">
                <tr>
                    <th class="px-4 py-3 cursor-pointer hover:bg-gray-100" @click="emit('sort', 'products.name')">
                        Produk <span v-if="sortBy === 'products.name'">{{ sortDir === 'asc' ? '↑' : '↓' }}</span>
                    </th>
                    <th class="px-4 py-3 cursor-pointer hover:bg-gray-100" @click="emit('sort', 'u1.name')">
                        Dari Satuan <span v-if="sortBy === 'u1.name'">{{ sortDir === 'asc' ? '↑' : '↓' }}</span>
                    </th>
                    <th class="px-4 py-3 cursor-pointer hover:bg-gray-100" @click="emit('sort', 'u2.name')">
                        Ke Satuan <span v-if="sortBy === 'u2.name'">{{ sortDir === 'asc' ? '↑' : '↓' }}</span>
                    </th>
                    <th class="px-4 py-3 cursor-pointer hover:bg-gray-100" @click="emit('sort', 'conversion_ratio')">
                        Rasio <span v-if="sortBy === 'conversion_ratio'">{{ sortDir === 'asc' ? '↑' : '↓' }}</span>
                    </th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading" class="border-b animate-pulse">
                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">Memuat data...</td>
                </tr>
                <tr v-else-if="items.length === 0" class="border-b">
                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">Belum ada data konversi.</td>
                </tr>
                <tr v-for="item in items" :key="item.uuid" class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ item.item_name || 'Global' }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ item.from_unit_name }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ item.to_unit_name }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ item.conv_ratio }}</td>
                    <td class="px-4 py-3">
                        <span 
                            :class="item.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                            class="px-2 py-1 rounded-full text-xs font-medium"
                        >
                            {{ item.is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <button @click="emit('edit', item)" class="text-blue-600 hover:underline">Ubah</button>
                        <button @click="emit('delete', item.uuid)" class="text-red-600 hover:underline">Hapus</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
