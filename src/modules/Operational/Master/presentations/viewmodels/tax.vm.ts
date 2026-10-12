import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import apiService from '@modules/Core/shared/core/api_service';
import type { TaxModel, TaxFormData, TaxFilters, TaxMeta } from './tax.state';
import { TAX_DEFAULT_FILTERS, TAX_PER_PAGE_OPTIONS } from './tax.state';

export const useTaxVM = defineStore('tax-vm', () => {
    const page = usePage<{
        auth?: { user?: { roles?: string[]; permissions?: string[] } | null };
        flash?: { success?: string | null; error?: string | null };
        errors?: Record<string, string | undefined>;
    }>();

    const taxes       = ref<TaxModel[]>([]);
    const meta        = ref<TaxMeta>({} as TaxMeta);
    const isLoading   = ref(false);
    const showForm    = ref(false);
    const editingItem = ref<TaxModel | null>(null);
    const search      = ref(TAX_DEFAULT_FILTERS.search);
    const perPage     = ref(TAX_DEFAULT_FILTERS.per_page);
    const currentPage = ref(TAX_DEFAULT_FILTERS.page);

    const perPageOptions = TAX_PER_PAGE_OPTIONS;
    const triggerReload = ref<(() => void) | null>(null);

        function registerReload(fn: () => void) {
            triggerReload.value = fn;
        }


    const canManage = computed(() => {
        const roles = page.props.auth?.user?.roles ?? [];
        if (['owner','superadmin','admin'].some(r => roles.includes(r))) return true;
        return (page.props.auth?.user?.permissions ?? []).includes('master.manage');
    });

    const flash = computed(() => ({
        success: page.props.flash?.success ?? null,
        error: page.props.flash?.error ?? page.props.errors?.file ?? null,
    }));

    const form = useForm<TaxFormData>({
        uuid: undefined,
        tax_code: '',
        percentage: 0,
        is_active: true,
        is_primary: false,
        is_pph: false,
    });

    async function fetchTaxes(filters: TaxFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/masters/taxes', {
                params: { search: search.value, per_page: perPage.value, page: currentPage.value, ...filters },
            });
            taxes.value = res.data.data ?? [];
            meta.value  = res.data.meta  ?? {};
        } catch(e) { console.error(e); }
        finally    { isLoading.value = false; }
    }

    function initFilters(filters: TaxFilters) {
        search.value      = filters.search   ?? TAX_DEFAULT_FILTERS.search;
        perPage.value     = filters.per_page ?? TAX_DEFAULT_FILTERS.per_page;
        currentPage.value = filters.page     ?? TAX_DEFAULT_FILTERS.page;
        fetchTaxes();
    }

    function openCreate() { editingItem.value = null; form.reset(); showForm.value = true; }

    function openEdit(item: TaxModel) {
        editingItem.value = item;
        form.uuid       = item.uuid;
        form.tax_code   = item.tax_code;
        form.percentage = item.percentage;
        form.is_active  = item.is_active;
        form.is_primary = item.is_primary;
        form.is_pph     = item.is_pph ?? false;
        showForm.value  = true;
    }

    function closeForm() { showForm.value = false; editingItem.value = null; form.reset(); }

    async function submitForm() {
        const isEdit = !!editingItem.value;
        const url    = isEdit ? `/masters/taxes/${editingItem.value!.uuid}` : '/masters/taxes';
        try {
            if (isEdit) { await apiService.put(url, form.data()); }
            else        { await apiService.post(url, form.data()); }
            Swal.fire({ title: 'Berhasil!', text: isEdit ? 'Pajak diperbarui.' : 'Pajak baru ditambahkan.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
            closeForm(); fetchTaxes();
            triggerReload.value?.();
        } catch(e: any) {
            const errors = e.response?.data?.errors ?? {};
            Object.keys(errors).forEach(k => form.setError(k as any, errors[k][0]));
        }
    }

    async function deleteTax(uuid: string) {
        const res = await Swal.fire({ title: 'Hapus Pajak?', text: 'Data tidak bisa dipulihkan.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#ef4444' });
        if (!res.isConfirmed) return;
        await apiService.delete(`/masters/taxes/${uuid}`);
        fetchTaxes();
        triggerReload.value?.();
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p;
        fetchTaxes({ page: p });
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
    return { taxes, triggerReload,
            registerReload, filterActive, activeQueryParams, setFilterActive, meta, isLoading, showForm, editingItem, canManage, flash, form, search, perPage, perPageOptions, currentPage, fetchTaxes, initFilters, openCreate, openEdit, closeForm, submitForm, deleteTax, changePage };
});
