import type { LookupPageProps } from './lookup.state';
import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import * as lookupDI from '@modules/Operational/Master/domain/di/lookup.di';
import type { LookupVMDependencies } from '@modules/Operational/Master/domain/di/lookup.di';
import type { LookupFilters, LookupFormData, LookupModel } from '@modules/Operational/Master/domain/models/lookup.model';
import { ValidationFailure } from '@modules/Core/shared/core/failures';
import { bindImportMenuDismiss } from '@modules/Core/shared/core/helpers/import_menu';
import { FormMode } from '@modules/Core/shared/core/domain/models/form_mode.model';

function debounce(fn: Function, delay: number) {
    let timeoutId: ReturnType<typeof setTimeout> | undefined;

    return function (...args: any[]) {
        if (timeoutId) {
            clearTimeout(timeoutId);
        }

        timeoutId = setTimeout(() => fn.apply(null, args), delay);
    };
}

export function createLookupVMStore(
    storeId = 'lookup-vm',
    dependencies: LookupVMDependencies = lookupDI,
) {
    return defineStore(storeId, () => {
        const {
            fetchLookupsUseCase,
            saveLookupUseCase,
            deleteLookupUseCase,
            exportLookupsUseCase,
            importLookupsUseCase,
            downloadLookupImportTemplateUseCase,
        } = dependencies;

        const lookups = ref<LookupModel[]>([]);
        const meta = ref<any>({});
        const summary = ref({ total: 0 });
        const triggerReload = ref<(() => void) | null>(null);

        function registerReload(fn: () => void) {
            triggerReload.value = fn;
        }

        const isLoading = ref(false);
        const showForm = ref(false);
        const editingLookup = ref<LookupModel | null>(null);
        const isExporting = ref(false);
        const isImporting = ref(false);
        const reloadTrigger = ref(0);
        const showImportMenu = ref(false);
        const lookupTableReload = ref<(() => void) | null>(null);

        function setLookupTable(instance: any) {
            lookupTableReload.value = () => instance?.reload?.();
        }

        watch(reloadTrigger, () => lookupTableReload.value?.());

        // Constant Options
        const searchColumns = [
            { id: 'all', text: 'Semua Kolom' },
            { id: 'name', text: 'Nama' },
            { id: 'program_code', text: 'Kode Program' },
        ];

        const perPageOptions = [
            { id: 5, text: '5' },
            { id: 10, text: '10' },
            { id: 25, text: '25' },
            { id: 50, text: '50' },
            { id: 100, text: '100' },
        ];

        const search = ref('');
        const searchColumn = ref('all');
        const sortBy = ref<'name' | 'program_code'>('name');
        const sortDirection = ref<'asc' | 'desc'>('asc');
        const perPage = ref(5);
        const currentPage = ref(1);

        const page = usePage<LookupPageProps>();
        const canManageLookups = computed(() => {
            const roles = Array.isArray(page.props.auth?.user?.roles)
                ? page.props.auth.user.roles
                : [];
            const superRoles = ['owner', 'superadmin', 'admin'];
            if (roles.some((r: string) => superRoles.includes(r))) {
                return true;
            }

            const permissionNames = Array.isArray(page.props.auth?.user?.permissions)
                ? page.props.auth.user.permissions
                : [];

            return permissionNames.includes('master.lookup.manage');
        });

        const flash = computed(() => ({
            success: page.props.flash?.success ?? null,
            error: page.props.flash?.error ?? page.props.errors?.file ?? null,
        }));

        const form = useForm<LookupFormData>({
            uuid: undefined,
            name: '',
            description: '',
            user_can_edit_flag: 'Y',
            program_code: '',
        });

        async function fetchLookups(filters: LookupFilters = {}) {
            const finalFilters = {
                search: search.value,
                search_column: searchColumn.value,
                sort_by: sortBy.value,
                sort_direction: sortDirection.value,
                per_page: perPage.value,
                page: currentPage.value,
                ...filters,
            };

            if (typeof finalFilters.page === 'number') {
                currentPage.value = finalFilters.page;
            }

            isLoading.value = true;
            const result = await fetchLookupsUseCase.execute(finalFilters);

            result.fold(
                (failure: any) => {
                    isLoading.value = false;
                    alert(failure.message);
                },
                (data: any) => {
                    lookups.value = data.data || [];
                    meta.value = {
                        ...(data.meta || data),
                        links: data.links || data.meta?.links || [],
                    };
                    summary.value = {
                        total: data.total || data.meta?.total || 0,
                    };
                    isLoading.value = false;
                },
            );
        }

        const updateFilters = debounce(() => {
            currentPage.value = 1;
            fetchLookups({ page: 1 });
        }, 300);

        watch([search, searchColumn, perPage], () => {
            updateFilters();
        });

        async function deleteLookup(lookup: LookupModel) {
            if (!canManageLookups.value) {
                return;
            }

            const confirmResult = await Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `Lookup "${lookup.name}" akan dihapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--danger)',
                cancelButtonColor: 'var(--muted)',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            });

            if (!confirmResult.isConfirmed) {
                return;
            }

            const result = await deleteLookupUseCase.execute(lookup.uuid);
            result.fold(
                (failure: any) => {
                    Swal.fire('Gagal!', failure.message, 'error');
                },
                () => {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: 'Lookup berhasil dihapus.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                    });
                    reloadTrigger.value++;
                    fetchLookups();
                    triggerReload.value?.();
                },
            );
        }

        async function exportLookups() {
            isExporting.value = true;
            const result = await exportLookupsUseCase.execute({
                search: search.value,
                search_column: searchColumn.value,
                sort_by: sortBy.value,
                sort_direction: sortDirection.value,
            });

            result.fold(
                (failure: any) => {
                    Swal.fire('Gagal!', failure.message, 'error');
                },
                () => undefined,
            );

            isExporting.value = false;
        }

        async function importLookups(file: File) {
            if (!canManageLookups.value) {
                return;
            }

            isImporting.value = true;
            const result = await importLookupsUseCase.execute(file);

            result.fold(
                (failure: any) => {
                    if (failure instanceof ValidationFailure) {
                        alert(failure.message + '\n' + Object.values(failure.errors).flat().join('\n'));
                    } else {
                        alert(failure.message);
                    }
                },
                () => {
                    fetchLookups();
                },
            );

            isImporting.value = false;
        }

        async function downloadImportTemplate() {
            if (!canManageLookups.value) {
                return;
            }

            await downloadLookupImportTemplateUseCase.execute();
        }


        function handleImport(event: Event) {
            const target = event.target as HTMLInputElement;
            if (target.files?.length) {
                importLookups(target.files[0]).then(() => {
                    target.value = '';
                });
            }
        }
        function toggleImportMenu(event?: MouseEvent) {
            event?.stopPropagation();
            showImportMenu.value = !showImportMenu.value;
        }

        function closeImportMenu() {
            showImportMenu.value = false;
        }

        function openImportData() {
            showImportMenu.value = false;
            openImportPicker();
        }

        function closeImportMenuAndDownload() {
            showImportMenu.value = false;
            downloadImportTemplate();
        }
        function openCreate() {
            if (!canManageLookups.value) {
                return;
            }

            editingLookup.value = null;
            form.defaults({
                uuid: undefined,
                name: '',
                program_code: '',
            });
            form.reset();
            form.clearErrors();
            showForm.value = true;
        }

        function openEdit(lookup: LookupModel) {
            if (!canManageLookups.value) {
                return;
            }

            editingLookup.value = { ...lookup };
            form.defaults({
                uuid: lookup.uuid,
                name: lookup.name,
                program_code: lookup.program_code || '',
            });
            form.reset();
            form.clearErrors();
            showForm.value = true;
        }

        function closeForm() {
            showForm.value = false;
            editingLookup.value = null;
            form.reset();
            form.clearErrors();
        }

        async function submitForm() {
            if (!canManageLookups.value) {
                return;
            }

            form.clearErrors();
            (form as typeof form & { processing: boolean }).processing = true;

            const result = await saveLookupUseCase.execute({
                ...form.data(),
                uuid: editingLookup.value?.uuid,
            });

            result.fold(
                (failure: any) => {
                    if (failure instanceof ValidationFailure) {
                        const formattedErrors: Record<string, string> = {};
                        for (const [key, messages] of Object.entries(failure.errors)) {
                            formattedErrors[key] = Array.isArray(messages) ? messages[0] : messages;
                        }
                        form.setError(formattedErrors as any);
                    } else {
                        alert(failure.message);
                    }
                },
                () => {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: editingLookup.value ? 'Lookup diperbarui.' : 'Lookup baru dibuat.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                    });
                    reloadTrigger.value++;
                    fetchLookups();
                    triggerReload.value?.();
                    closeForm();
                    triggerReload.value?.();
                },
            );

            (form as typeof form & { processing: boolean }).processing = false;
        }

        function openImportPicker() {
            if (!canManageLookups.value) {
                return;
            }

            document.getElementById('lookupImportInput')?.click();
        }

        function toggleSort(column: 'name' | 'program_code') {
            if (sortBy.value === column) {
                sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy.value = column;
                sortDirection.value = 'asc';
            }

            currentPage.value = 1;
            fetchLookups({ page: 1 });
        }

        function changePageFromUrl(url: string) {
            const parsedUrl = new URL(url, window.location.origin);
            const targetPage = Number(parsedUrl.searchParams.get('page') || '1');
            currentPage.value = targetPage;
            fetchLookups({ page: targetPage });
        }

        function initFilters(filters: LookupFilters): void {
            search.value = filters.search || '';
            searchColumn.value = filters.search_column || 'all';
            sortBy.value = filters.sort_by || 'name';
            sortDirection.value = filters.sort_direction || 'asc';
            perPage.value = filters.per_page || 5;
            currentPage.value = filters.page || 1;
            fetchLookups();
        }

        function initFiltersOnly(filters: LookupFilters): void {
            search.value = filters.search || '';
            searchColumn.value = filters.search_column || 'all';
            sortBy.value = filters.sort_by || 'name';
            sortDirection.value = filters.sort_direction || 'asc';
            perPage.value = filters.per_page || 5;
            currentPage.value = filters.page || 1;
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

        const formMode = computed(() => editingLookup.value ? FormMode.EDIT : FormMode.CREATE);

        let unbindImportMenuDismiss: (() => void) | null = null;
        const initImportMenuDismiss = () => {
            unbindImportMenuDismiss = bindImportMenuDismiss(closeImportMenu);
        };
        const disposeImportMenuDismiss = () => {
            unbindImportMenuDismiss?.();
            unbindImportMenuDismiss = null;
        };

        return {
            lookups,
            triggerReload,
            registerReload,
            setLookupTable,
            filterActive, activeQueryParams, setFilterActive,
            meta,
            summary,
            isLoading,
            showForm,
            editingLookup,
            formMode,
            isExporting,
            isImporting,
            canManageLookups,
            flash,
            searchColumns,
            perPageOptions,
            search,
            searchColumn,
            sortBy,
            sortDirection,
            perPage,
            currentPage,
            form,
            fetchLookups,
            deleteLookup,
            exportLookups,
            importLookups,
            downloadImportTemplate,
            openCreate,
            openEdit,
            closeForm,
            submitForm,
            openImportPicker,
            toggleSort,
            changePageFromUrl,
            initFilters,
            initFiltersOnly,
            showImportMenu,
            toggleImportMenu,
            closeImportMenu,
            openImportData,
            closeImportMenuAndDownload,
            handleImport,
            initImportMenuDismiss,
            disposeImportMenuDismiss,
            reloadTrigger,
        };
    });
}

export const useLookupVM = createLookupVMStore();
