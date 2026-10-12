import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import apiService from '@modules/Core/shared/core/api_service';
import type { TreatmentPackageModel, TreatmentPackageFormData, TreatmentPackageFilters, TreatmentPackageMeta } from './treatment_package.state';
import { TREATMENT_PACKAGE_DEFAULT_FILTERS, TREATMENT_PACKAGE_PER_PAGE_OPTIONS, TREATMENT_PACKAGE_SEARCH_COLUMNS } from './treatment_package.state';

export const useTreatmentPackageVM = defineStore('treatment-package-vm', () => {
    const page = usePage<{ auth?: { user?: { roles?: string[]; permissions?: string[] } | null } }>();

    const packages    = ref<TreatmentPackageModel[]>([]);
    const meta        = ref<TreatmentPackageMeta>({} as TreatmentPackageMeta);
    const isLoading   = ref(false);
    const showForm    = ref(false);
    const editingItem = ref<TreatmentPackageModel | null>(null);
    const search      = ref(TREATMENT_PACKAGE_DEFAULT_FILTERS.search);
    const searchColumn= ref(TREATMENT_PACKAGE_DEFAULT_FILTERS.search_column);
    const perPage     = ref(TREATMENT_PACKAGE_DEFAULT_FILTERS.per_page);
    const currentPage = ref(TREATMENT_PACKAGE_DEFAULT_FILTERS.page);

    const searchColumns  = TREATMENT_PACKAGE_SEARCH_COLUMNS;
    const perPageOptions = TREATMENT_PACKAGE_PER_PAGE_OPTIONS;

    const canManage = computed(() => {
        const roles = page.props.auth?.user?.roles ?? [];
        if (['owner','superadmin','admin'].some(r => roles.includes(r))) return true;
        return (page.props.auth?.user?.permissions ?? []).includes('master.manage');
    });

    const form = useForm<TreatmentPackageFormData>({
        uuid: undefined, code: '', name: '', price: 0, is_active: true,
    });

    async function fetchPackages(filters: TreatmentPackageFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/masters/treatment-packages', {
                params: { search: search.value, search_column: searchColumn.value,
                    per_page: perPage.value, page: currentPage.value, ...filters },
            });
            packages.value = res.data.data ?? [];
            meta.value = res.data.meta ?? {};
        } catch(e) { console.error(e); }
        finally { isLoading.value = false; }
    }

    function initFilters(filters: TreatmentPackageFilters = {}) {
        search.value       = filters.search        ?? TREATMENT_PACKAGE_DEFAULT_FILTERS.search;
        searchColumn.value = filters.search_column ?? TREATMENT_PACKAGE_DEFAULT_FILTERS.search_column;
        perPage.value      = filters.per_page      ?? TREATMENT_PACKAGE_DEFAULT_FILTERS.per_page;
        currentPage.value  = filters.page          ?? TREATMENT_PACKAGE_DEFAULT_FILTERS.page;
        fetchPackages();
    }

    function openCreate() { editingItem.value = null; form.reset(); showForm.value = true; }

    function openEdit(item: TreatmentPackageModel) {
        editingItem.value = item;
        form.uuid      = item.uuid;
        form.code      = item.code ?? '';
        form.name      = item.name;
        form.price     = item.price ?? 0;
        form.is_active = item.is_active;
        showForm.value = true;
    }

    function closeForm() { showForm.value = false; editingItem.value = null; form.reset(); }

    async function submitForm() {
        const isEdit = !!editingItem.value;
        const url = isEdit ? `/masters/treatment-packages/${editingItem.value!.uuid}` : '/masters/treatment-packages';
        try {
            if (isEdit) { await apiService.put(url, form.data()); }
            else        { await apiService.post(url, form.data()); }
            Swal.fire({ title: 'Berhasil!', text: isEdit ? 'Paket diperbarui.' : 'Paket baru ditambahkan.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
            closeForm(); fetchPackages();
        } catch(e: any) {
            const errors = e.response?.data?.errors ?? {};
            Object.keys(errors).forEach(k => form.setError(k as any, errors[k][0]));
        }
    }

    async function deletePackage(item: TreatmentPackageModel) {
        const res = await Swal.fire({ title: 'Hapus Paket?', text: `"${item.name}" tidak bisa dipulihkan.`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#ef4444' });
        if (!res.isConfirmed) return;
        await apiService.delete(`/masters/treatment-packages/${item.uuid}`);
        fetchPackages();
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p; fetchPackages({ page: p });
    }

    return { packages, meta, isLoading, showForm, editingItem, canManage, form,
        search, searchColumn, searchColumns, perPage, perPageOptions, currentPage,
        fetchPackages, initFilters, openCreate, openEdit, closeForm, submitForm, deletePackage, changePage };
});
