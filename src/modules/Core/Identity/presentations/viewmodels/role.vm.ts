import type { RolePageProps } from './role.state';
import { defineStore } from 'pinia';
import { ref, watch, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
// Local debounce helper to avoid ESM lodash issues
function debounce(fn: Function, delay: number) {
    let timeoutId: any;
    return function (...args: any[]) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn.apply(null, args), delay);
    };
}
import * as roleDI from '@modules/Core/Identity/domain/di/role.di';
import { createRoleDependencies, type RoleVMDependencies } from '@modules/Core/Identity/domain/di/role.di';
import type { RoleModel, RoleFilters, RoleFormData, PermissionModel } from '@modules/Core/Identity/domain/models/role.model';
import type { PaginationMeta } from '@modules/Core/shared/types/pagination';
import { ValidationFailure } from '@modules/Core/shared/core/failures';
import Swal from 'sweetalert2';
import { bindImportMenuDismiss } from '@modules/Core/shared/core/helpers/import_menu';
import { FormMode } from '@modules/Core/shared/core/domain/models/form_mode.model';

export function createRoleVMStore(
    storeId = 'role-vm',
    dependencies: RoleVMDependencies = roleDI,
) {
    return defineStore(storeId, () => {
        const {
            fetchRolesUseCase,
            saveRoleUseCase,
            deleteRoleUseCase,
            exportRolesUseCase,
            importRolesUseCase,
            downloadRoleImportTemplateUseCase,
        } = dependencies;

        // UI State
        const roles = ref<RoleModel[]>([]);
        const meta = ref<PaginationMeta | null>(null);
        const availablePermissions = ref<PermissionModel[]>([]);
        
        const showForm = ref(false);
        const editingRole = ref<RoleModel | null>(null);
        const isExporting = ref(false);
        const isImporting = ref(false);
        const showImportMenu = ref(false);
        const isLoading = ref(false);
        const permissionSearch = ref('');

        // Constant Options
        const searchColumns = [
            { id: 'all', text: 'Semua Kolom' },
            { id: 'name', text: 'Nama' },
            { id: 'label', text: 'Label' },
        ];

        const perPageOptions = [
            { id: 5, text: '5' },
            { id: 10, text: '10' },
            { id: 25, text: '25' },
            { id: 50, text: '50' },
            { id: 100, text: '100' },
        ];

        // Filter State
        const search = ref('');
        const searchColumn = ref<'all' | 'name' | 'label'>('all');
        const sortBy = ref<'name' | 'label'>('name');
        const sortDirection = ref<'asc' | 'desc'>('asc');
        const perPage = ref(5);
        const currentPage = ref(1);
        const page = usePage<RolePageProps>();
        
        const filteredPermissions = computed(() => {
            const search = permissionSearch.value.toLowerCase();
            const flat = availablePermissions.value || [];
            
            // 1. Filter
            const filtered = search 
                ? flat.filter(p => p.name.toLowerCase().includes(search) || p.label.toLowerCase().includes(search))
                : flat;

            // 2. Group
            const groups: Record<string, PermissionModel[]> = {};
            filtered.forEach(p => {
                const parts = p.name.split('.');
                const group = parts.length > 1 ? parts[0] : 'other';
                if (!groups[group]) groups[group] = [];
                groups[group].push(p);
            });

            return groups;
        });

        const canManageRoles = computed(() => {
            const permissions = Array.isArray(page.props.auth?.user?.permissions)
                ? page.props.auth.user.permissions
                : [];
            
            const rolesData = page.props.auth?.user?.roles;
            const roles = Array.isArray(rolesData)
                ? rolesData
                : (typeof rolesData === 'object' && rolesData !== null ? Object.values(rolesData) : []);

            return roles.includes('owner') || permissions.includes('settings.role.manage');
        });

        const flash = computed(() => ({
            success: page.props.flash?.success ?? null,
            error: page.props.flash?.error ?? page.props.errors?.file ?? null,
        }));
        const formMode = computed(() => editingRole.value ? FormMode.EDIT : FormMode.CREATE);

        // Form State
        const form = useForm<RoleFormData>({
            uuid: undefined,
            name: '',
            label: '',
            permissions: [],
        });

        async function fetchRoles(filters: RoleFilters = {}) {
            const finalFilters = {
                search: search.value,
                search_column: searchColumn.value,
                sort_by: sortBy.value,
                sort_direction: sortDirection.value,
                per_page: perPage.value,
                page: currentPage.value,
                ...filters
            };

            isLoading.value = true;
            const result = await fetchRolesUseCase.execute(finalFilters);
            result.fold(
                (failure: any) => { 
                    isLoading.value = false;
                    console.error('Fetch Roles Failed:', failure.message);
                    Swal.fire('Gagal!', failure.message, 'error'); 
                },
                (data: any) => {
                    // Robust mapping (Handle both nested 'roles' key and top-level response)
                    const rolesPayload = data.roles || data;
                    
                    roles.value = Array.isArray(rolesPayload.data) 
                        ? rolesPayload.data 
                        : (Array.isArray(rolesPayload) ? rolesPayload : []);
                    
                    meta.value = rolesPayload;

                    if (data.permissions) {
                        availablePermissions.value = data.permissions;
                    }
                    isLoading.value = false;
                }
            );
        }

        const updateFilters = debounce(() => {
            currentPage.value = 1;
            fetchRoles();
        }, 300);

        watch([search, searchColumn, perPage], () => {
            updateFilters();
        });

        watch(() => page.props.auth?.api_token, (token) => {
            if (token) void fetchRoles();
        }, { immediate: true });

        async function deleteRole(role: RoleModel) {
            if (!canManageRoles.value) return;

            const confirmResult = await Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `Role "${role.label}" akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--danger)',
                cancelButtonColor: 'var(--muted)',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            });

            if (confirmResult.isConfirmed) {
                const result = await deleteRoleUseCase.execute(role.uuid);
                result.fold(
                    (failure: any) => { Swal.fire('Gagal!', failure.message, 'error'); },
                    () => {
                        Swal.fire({
                            title: 'Terhapus!',
                            text: 'Role berhasil dihapus.',
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false,
                            timerProgressBar: true,
                        });
                        fetchRoles();
                    }
                );
            }
        }

        async function exportRoles() {
            isExporting.value = true;
            await exportRolesUseCase.execute({
                search: search.value,
                sort_by: sortBy.value,
                sort_direction: sortDirection.value,
            });
            isExporting.value = false;
        }

        async function importRoles(file: File) {
            if (!canManageRoles.value) return;
            isImporting.value = true;
            const result = await importRolesUseCase.execute(file);
            result.fold(
                (failure: any) => {
                    if (failure instanceof ValidationFailure) {
                        alert(failure.message + '\n' + Object.values(failure.errors).flat().join('\n'));
                    } else {
                        alert(failure.message);
                    }
                },
                () => { fetchRoles(); }
            );
            isImporting.value = false;
        }

        async function downloadImportTemplate() {
            if (!canManageRoles.value) return;
            showImportMenu.value = false;
            await downloadRoleImportTemplateUseCase.execute();
        }

        function openImportPicker() {
            if (!canManageRoles.value) return;
            document.getElementById('importInput')?.click();
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
            await importRoles(target.files[0]);
            target.value = '';
        }

        function openCreate() {
            if (!canManageRoles.value) return;
            editingRole.value = null;
            form.defaults({
                uuid: undefined,
                name: '',
                label: '',
                permissions: [],
            });
            form.reset();
            form.clearErrors();
            showForm.value = true;
        }

        function openEdit(role: RoleModel & { permissions?: PermissionModel[] }) {
            if (!canManageRoles.value) return;
            editingRole.value = role;
            form.defaults({
                uuid: role.uuid,
                name: role.name,
                label: role.label,
                permissions: role.permissions?.map((p: PermissionModel) => p.uuid) || [],
            });
            form.reset();
            form.clearErrors();
            showForm.value = true;
        }

        function closeForm() {
            showForm.value = false;
            editingRole.value = null;
            form.reset();
            form.clearErrors();
        }

        async function submitForm() {
            if (!canManageRoles.value) return;
            form.clearErrors();
            
            const result = await saveRoleUseCase.execute({
                ...form.data(),
                uuid: editingRole.value?.uuid,
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
                        text: editingRole.value ? 'Role diperbarui.' : 'Role baru dibuat.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                    });
                    fetchRoles(); 
                    closeForm();
                }
            );
        }

        function toggleSort(column: 'name' | 'label') {
            if (sortBy.value === column) {
                sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy.value = column;
                sortDirection.value = 'asc';
            }
            currentPage.value = 1;
            fetchRoles();
        }

        function fetchRolesByUrl(url: string) {
            try {
                const urlObj = new URL(url, window.location.origin);
                const pageParam = urlObj.searchParams.get('page');
                if (pageParam) {
                    const pageNum = parseInt(pageParam);
                    currentPage.value = pageNum;
                    fetchRoles({ page: pageNum });
                }
            } catch (e) {
                console.error('Failed to parse pagination URL:', e);
            }
        }

        const initFilters = (filters: RoleFilters): void => {
            search.value = filters.search || '';
            searchColumn.value = filters.search_column || 'all';
            sortBy.value = filters.sort_by || 'name';
            sortDirection.value = filters.sort_direction || 'asc';
            perPage.value = filters.per_page || 5;
            currentPage.value = filters.page || 1;
            fetchRoles();
        };

        let unbindImportMenuDismiss: (() => void) | null = null;
        const initImportMenuDismiss = () => {
            unbindImportMenuDismiss = bindImportMenuDismiss(closeImportMenu);
        };
        const disposeImportMenuDismiss = () => {
            unbindImportMenuDismiss?.();
            unbindImportMenuDismiss = null;
        };

        function togglePermission(uuid: string) {
            const index = form.permissions.indexOf(uuid);
            if (index === -1) {
                form.permissions.push(uuid);
            } else {
                form.permissions.splice(index, 1);
            }
        }

        function focusPermissionSearch(input: HTMLInputElement | null) {
            input?.focus();
            input?.select();
        }

        function closeFormFromEscape(input: HTMLInputElement | null) {
            if (input && document.activeElement === input) {
                permissionSearch.value = '';
                input.blur();
                return;
            }

            closeForm();
        }

        function getAvatarInitial(label: string) {
            return label ? label.charAt(0).toUpperCase() : '?';
        }

        function getAvatarColor(label: string) {
            const colors = ['#1e2a3a', '#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#6366f1'];
            let hash = 0;
            for (let index = 0; index < label.length; index += 1) {
                hash = label.charCodeAt(index) + ((hash << 5) - hash);
            }
            return colors[Math.abs(hash) % colors.length];
        }

        return {
            roles,
            meta,
            availablePermissions,
            showForm,
            editingRole,
            isExporting,
            isImporting,
            showImportMenu,
            isLoading,
            canManageRoles,
            flash,
            formMode,
            searchColumns,
            perPageOptions,
            permissionSearch,
            filteredPermissions,
            search,
            searchColumn,
            sortBy,
            sortDirection,
            perPage,
            currentPage,
            form,
            fetchRoles,
            fetchRolesByUrl,
            deleteRole,
            exportRoles,
            importRoles,
            downloadImportTemplate,
            openImportPicker,
            toggleImportMenu,
            closeImportMenu,
            openImportData,
            handleImport,
            initImportMenuDismiss,
            disposeImportMenuDismiss,
            openCreate,
            openEdit,
            closeForm,
            submitForm,
            togglePermission,
            focusPermissionSearch,
            closeFormFromEscape,
            getAvatarInitial,
            getAvatarColor,
            toggleSort,
            initFilters,
        };
    });
}

export const useRoleVM = createRoleVMStore();
