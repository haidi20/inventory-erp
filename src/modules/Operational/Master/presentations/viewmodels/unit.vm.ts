import type { UnitPageProps } from './unit.state';
import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import * as unitDI from '@modules/Operational/Master/domain/di/unit.di';
import type { UnitVMDependencies } from '@modules/Operational/Master/domain/di/unit.di';
import type { UnitFilters, UnitFormData, UnitModel } from '@modules/Operational/Master/domain/models/unit.model';
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

export function createUnitVMStore(
    storeId = 'unit-vm',
    dependencies: UnitVMDependencies = unitDI,
) {
    return defineStore(storeId, () => {
        const {
            fetchUnitsUseCase,
            saveUnitUseCase,
            deleteUnitUseCase,
            exportUnitsUseCase,
            importUnitsUseCase,
            downloadUnitImportTemplateUseCase,
        } = dependencies;

        const units = ref<UnitModel[]>([]);
        const meta = ref<any>({});
        const summary = ref({ total: 0 });
        const isLoading = ref(false);
        const showForm = ref(false);
        const editingUnit = ref<UnitModel | null>(null);
        const isExporting = ref(false);
        const isImporting = ref(false);
        const showImportMenu = ref(false);

        // Constant Options
        const searchColumns = [
            { id: 'all', text: 'Semua Kolom' },
            { id: 'code', text: 'Kode' },
            { id: 'name', text: 'Nama' },
        ];

        const triggerReload = ref<(() => void) | null>(null);

        function registerReload(fn: () => void) {
            triggerReload.value = fn;
        }

        const perPageOptions = [
            { id: 5, text: '5' },
            { id: 10, text: '10' },
            { id: 25, text: '25' },
            { id: 50, text: '50' },
            { id: 100, text: '100' },
        ];

        const search = ref('');
        const searchColumn = ref('all');
        const sortBy = ref<'code' | 'name'>('code');
        const sortDirection = ref<'asc' | 'desc'>('asc');
        const perPage = ref(5);
        const currentPage = ref(1);

        const page = usePage<UnitPageProps>();
        const canManageUnits = computed(() => {
            const roles = Array.isArray((page.props as any).auth?.user?.roles)
                ? (page.props as any).auth.user.roles as string[]
                : [];
            if (['owner', 'superadmin', 'admin'].some((r) => roles.includes(r))) return true;
            const permissionNames = Array.isArray(page.props.auth?.user?.permissions)
                ? page.props.auth.user.permissions
                : [];
            return permissionNames.includes('master.manage');
        });

        const flash = computed(() => ({
            success: page.props.flash?.success ?? null,
            error: page.props.flash?.error ?? page.props.errors?.file ?? null,
        }));

        const form = useForm<UnitFormData>({
            uuid: undefined,
            code: '',
            name: '',
            symbol: '',
            description: '',
            is_active: true,
        });

        async function fetchUnits(filters: UnitFilters = {}) {
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
            const result = await fetchUnitsUseCase.execute(finalFilters);

            result.fold(
                (failure: any) => {
                    isLoading.value = false;
                    alert(failure.message);
                },
                (data: any) => {
                    units.value = data.data || [];
                    meta.value = {
                        ...(data.meta || data),
                        links: data.links || data.meta?.links || [],
                    };
                    summary.value = data.summary || { total: data.total || data.meta?.total || 0 };
                    isLoading.value = false;
                },
            );
        }

        const updateFilters = debounce(() => {
            currentPage.value = 1;
            fetchUnits({ page: 1 });
        }, 300);

        watch([search, searchColumn, perPage], () => {
            updateFilters();
        });

        async function deleteUnit(unit: UnitModel) {
            if (!canManageUnits.value) {
                return;
            }

            const confirmResult = await Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `Satuan "${unit.name}" akan dihapus.`,
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

            const result = await deleteUnitUseCase.execute(unit.uuid);
            result.fold(
                (failure: any) => {
                    Swal.fire('Gagal!', failure.message, 'error');
                },
                () => {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: 'Satuan berhasil dihapus.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                    });
                    fetchUnits();
                },
            );
        }

        async function exportUnits() {
            isExporting.value = true;
            const result = await exportUnitsUseCase.execute({
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

        async function importUnits(file: File) {
            if (!canManageUnits.value) {
                return;
            }

            isImporting.value = true;
            const result = await importUnitsUseCase.execute(file);

            result.fold(
                (failure: any) => {
                    if (failure instanceof ValidationFailure) {
                        alert(failure.message + '\n' + Object.values(failure.errors).flat().join('\n'));
                    } else {
                        alert(failure.message);
                    }
                },
                () => {
                    fetchUnits();
                },
            );

            isImporting.value = false;
        }

        async function downloadImportTemplate() {
            if (!canManageUnits.value) {
                return;
            }

            showImportMenu.value = false;
            await downloadUnitImportTemplateUseCase.execute();
        }

        function openCreate() {
            if (!canManageUnits.value) {
                return;
            }

            editingUnit.value = null;
            form.defaults({
                uuid: undefined,
                code: '',
                name: '',
                symbol: '',
                description: '',
                is_active: true,
            });
            form.reset();
            form.clearErrors();
            showForm.value = true;
        }

        function openEdit(unit: UnitModel) {
            if (!canManageUnits.value) {
                return;
            }

            editingUnit.value = { ...unit };
            form.defaults({
                uuid: unit.uuid,
                code: unit.code,
                name: unit.name,
                symbol: unit.symbol || '',
                description: unit.description || '',
                is_active: unit.is_active,
            });
            form.reset();
            form.clearErrors();
            showForm.value = true;
        }

        function closeForm() {
            showForm.value = false;
            editingUnit.value = null;
            form.reset();
            form.clearErrors();
        }

        async function submitForm() {
            if (!canManageUnits.value) {
                return;
            }

            form.clearErrors();
            (form as typeof form & { processing: boolean }).processing = true;

            const result = await saveUnitUseCase.execute({
                ...form.data(),
                uuid: editingUnit.value?.uuid,
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
                        text: editingUnit.value ? 'Satuan diperbarui.' : 'Satuan baru dibuat.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                    });
                    fetchUnits();
                    triggerReload.value?.();
                    closeForm();
                    triggerReload.value?.();
                },
            );

            (form as typeof form & { processing: boolean }).processing = false;
        }

        function openImportPicker() {
            if (!canManageUnits.value) {
                return;
            }

            document.getElementById('unitImportInput')?.click();
        }

        function toggleImportMenu(event?: MouseEvent) {
            event?.stopPropagation();
            showImportMenu.value = !showImportMenu.value;
        }

        function closeImportMenu() {
            showImportMenu.value = false;
        }

        function openImportData() {
            openImportPicker();
        }

        async function handleImport(event: Event) {
            const target = event.target as HTMLInputElement;
            if (!target.files?.length) return;
            await importUnits(target.files[0]);
            target.value = '';
        }

        function toggleSort(column: 'code' | 'name') {
            if (sortBy.value === column) {
                sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy.value = column;
                sortDirection.value = 'asc';
            }

            currentPage.value = 1;
            fetchUnits({ page: 1 });
        }

        function changePageFromUrl(url: string) {
            const parsedUrl = new URL(url, window.location.origin);
            const targetPage = Number(parsedUrl.searchParams.get('page') || '1');
            currentPage.value = targetPage;
            fetchUnits({ page: targetPage });
        }

        function initFilters(filters: UnitFilters): void {
            search.value = filters.search || '';
            searchColumn.value = filters.search_column || 'all';
            sortBy.value = filters.sort_by || 'code';
            sortDirection.value = filters.sort_direction || 'asc';
            perPage.value = filters.per_page || 5;
            currentPage.value = filters.page || 1;
            fetchUnits();
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

        const formMode = computed(() => editingUnit.value ? FormMode.EDIT : FormMode.CREATE);

        let unbindImportMenuDismiss: (() => void) | null = null;
        const initImportMenuDismiss = () => {
            unbindImportMenuDismiss = bindImportMenuDismiss(closeImportMenu);
        };
        const disposeImportMenuDismiss = () => {
            unbindImportMenuDismiss?.();
            unbindImportMenuDismiss = null;
        };

        return {
            units,
            triggerReload,
            registerReload,
            filterActive, activeQueryParams, setFilterActive,
            meta,
            summary,
            isLoading,
            showForm,
            editingUnit,
            formMode,
            isExporting,
            isImporting,
            canManageUnits,
            flash,
            showImportMenu,
            searchColumns,
            perPageOptions,
            search,
            searchColumn,
            sortBy,
            sortDirection,
            perPage,
            currentPage,
            form,
            fetchUnits,
            deleteUnit,
            exportUnits,
            importUnits,
            downloadImportTemplate,
            openCreate,
            openEdit,
            closeForm,
            submitForm,
            openImportPicker,
            toggleImportMenu,
            closeImportMenu,
            openImportData,
            handleImport,
            initImportMenuDismiss,
            disposeImportMenuDismiss,
            toggleSort,
            changePageFromUrl,
            initFilters,
        };
    });
}

export const useUnitVM = createUnitVMStore();
