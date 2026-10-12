import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import apiService from '@modules/Core/shared/core/api_service';
import type { PaymentTermModel, PaymentTermFormData, PaymentTermFilters, PaymentTermMeta } from './payment_term.state';
import { PAYMENT_TERM_DEFAULT_FILTERS, PAYMENT_TERM_PER_PAGE_OPTIONS } from './payment_term.state';

export const usePaymentTermVM = defineStore('payment-term-vm', () => {
    const page = usePage<{
        auth?: { user?: { roles?: string[]; permissions?: string[] } | null };
        flash?: { success?: string | null; error?: string | null };
        errors?: Record<string, string | undefined>;
    }>();

    const terms       = ref<PaymentTermModel[]>([]);
    const meta        = ref<PaymentTermMeta>({} as PaymentTermMeta);
    const isLoading   = ref(false);
    const showForm    = ref(false);
    const editingItem = ref<PaymentTermModel | null>(null);
    const search      = ref(PAYMENT_TERM_DEFAULT_FILTERS.search);
    const perPage     = ref(PAYMENT_TERM_DEFAULT_FILTERS.per_page);
    const currentPage = ref(PAYMENT_TERM_DEFAULT_FILTERS.page);

    const perPageOptions = PAYMENT_TERM_PER_PAGE_OPTIONS;
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

    const form = useForm<PaymentTermFormData>({
        uuid: undefined,
        name: '',
        description: '',
        due_days: 0,
        is_active: true,
        is_primary: false,
    });

    async function fetchTerms(filters: PaymentTermFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/masters/payment-terms', {
                params: { search: search.value, per_page: perPage.value, page: currentPage.value, ...filters },
            });
            terms.value = res.data.data ?? [];
            meta.value  = res.data.meta  ?? {};
        } catch(e) { console.error(e); }
        finally    { isLoading.value = false; }
    }

    function initFilters(filters: PaymentTermFilters) {
        search.value      = filters.search   ?? PAYMENT_TERM_DEFAULT_FILTERS.search;
        perPage.value     = filters.per_page ?? PAYMENT_TERM_DEFAULT_FILTERS.per_page;
        currentPage.value = filters.page     ?? PAYMENT_TERM_DEFAULT_FILTERS.page;
        fetchTerms();
    }

    function openCreate() { editingItem.value = null; form.reset(); showForm.value = true; }

    function openEdit(item: PaymentTermModel) {
        editingItem.value = item;
        form.uuid        = item.uuid;
        form.name        = item.name;
        form.description = item.description ?? '';
        form.due_days    = item.due_days;
        form.is_active   = item.is_active;
        form.is_primary  = item.is_primary;
        showForm.value   = true;
    }

    function closeForm() { showForm.value = false; editingItem.value = null; form.reset(); }

    async function submitForm() {
        const isEdit = !!editingItem.value;
        const url    = isEdit ? `/masters/payment-terms/${editingItem.value!.uuid}` : '/masters/payment-terms';
        try {
            if (isEdit) { await apiService.put(url, form.data()); }
            else        { await apiService.post(url, form.data()); }
            Swal.fire({ title: 'Berhasil!', text: isEdit ? 'Termin diperbarui.' : 'Termin baru ditambahkan.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
            closeForm(); fetchTerms();
            triggerReload.value?.();
        } catch(e: any) {
            const errors = e.response?.data?.errors ?? {};
            Object.keys(errors).forEach(k => form.setError(k as any, errors[k][0]));
        }
    }

    async function deleteTerm(uuid: string) {
        const res = await Swal.fire({ title: 'Hapus Termin?', text: 'Data tidak bisa dipulihkan.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#ef4444' });
        if (!res.isConfirmed) return;
        await apiService.delete(`/masters/payment-terms/${uuid}`);
        fetchTerms();
        triggerReload.value?.();
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p;
        fetchTerms({ page: p });
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
    return { terms, triggerReload,
            registerReload, filterActive, activeQueryParams, setFilterActive, meta, isLoading, showForm, editingItem, canManage, flash, form, search, perPage, perPageOptions, currentPage, fetchTerms, initFilters, openCreate, openEdit, closeForm, submitForm, deleteTerm, changePage };
});
