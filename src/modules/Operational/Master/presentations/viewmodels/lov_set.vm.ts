import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import apiService from '@modules/Core/shared/core/api_service';
import type { LovSetModel, LovSetFormData, LovSetFilters, LovSetMeta } from './lov_set.state';
import { LOV_SET_DEFAULT_FILTERS, LOV_SET_PER_PAGE_OPTIONS } from './lov_set.state';

export function createLovSetVMStore(storeId: string) {
    return defineStore(storeId, () => {
        const page = usePage<{ auth?: { user?: { roles?: string[]; permissions?: string[] } | null } }>();

        const items       = ref<LovSetModel[]>([]);
        const meta        = ref<LovSetMeta>({} as LovSetMeta);
        const isLoading   = ref(false);
        const isExporting = ref(false);
        const showForm    = ref(false);
        const editingItem = ref<LovSetModel | null>(null);
        const search      = ref(LOV_SET_DEFAULT_FILTERS.search);
        const perPage     = ref(LOV_SET_DEFAULT_FILTERS.per_page);
        const currentPage = ref(LOV_SET_DEFAULT_FILTERS.page);
        const perPageOptions = LOV_SET_PER_PAGE_OPTIONS;

        const canManage = computed(() => {
            const roles = page.props.auth?.user?.roles ?? [];
            if (['owner', 'superadmin', 'admin'].some(r => roles.includes(r))) return true;
            return (page.props.auth?.user?.permissions ?? []).includes('master.manage');
        });

        const form = useForm<LovSetFormData>({
            uuid: undefined, name: '', program_code: '', description: '',
        });

        async function fetchItems(filters: LovSetFilters = {}) {
            isLoading.value = true;
            try {
                const res = await apiService.get('/lov/sets', {
                    params: { search: search.value, per_page: perPage.value, page: currentPage.value, ...filters },
                });
                items.value = res.data.data ?? [];
                meta.value  = res.data.meta ?? {};
            } catch(e) { console.error(e); }
            finally { isLoading.value = false; }
        }

        function initFilters(filters: LovSetFilters = {}) {
            search.value      = filters.search   ?? LOV_SET_DEFAULT_FILTERS.search;
            perPage.value     = filters.per_page ?? LOV_SET_DEFAULT_FILTERS.per_page;
            currentPage.value = filters.page     ?? LOV_SET_DEFAULT_FILTERS.page;
            fetchItems();
        }

        function openCreate() { editingItem.value = null; form.reset(); showForm.value = true; }

        function openEdit(item: LovSetModel) {
            editingItem.value = item;
            form.uuid         = item.uuid;
            form.name         = item.name;
            form.program_code = item.program_code ?? '';
            form.description  = item.description ?? '';
            showForm.value    = true;
        }

        function closeForm() { showForm.value = false; editingItem.value = null; form.reset(); }

        async function submitForm() {
            const isEdit = !!editingItem.value;
            const url    = isEdit ? `/lov/sets/${editingItem.value!.uuid}` : '/lov/sets';
            try {
                if (isEdit) { await apiService.put(url, form.data()); }
                else        { await apiService.post(url, form.data()); }
                Swal.fire({ title: 'Berhasil!', text: isEdit ? 'Data diperbarui.' : 'Data baru ditambahkan.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
                closeForm(); fetchItems();
            } catch(e: any) {
                const errors = e.response?.data?.errors ?? {};
                Object.keys(errors).forEach(k => form.setError(k as any, errors[k][0]));
            }
        }

        async function deleteItem(item: LovSetModel) {
            const res = await Swal.fire({ title: 'Hapus Data?', text: `"${item.name}" tidak bisa dipulihkan.`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#ef4444' });
            if (!res.isConfirmed) return;
            await apiService.delete(`/lov/sets/${item.uuid}`);
            fetchItems();
        }

        async function exportItems() {
            isExporting.value = true;
            try {
                const res = await apiService.get('/lov/sets/export', { responseType: 'blob' });
                const url = window.URL.createObjectURL(new Blob([res.data]));
                const link = document.createElement('a');
                link.href = url; link.setAttribute('download', 'lov-sets.xlsx');
                document.body.appendChild(link); link.click(); link.remove();
                window.URL.revokeObjectURL(url);
            } catch(e) { console.error(e); }
            finally { isExporting.value = false; }
        }

        function changePage(url: string) {
            const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
            currentPage.value = p; fetchItems({ page: p });
        }

        return { items, meta, isLoading, isExporting, showForm, editingItem, canManage, form,
            search, perPage, perPageOptions, currentPage,
            fetchItems, initFilters, openCreate, openEdit, closeForm, submitForm, deleteItem, exportItems, changePage };
    });
}
