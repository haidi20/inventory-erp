import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import apiService from '@modules/Core/shared/core/api_service';
import type { TreatmentModel, TreatmentFormData, TreatmentFilters, TreatmentMeta } from './treatment.state';
import { TREATMENT_DEFAULT_FILTERS, TREATMENT_PER_PAGE_OPTIONS, TREATMENT_SEARCH_COLUMNS } from './treatment.state';

export const useTreatmentVM = defineStore('treatment-vm', () => {
    const page = usePage<{ auth?: { user?: { roles?: string[]; permissions?: string[] } | null } }>();

    const treatments  = ref<TreatmentModel[]>([]);
    const meta        = ref<TreatmentMeta>({} as TreatmentMeta);
    const isLoading   = ref(false);
    const showForm    = ref(false);
    const editingItem = ref<TreatmentModel | null>(null);
    const search      = ref(TREATMENT_DEFAULT_FILTERS.search);
    const searchColumn= ref(TREATMENT_DEFAULT_FILTERS.search_column);
    const perPage     = ref(TREATMENT_DEFAULT_FILTERS.per_page);
    const currentPage = ref(TREATMENT_DEFAULT_FILTERS.page);

    const searchColumns  = TREATMENT_SEARCH_COLUMNS;
    const perPageOptions = TREATMENT_PER_PAGE_OPTIONS;

    const canManage = computed(() => {
        const roles = page.props.auth?.user?.roles ?? [];
        if (['owner','superadmin','admin'].some(r => roles.includes(r))) return true;
        return (page.props.auth?.user?.permissions ?? []).includes('master.manage');
    });

    const form = useForm<TreatmentFormData>({
        uuid: undefined, code: '', name: '',
        duration_minutes: 0, price: 0, doctor_fee: 0, is_active: true,
    });

    async function fetchTreatments(filters: TreatmentFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/masters/treatments', {
                params: { search: search.value, search_column: searchColumn.value,
                    per_page: perPage.value, page: currentPage.value, ...filters },
            });
            treatments.value = res.data.data ?? [];
            meta.value = res.data.meta ?? {};
        } catch(e) { console.error(e); }
        finally { isLoading.value = false; }
    }

    function initFilters(filters: TreatmentFilters = {}) {
        search.value       = filters.search        ?? TREATMENT_DEFAULT_FILTERS.search;
        searchColumn.value = filters.search_column ?? TREATMENT_DEFAULT_FILTERS.search_column;
        perPage.value      = filters.per_page      ?? TREATMENT_DEFAULT_FILTERS.per_page;
        currentPage.value  = filters.page          ?? TREATMENT_DEFAULT_FILTERS.page;
        fetchTreatments();
    }

    function openCreate() { editingItem.value = null; form.reset(); showForm.value = true; }

    function openEdit(item: TreatmentModel) {
        editingItem.value = item;
        form.uuid             = item.uuid;
        form.code             = item.code ?? '';
        form.name             = item.name;
        form.duration_minutes = item.duration_minutes ?? 0;
        form.price            = item.price ?? 0;
        form.doctor_fee       = item.doctor_fee ?? 0;
        form.is_active        = item.is_active;
        showForm.value = true;
    }

    function closeForm() { showForm.value = false; editingItem.value = null; form.reset(); }

    async function submitForm() {
        const isEdit = !!editingItem.value;
        const url = isEdit ? `/masters/treatments/${editingItem.value!.uuid}` : '/masters/treatments';
        try {
            if (isEdit) { await apiService.put(url, form.data()); }
            else        { await apiService.post(url, form.data()); }
            Swal.fire({ title: 'Berhasil!', text: isEdit ? 'Tindakan diperbarui.' : 'Tindakan baru ditambahkan.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
            closeForm(); fetchTreatments();
        } catch(e: any) {
            const errors = e.response?.data?.errors ?? {};
            Object.keys(errors).forEach(k => form.setError(k as any, errors[k][0]));
        }
    }

    async function deleteTreatment(item: TreatmentModel) {
        const res = await Swal.fire({ title: 'Hapus Tindakan?', text: `"${item.name}" tidak bisa dipulihkan.`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#ef4444' });
        if (!res.isConfirmed) return;
        await apiService.delete(`/masters/treatments/${item.uuid}`);
        fetchTreatments();
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p; fetchTreatments({ page: p });
    }

    return { treatments, meta, isLoading, showForm, editingItem, canManage, form,
        search, searchColumn, searchColumns, perPage, perPageOptions, currentPage,
        fetchTreatments, initFilters, openCreate, openEdit, closeForm, submitForm, deleteTreatment, changePage };
});
