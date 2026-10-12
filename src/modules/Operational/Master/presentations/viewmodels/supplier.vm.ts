import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import apiService from '@modules/Core/shared/core/api_service';
import type { SupplierModel, SupplierFormData, SupplierFilters, SupplierMeta } from './supplier.state';
import { SUPPLIER_DEFAULT_FILTERS, SUPPLIER_PER_PAGE_OPTIONS, SUPPLIER_SEARCH_COLUMNS } from './supplier.state';

export const useSupplierVM = defineStore('supplier-vm', () => {
    const page = usePage<{
        auth?: { user?: { roles?: string[]; permissions?: string[] } | null };
        flash?: { success?: string | null; error?: string | null };
        errors?: Record<string, string | undefined>;
    }>();

    const suppliers   = ref<SupplierModel[]>([]);
    const meta        = ref<SupplierMeta>({} as SupplierMeta);
    const isLoading   = ref(false);
    const showForm    = ref(false);
    const editingItem = ref<SupplierModel | null>(null);
    const search      = ref(SUPPLIER_DEFAULT_FILTERS.search);
    const searchColumn= ref(SUPPLIER_DEFAULT_FILTERS.search_column);
    const sortBy      = ref(SUPPLIER_DEFAULT_FILTERS.sort_by);
    const sortDir     = ref<'asc'|'desc'>(SUPPLIER_DEFAULT_FILTERS.sort_direction);
    const perPage     = ref(SUPPLIER_DEFAULT_FILTERS.per_page);
    const currentPage = ref(SUPPLIER_DEFAULT_FILTERS.page);

    const searchColumns  = SUPPLIER_SEARCH_COLUMNS;
    const perPageOptions = SUPPLIER_PER_PAGE_OPTIONS;
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

    const form = useForm<SupplierFormData>({
        uuid: undefined, name: '', phone: '', email: '',
        address: '', term_days: 0, is_active: true,
    });

    async function fetchSuppliers(filters: SupplierFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/suppliers', {
                params: { search: search.value, search_column: searchColumn.value,
                    sort_by: sortBy.value, sort_direction: sortDir.value,
                    per_page: perPage.value, page: currentPage.value, ...filters },
            });
            suppliers.value = res.data.data ?? [];
            meta.value = res.data.meta ?? {};
        } catch(e) { console.error(e); }
        finally { isLoading.value = false; }
    }

    function initFilters(filters: SupplierFilters = {}) {
        search.value       = filters.search          ?? SUPPLIER_DEFAULT_FILTERS.search;
        searchColumn.value = filters.search_column   ?? SUPPLIER_DEFAULT_FILTERS.search_column;
        sortBy.value       = filters.sort_by         ?? SUPPLIER_DEFAULT_FILTERS.sort_by;
        sortDir.value      = filters.sort_direction  ?? SUPPLIER_DEFAULT_FILTERS.sort_direction;
        perPage.value      = filters.per_page        ?? SUPPLIER_DEFAULT_FILTERS.per_page;
        currentPage.value  = filters.page            ?? SUPPLIER_DEFAULT_FILTERS.page;
        fetchSuppliers();
    }

    function openCreate() { editingItem.value = null; form.reset(); showForm.value = true; }

    function openEdit(item: SupplierModel) {
        editingItem.value = item;
        form.uuid    = item.uuid;
        form.name    = item.name;
        form.phone   = item.phone ?? '';
        form.email   = item.email ?? '';
        form.address = item.address ?? '';
        form.term_days = item.term_days ?? 0;
        form.is_active = item.is_active;
        showForm.value = true;
    }

    function closeForm() { showForm.value = false; editingItem.value = null; form.reset(); }

    async function submitForm() {
        const isEdit = !!editingItem.value;
        const url = isEdit ? `/suppliers/${editingItem.value!.uuid}` : '/suppliers';
        try {
            if (isEdit) { await apiService.put(url, form.data()); }
            else        { await apiService.post(url, form.data()); }
            Swal.fire({ title: 'Berhasil!', text: isEdit ? 'Supplier diperbarui.' : 'Supplier baru ditambahkan.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
            closeForm(); fetchSuppliers();
            triggerReload.value?.();
        } catch(e: any) {
            const errors = e.response?.data?.errors ?? {};
            Object.keys(errors).forEach(k => form.setError(k as any, errors[k][0]));
        }
    }

    async function deleteSupplier(item: SupplierModel) {
        const res = await Swal.fire({ title: 'Hapus Supplier?', text: `"${item.name}" tidak bisa dipulihkan.`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#ef4444' });
        if (!res.isConfirmed) return;
        await apiService.delete(`/suppliers/${item.uuid}`);
        fetchSuppliers();
        triggerReload.value?.();
    }

    function toggleSort(col: string) {
        if (sortBy.value === col) { sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'; }
        else { sortBy.value = col; sortDir.value = 'asc'; }
        currentPage.value = 1; fetchSuppliers({ page: 1 });
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p; fetchSuppliers({ page: p });
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
    return { suppliers, triggerReload,
            registerReload, filterActive, activeQueryParams, setFilterActive, meta, isLoading, showForm, editingItem, canManage, flash, form,
        search, searchColumn, searchColumns, sortBy, sortDir, perPage, perPageOptions, currentPage,
        fetchSuppliers, initFilters, openCreate, openEdit, closeForm, submitForm, deleteSupplier, toggleSort, changePage };
});
