import type { FarmacyAuthPageProps, FarmacyFormField, FarmacySortableColumn } from './farmacy.state';
import { defineStore } from 'pinia';
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { farmacyDependencies } from '@modules/Clinical/Pharmacy/domain/di/farmacy.di';
import { bindImportMenuDismiss } from '@modules/Core/shared/core/helpers/import_menu';
import { ValidationFailure } from '@modules/Core/shared/core/failures';
import type { 
    FarmacyModel, 
    FarmacyFilters, 
    FarmacySummary, 
    FarmacyPaginationMeta,
    FarmacyFormData
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';
import type { FarmacyRoleItem } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';
import { FormMode } from '@modules/Core/shared/core/domain/models/form_mode.model';

function debounce<TArgs extends unknown[]>(fn: (...args: TArgs) => void | Promise<void>, delay: number) {
    let timeoutId: ReturnType<typeof setTimeout>;
    return (...args: TArgs) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

export const useFarmacyVM = defineStore('farmacy-vm', () => {
    const { 
        fetchFarmacyUseCase,
        saveFarmacyUseCase,
        deleteFarmacyUseCase,
        exportFarmacyUseCase,
        importFarmacyUseCase,
        downloadFarmacyImportTemplateUseCase
    } = farmacyDependencies;

    const page = usePage();

    // --- State ---
    const Farmacies = ref<FarmacyModel[]>([]);
    const meta = ref<FarmacyPaginationMeta | null>(null);
    const summary = ref<FarmacySummary | null>(null);
    const isLoading = ref(false);
    const isExporting = ref(false);
    const isImporting = ref(false);
    const showImportMenu = ref(false);
    const showForm = ref(false);
    const editingFarmacy = ref<FarmacyModel | null>(null);

    // --- Constant Options ---
    const searchColumns = [
        { id: 'all', text: 'Semua Kolom' },
        { id: 'name', text: 'Nama' },
        { id: 'email', text: 'Email' },
    ];

    const perPageOptions = [
        { id: 5, text: '5' },
        { id: 10, text: '10' },
        { id: 25, text: '25' },
        { id: 50, text: '50' },
        { id: 100, text: '100' },
    ];

    // --- Filters ---
    const search = ref('');
    const searchColumn = ref('all');
    const roleFilter = ref('');
    const sortBy = ref<FarmacySortableColumn>('name');
    const sortDirection = ref<'asc' | 'desc'>('asc');
    const currentPage = ref(1);
    const perPage = ref(5);

    const form = useForm<FarmacyFormData>({
        name: '',
        email: '',
        password: '',
        role_uuids: [],
        employee_uuid: '',
    });
    const roleOptions = ref<FarmacyRoleItem[]>([]);
    const roleSearch = ref('');

    const filteredRoles = computed(() => {
        const query = roleSearch.value.toLowerCase().trim();
        if (!query) return roleOptions.value;

        return roleOptions.value.filter((role) =>
            role.label?.toLowerCase().includes(query) || role.name?.toLowerCase().includes(query)
        );
    });
    const formMode = computed(() => editingFarmacy.value ? FormMode.EDIT : FormMode.CREATE);

    const initRoleOptions = (roles: FarmacyRoleItem[]) => {
        roleOptions.value = roles;
    };

    // --- Computed ---
    const canManageFarmacy = computed(() => {
        const props = page.props as FarmacyAuthPageProps;
        return props.auth?.user?.permissions?.includes('master.manage') ?? false;
    });

    const flash = computed(() => {
        const props = page.props as FarmacyAuthPageProps;
        return {
            success: props.flash?.success ?? null,
            error: props.flash?.error ?? props.errors?.file ?? null,
        };
    });

    // --- Actions ---
    const initFilters = (initialFilters: FarmacyFilters) => {
        search.value = initialFilters.search || '';
        searchColumn.value = initialFilters.search_column || 'all';
        roleFilter.value = initialFilters.role || '';
        sortBy.value = initialFilters.sort_by || 'name';
        sortDirection.value = initialFilters.sort_direction || 'asc';
        currentPage.value = initialFilters.page || 1;
        perPage.value = initialFilters.per_page || 5;
    };

    const fetchFarmacy = async () => {
        isLoading.value = true;
        const result = await fetchFarmacyUseCase.execute({
            search: search.value,
            search_column: searchColumn.value,
            role: roleFilter.value,
            sort_by: sortBy.value,
            sort_direction: sortDirection.value,
            page: currentPage.value,
            per_page: perPage.value,
        });

        result.fold(
            (failure) => {
                console.error(failure.message);
            },
            (success) => {
                Farmacies.value = success.data;
                meta.value = success.meta;
                summary.value = success.summary;
            }
        );
        isLoading.value = false;
    };

    const debouncedFetch = debounce(fetchFarmacy, 300);

    const toggleSort = (column: FarmacySortableColumn) => {
        if (sortBy.value === column) {
            sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortBy.value = column;
            sortDirection.value = 'asc';
        }
        fetchFarmacy();
    };

    const openCreate = () => {
        editingFarmacy.value = null;
        form.reset();
        form.clearErrors();
        showForm.value = true;
    };

    const openEdit = (farmacy: FarmacyModel) => {
        editingFarmacy.value = farmacy;
        form.name = farmacy.name;
        form.email = farmacy.email;
        form.password = '';
        form.role_uuids = farmacy.role_uuids || [];
        form.employee_uuid = farmacy.employee_uuid || '';
        form.clearErrors();
        showForm.value = true;
    };

    const closeForm = () => {
        showForm.value = false;
        editingFarmacy.value = null;
        form.reset();
    };

    const submitForm = async () => {
        const result = await saveFarmacyUseCase.execute({
            ...form.data(),
            uuid: editingFarmacy.value?.uuid
        });

        result.fold(
            (failure) => {
                if (failure instanceof ValidationFailure) {
                    Object.entries(failure.errors).forEach(([key, messages]) => {
                        form.setError(key as FarmacyFormField, messages[0] ?? 'Data tidak valid.');
                    });
                }
            },
            () => {
                closeForm();
                fetchFarmacy();
            }
        );
    };

    const deleteFarmacy = async (farmacy: FarmacyModel) => {
        if (!confirm(`Hapus farmacy ${farmacy.name}?`)) return;
        
        const result = await deleteFarmacyUseCase.execute(farmacy.uuid);
        result.fold(
            (failure) => { console.error(failure.message); },
            () => { fetchFarmacy(); }
        );
    };

    const exportFarmacy = async () => {
        isExporting.value = true;
        await exportFarmacyUseCase.execute({
            search: search.value,
            role: roleFilter.value
        });
        isExporting.value = false;
    };

    const importFarmacy = async (file: File) => {
        isImporting.value = true;
        const result = await importFarmacyUseCase.execute(file);
        result.fold(
            (failure) => { console.error(failure.message); },
            () => { fetchFarmacy(); }
        );
        isImporting.value = false;
    };

    const openImportPicker = () => {
        showImportMenu.value = false;
        document.getElementById('importInput')?.click();
    };

    const toggleImportMenu = (event?: MouseEvent) => {
        event?.stopPropagation();
        showImportMenu.value = !showImportMenu.value;
    };

    const closeImportMenu = () => {
        showImportMenu.value = false;
    };

    const openImportData = () => {
        openImportPicker();
    };

    const downloadImportTemplate = async () => {
        showImportMenu.value = false;
        await downloadFarmacyImportTemplateUseCase.execute();
    };

    const handleImport = async (event: Event) => {
        const target = event.target as HTMLInputElement;
        if (!target.files?.length) return;
        await importFarmacy(target.files[0]);
        target.value = '';
    };

    let unbindImportMenuDismiss: (() => void) | null = null;
    const initImportMenuDismiss = () => {
        unbindImportMenuDismiss = bindImportMenuDismiss(closeImportMenu);
    };
    const disposeImportMenuDismiss = () => {
        unbindImportMenuDismiss?.();
        unbindImportMenuDismiss = null;
    };

    const formatRoleLabels = (farmacy: FarmacyModel) => {
        return (farmacy.roles || []).map((role) => role.label).join(', ');
    };

    watch(() => (page.props as FarmacyAuthPageProps).auth?.api_token, (token) => {
        if (token) void fetchFarmacy();
    }, { immediate: true });

    return {
        Farmacies,
        meta,
        summary,
        isLoading,
        isExporting,
        isImporting,
        showImportMenu,
        showForm,
        editingFarmacy,
        searchColumns,
        perPageOptions,
        search,
        searchColumn,
        roleFilter,
        sortBy,
        sortDirection,
        currentPage,
        perPage,
        form,
        roleSearch,
        filteredRoles,
        formMode,
        initRoleOptions,
        canManageFarmacy,
        flash,
        initFilters,
        fetchFarmacy,
        debouncedFetch,
        toggleSort,
        openCreate,
        openEdit,
        closeForm,
        submitForm,
        deleteFarmacy,
        exportFarmacy,
        importFarmacy,
        downloadImportTemplate,
        openImportPicker,
        toggleImportMenu,
        closeImportMenu,
        openImportData,
        handleImport,
        formatRoleLabels,
        initImportMenuDismiss,
        disposeImportMenuDismiss
    };
});
