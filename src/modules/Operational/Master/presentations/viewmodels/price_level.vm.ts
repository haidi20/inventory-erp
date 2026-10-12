import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import apiService from '@modules/Core/shared/core/api_service';
import type { PriceLevelModel, PriceLevelFormData, PriceLevelFilters, PriceLevelMeta } from './price_level.state';
import { PRICE_LEVEL_DEFAULT_FILTERS, PRICE_LEVEL_PER_PAGE_OPTIONS } from './price_level.state';

export const usePriceLevelVM = defineStore('price-level-vm', () => {
    const page = usePage<{ auth?: { user?: { roles?: string[]; permissions?: string[] } | null } }>();

    const items       = ref<PriceLevelModel[]>([]);
    const meta        = ref<PriceLevelMeta>({} as PriceLevelMeta);
    const isLoading   = ref(false);
    const showForm    = ref(false);
    const editingItem = ref<PriceLevelModel | null>(null);
    const search      = ref(PRICE_LEVEL_DEFAULT_FILTERS.search);
    const perPage     = ref(PRICE_LEVEL_DEFAULT_FILTERS.per_page);
    const currentPage = ref(PRICE_LEVEL_DEFAULT_FILTERS.page);
    const perPageOptions = PRICE_LEVEL_PER_PAGE_OPTIONS;

    const canManage = computed(() => {
        const roles = page.props.auth?.user?.roles ?? [];
        if (['owner', 'superadmin', 'admin'].some(r => roles.includes(r))) return true;
        return (page.props.auth?.user?.permissions ?? []).includes('master.manage');
    });

    const form = useForm<PriceLevelFormData>({
        uuid: undefined, code: '', name: '', description: '', is_active: true,
    });

    async function fetchItems(filters: PriceLevelFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/masters/price-levels', {
                params: { search: search.value, per_page: perPage.value, page: currentPage.value, ...filters },
            });
            items.value = res.data.data ?? [];
            meta.value  = res.data.meta ?? {};
        } catch(e) { console.error(e); }
        finally { isLoading.value = false; }
    }

    function initFilters(filters: PriceLevelFilters = {}) {
        search.value      = filters.search   ?? PRICE_LEVEL_DEFAULT_FILTERS.search;
        perPage.value     = filters.per_page ?? PRICE_LEVEL_DEFAULT_FILTERS.per_page;
        currentPage.value = filters.page     ?? PRICE_LEVEL_DEFAULT_FILTERS.page;
        fetchItems();
    }

    function openCreate() { editingItem.value = null; form.reset(); showForm.value = true; }

    function openEdit(item: PriceLevelModel) {
        editingItem.value = item;
        form.uuid        = item.uuid;
        form.code        = item.code;
        form.name        = item.name;
        form.description = item.description ?? '';
        form.is_active   = item.is_active;
        showForm.value   = true;
    }

    function closeForm() { showForm.value = false; editingItem.value = null; form.reset(); }

    async function submitForm() {
        const isEdit = !!editingItem.value;
        const url    = isEdit ? `/masters/price-levels/${editingItem.value!.uuid}` : '/masters/price-levels';
        try {
            if (isEdit) { await apiService.put(url, form.data()); }
            else        { await apiService.post(url, form.data()); }
            Swal.fire({ title: 'Berhasil!', text: isEdit ? 'Level harga diperbarui.' : 'Level harga baru ditambahkan.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
            closeForm(); fetchItems();
        } catch(e: any) {
            const errors = e.response?.data?.errors ?? {};
            Object.keys(errors).forEach(k => form.setError(k as any, errors[k][0]));
        }
    }

    async function deleteItem(item: PriceLevelModel) {
        const res = await Swal.fire({ title: 'Hapus Level Harga?', text: `"${item.name}" tidak bisa dipulihkan.`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#ef4444' });
        if (!res.isConfirmed) return;
        await apiService.delete(`/masters/price-levels/${item.uuid}`);
        fetchItems();
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p; fetchItems({ page: p });
    }

    return { items, meta, isLoading, showForm, editingItem, canManage, form,
        search, perPage, perPageOptions, currentPage,
        fetchItems, initFilters, openCreate, openEdit, closeForm, submitForm, deleteItem, changePage };
});
