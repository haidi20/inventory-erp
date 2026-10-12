import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import apiService from '@modules/Core/shared/core/api_service';
import type { EmployeeModel, EmployeeFormData, EmployeeFilters, EmployeeMeta } from './employee.state';
import { EMPLOYEE_DEFAULT_FILTERS, EMPLOYEE_PER_PAGE_OPTIONS, EMPLOYEE_SEARCH_COLUMNS } from './employee.state';

export const useEmployeeVM = defineStore('employee-vm', () => {
    const page = usePage<{ auth?: { user?: { roles?: string[]; permissions?: string[] } | null } }>();

    const employees   = ref<EmployeeModel[]>([]);
    const meta        = ref<EmployeeMeta>({} as EmployeeMeta);
    const isLoading   = ref(false);
    const showForm    = ref(false);
    const editingItem = ref<EmployeeModel | null>(null);
    const search      = ref(EMPLOYEE_DEFAULT_FILTERS.search);
    const searchColumn= ref(EMPLOYEE_DEFAULT_FILTERS.search_column);
    const perPage     = ref(EMPLOYEE_DEFAULT_FILTERS.per_page);
    const currentPage = ref(EMPLOYEE_DEFAULT_FILTERS.page);

    const searchColumns  = EMPLOYEE_SEARCH_COLUMNS;
    const perPageOptions = EMPLOYEE_PER_PAGE_OPTIONS;

    const canManage = computed(() => {
        const roles = page.props.auth?.user?.roles ?? [];
        if (['owner','superadmin','admin'].some(r => roles.includes(r))) return true;
        return (page.props.auth?.user?.permissions ?? []).includes('master.manage');
    });

    const form = useForm<EmployeeFormData>({
        uuid: undefined, code: '', name: '', position: '',
        phone: '', email: '', is_active: true,
    });

    async function fetchEmployees(filters: EmployeeFilters = {}) {
        isLoading.value = true;
        try {
            const res = await apiService.get('/employees', {
                params: { search: search.value, search_column: searchColumn.value,
                    per_page: perPage.value, page: currentPage.value, ...filters },
            });
            employees.value = res.data.data ?? [];
            meta.value = res.data.meta ?? {};
        } catch(e) { console.error(e); }
        finally { isLoading.value = false; }
    }

    function initFilters(filters: EmployeeFilters = {}) {
        search.value       = filters.search        ?? EMPLOYEE_DEFAULT_FILTERS.search;
        searchColumn.value = filters.search_column ?? EMPLOYEE_DEFAULT_FILTERS.search_column;
        perPage.value      = filters.per_page      ?? EMPLOYEE_DEFAULT_FILTERS.per_page;
        currentPage.value  = filters.page          ?? EMPLOYEE_DEFAULT_FILTERS.page;
        fetchEmployees();
    }

    function openCreate() { editingItem.value = null; form.reset(); showForm.value = true; }

    function openEdit(item: EmployeeModel) {
        editingItem.value = item;
        form.uuid     = item.uuid;
        form.code     = item.code ?? '';
        form.name     = item.name;
        form.position = item.position ?? '';
        form.phone    = item.phone ?? '';
        form.email    = item.email ?? '';
        form.is_active = item.is_active;
        showForm.value = true;
    }

    function closeForm() { showForm.value = false; editingItem.value = null; form.reset(); }

    async function submitForm() {
        const isEdit = !!editingItem.value;
        const url = isEdit ? `/employees/${editingItem.value!.uuid}` : '/employees';
        try {
            if (isEdit) { await apiService.put(url, form.data()); }
            else        { await apiService.post(url, form.data()); }
            Swal.fire({ title: 'Berhasil!', text: isEdit ? 'Karyawan diperbarui.' : 'Karyawan baru ditambahkan.', icon: 'success', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false, timerProgressBar: true });
            closeForm(); fetchEmployees();
        } catch(e: any) {
            const errors = e.response?.data?.errors ?? {};
            Object.keys(errors).forEach(k => form.setError(k as any, errors[k][0]));
        }
    }

    async function deleteEmployee(item: EmployeeModel) {
        const res = await Swal.fire({ title: 'Hapus Karyawan?', text: `"${item.name}" tidak bisa dipulihkan.`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#ef4444' });
        if (!res.isConfirmed) return;
        await apiService.delete(`/employees/${item.uuid}`);
        fetchEmployees();
    }

    function changePage(url: string) {
        const p = Number(new URL(url, window.location.origin).searchParams.get('page') || '1');
        currentPage.value = p; fetchEmployees({ page: p });
    }

    return { employees, meta, isLoading, showForm, editingItem, canManage, form,
        search, searchColumn, searchColumns, perPage, perPageOptions, currentPage,
        fetchEmployees, initFilters, openCreate, openEdit, closeForm, submitForm, deleteEmployee, changePage };
});
