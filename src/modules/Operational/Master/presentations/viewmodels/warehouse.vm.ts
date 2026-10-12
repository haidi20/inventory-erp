import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import apiService from '@modules/Core/shared/core/api_service';
import type { WarehouseModel, WarehouseFormData, WarehouseFilters, WarehouseMeta } from './warehouse.state';
import { WAREHOUSE_DEFAULT_FILTERS, WAREHOUSE_SEARCH_COLUMNS, WAREHOUSE_PER_PAGE_OPTIONS } from './warehouse.state';

export const useWarehouseVM = defineStore('warehouse-vm', () => {
    const page = usePage<{
        auth?: { user?: { roles?: string[]; permissions?: string[] } | null };
        flash?: { success?: string | null; error?: string | null };
        errors?: Record<string, string | undefined>;
    }>();

    const warehouses  = ref<WarehouseModel[]>([]);
    const meta        = ref<WarehouseMeta>({} as WarehouseMeta);
    const isLoading   = ref(false);
    const showForm    = ref(false);
    const editingItem = ref<WarehouseModel | null>(null);
    const search      = ref(WAREHOUSE_DEFAULT_FILTERS.search);
    const searchColumn= ref(WAREHOUSE_DEFAULT_FILTERS.search_column);
    const sortBy      = ref(WAREHOUSE_DEFAULT_FILTERS.sort_by);
    const sortDir     = ref<'asc'|'desc'>(WAREHOUSE_DEFAULT_FILTERS.sort_direction);
    const perPage     = ref(WAREHOUSE_DEFAULT_FILTERS.per_page);
    const currentPage = ref(WAREHOUSE_DEFAULT_FILTERS.page);

    const searchColumns  = WAREHOUSE_SEARCH_COLUMNS;
    const perPageOptions = WAREHOUSE_PER_PAGE_OPTIONS;
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

    const form = useForm<WarehouseFormData>({
        uuid: undefined,
        code: '',
        name: '',
        description: '',
        is_active: true,
        can_sell: true,
        is_primary: false,
    });

    async function fetchWarehouses(filters: WarehouseFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/master/warehouses', {
                params: {
                    search: search.value, search_column: searchColumn.value,
                    sort_by: sortBy.value, sort_direction: sortDir.value,
                    per_page: perPage.value, page: currentPage.value,
                    ...filters,
                },
            });
            warehouses.value = res.data.data ?? [];
            meta.value       = res.data.meta  ?? {};
        } catch(e) { console.error(e); }
        finally    { isLoading.value = false; }
    }

    function initFilters(filters: WarehouseFilters) {
        search.value       = filters.search         ?? WAREHOUSE_DEFAULT_FILTERS.search;
        searchColumn.value = filters.search_column  ?? WAREHOUSE_DEFAULT_FILTERS.search_column;
        sortBy.value       = filters.sort_by        ?? WAREHOUSE_DEFAULT_FILTERS.sort_by;
        sortDir.value      = filters.sort_direction ?? WAREHOUSE_DEFAULT_FILTERS.sort_direction;
        perPage.value      = filters.per_page       ?? WAREHOUSE_DEFAULT_FILTERS.per_page;
        currentPage.value  = filters.page           ?? WAREHOUSE_DEFAULT_FILTERS.page;
        fetchWarehouses();
    }

    function openCreate() { editingItem.value = null; form.reset(); showForm.value = true; }

    function openEdit(item: WarehouseModel) {
        editingItem.value = item;
        form.uuid        = item.uuid;
        form.code        = item.code;
        form.name        = item.name;
        form.description = item.description ?? '';
        form.is_active   = item.is_active;
        form.can_sell    = item.can_sell;
        form.is_primary  = item.is_primary;
        showForm.value   = true;
    }

    function closeForm() { showForm.value = false; editingItem.value = null; form.reset(); }

    async function submitForm() {
        const isEdit = !!editingItem.value;
        const url    = isEdit ? `/master/warehouses/${editingItem.value!.uuid}` : '/master/warehouses';
        try {
            if (isEdit) { await apiService.put(url, form.data()); }
            else        { await apiService.post(url, form.data()); }
            Swal.fire({ title: 'Berhasil!', text: isEdit ? 'Gudang diperbarui.' : 'Gudang baru ditambahkan.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
            closeForm(); fetchWarehouses();
            triggerReload.value?.();
        } catch(e: any) {
            const errors = e.response?.data?.errors ?? {};
            Object.keys(errors).forEach(k => form.setError(k as any, errors[k][0]));
        }
    }

    async function deleteWarehouse(uuid: string) {
        const res = await Swal.fire({ title: 'Hapus Gudang?', text: 'Data tidak bisa dipulihkan.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#ef4444' });
        if (!res.isConfirmed) return;
        await apiService.delete(`/master/warehouses/${uuid}`);
        fetchWarehouses();
        triggerReload.value?.();
    }

    function toggleSort(col: string) {
        if (sortBy.value === col) { sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'; }
        else { sortBy.value = col; sortDir.value = 'asc'; }
        currentPage.value = 1;
        fetchWarehouses({ page: 1 });
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p;
        fetchWarehouses({ page: p });
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
    return { warehouses, triggerReload,
            registerReload, filterActive, activeQueryParams, setFilterActive, meta, isLoading, showForm, editingItem, canManage, flash, form, search, searchColumn, searchColumns, perPageOptions, sortBy, sortDir, perPage, currentPage, fetchWarehouses, initFilters, openCreate, openEdit, closeForm, submitForm, deleteWarehouse, toggleSort, changePage };
});
