import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import apiService from '@modules/Core/shared/core/api_service';

export const useBranchVM = defineStore('branch-vm', () => {
    const page = usePage<{
        auth?: { user?: { roles?: string[]; permissions?: string[] } | null };
        flash?: { success?: string | null; error?: string | null };
        errors?: Record<string, string | undefined>;
    }>();

    const branches    = ref<any[]>([]);
    const meta        = ref<any>({});
    const isLoading   = ref(false);
    const showForm    = ref(false);
    const editingItem = ref<any | null>(null);
    const search      = ref('');
    const perPage     = ref(15);
    const currentPage = ref(1);
    const triggerReload = ref<(() => void) | null>(null);

        function registerReload(fn: () => void) {
            triggerReload.value = fn;
        }


    const canManage = computed(() => {
        const roles = page.props.auth?.user?.roles ?? [];
        return ['owner', 'superadmin', 'admin'].some(r => roles.includes(r));
    });

    const flash = computed(() => ({
        success: page.props.flash?.success ?? null,
        error: page.props.flash?.error ?? page.props.errors?.file ?? null,
    }));

    const form = useForm({
        uuid: undefined as string | undefined,
        name: '',
        short_name: '',
        description: '',
        is_active: true,
    });

    async function fetchBranches(filters: Record<string, any> = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/master/branches', {
                params: { search: search.value, per_page: perPage.value, page: currentPage.value, ...filters },
            });
            branches.value = res.data.data ?? [];
            meta.value     = res.data.meta  ?? {};
        } catch (e) { console.error(e); }
        finally     { isLoading.value = false; }
    }

    function initFilters(filters: Record<string, any>) {
        search.value      = filters.search   ?? '';
        perPage.value     = filters.per_page ?? 15;
        currentPage.value = filters.page     ?? 1;
        fetchBranches();
    }

    function openCreate() { editingItem.value = null; form.reset(); showForm.value = true; }

    function openEdit(item: any) {
        editingItem.value  = item;
        form.uuid          = item.uuid;
        form.name          = item.name;
        form.short_name    = item.short_name;
        form.description   = item.description ?? '';
        form.is_active     = item.is_active;
        showForm.value     = true;
    }

    function closeForm() { showForm.value = false; editingItem.value = null; form.reset(); }

    async function submitForm() {
        const isEdit = !!editingItem.value;
        const url    = isEdit ? `/master/branches/${editingItem.value.uuid}` : '/master/branches';
        try {
            if (isEdit) { await apiService.put(url, form.data()); }
            else        { await apiService.post(url, form.data()); }
            Swal.fire({ title: 'Berhasil!', text: isEdit ? 'Cabang diperbarui.' : 'Cabang baru ditambahkan.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
            closeForm(); fetchBranches();
            triggerReload.value?.();
        } catch (e: any) {
            const errors = e.response?.data?.errors ?? {};
            Object.keys(errors).forEach(k => form.setError(k as any, errors[k][0]));
        }
    }

    async function deleteBranch(uuid: string) {
        const res = await Swal.fire({ title: 'Hapus Cabang?', text: 'Data tidak bisa dipulihkan.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#ef4444' });
        if (!res.isConfirmed) return;
        await apiService.delete(`/master/branches/${uuid}`);
        fetchBranches();
        triggerReload.value?.();
    }

    
    const filterActive = ref<boolean | null>(null);

    const activeQueryParams = computed<Record<string, any>>(() => {
        const p: Record<string, any> = {};
        if (filterActive.value !== null) p.filter_active = filterActive.value;
        return p;
    });

    function setFilterActive(val: boolean | null) {
        filterActive.value = val;
    }
    return { branches, triggerReload,
            registerReload, filterActive, activeQueryParams, setFilterActive, meta, isLoading, showForm, editingItem, canManage, flash, form, search, perPage, currentPage, fetchBranches, initFilters, openCreate, openEdit, closeForm, submitForm, deleteBranch };
});
