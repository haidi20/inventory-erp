import type { UserPageProps } from './user.state';
import { defineStore } from 'pinia';
import { ref, watch, computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
// Local debounce helper to avoid ESM lodash issues
function debounce(fn: Function, delay: number) {
    let timeoutId: any;
    return function (...args: any[]) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn.apply(null, args), delay);
    };
}
import * as userDI from '@modules/Core/Identity/domain/di/user.di';
import { createUserDependencies, type UserVMDependencies } from '@modules/Core/Identity/domain/di/user.di';
import type { UserModel, UserFilters, UserFormData } from '@modules/Core/Identity/domain/models/user.model';
import { ValidationFailure } from '@modules/Core/shared/core/failures';
import Swal from 'sweetalert2';
import { bindImportMenuDismiss } from '@modules/Core/shared/core/helpers/import_menu';
import { FormMode } from '@modules/Core/shared/core/domain/models/form_mode.model';

export function createUserVMStore(
    storeId = 'user-vm',
    dependencies: UserVMDependencies = userDI,
) {
    return defineStore(storeId, () => {
        const mapApiPaginationLinksToWeb = (links: Array<{ url: string | null; label: string; active: boolean }>) =>
            links.map((link) => ({
                ...link,
                url: link.url ? link.url.replace('/api/v1/settings/users', '/settings/users') : null,
            }));

        const {
            fetchUsersUseCase,
            saveUserUseCase,
            deleteUserUseCase,
            exportUsersUseCase,
            importUsersUseCase,
            downloadUserImportTemplateUseCase,
        } = dependencies;

        // UI State
        const users = ref<any[]>([]);
        const meta = ref<any>({});
        const summary = ref<any>({ total: 0, dokter: 0, other: 0 });
        const roles = ref<any[]>([]);
        const isLoading = ref(false);
        
        const showForm = ref(false);
        const editingUser = ref<UserModel | null>(null);
        const isExporting = ref(false);
        const isImporting = ref(false);
        const showImportMenu = ref(false);

        // Constant Options
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

        // Filter State
        const search = ref('');
        const searchColumn = ref('all');
        const roleFilter = ref('');
        const sortBy = ref<'name' | 'email'>('name');
        const sortDirection = ref<'asc' | 'desc'>('asc');
        const perPage = ref(5);
        const currentPage = ref(1);
        const page = usePage<UserPageProps>();
        
        const canManageUsers = computed(() => {
            const permissionNames = Array.isArray(page.props.auth?.user?.permissions)
                ? page.props.auth.user.permissions
                : [];
            return permissionNames.includes('settings.user.manage');
        });

        const roleSearch = ref('');
        const filteredRoles = computed(() => {
            const query = roleSearch.value.toLowerCase().trim();
            if (!query) return roles.value;

            return roles.value.filter((role) =>
                role.label?.toLowerCase().includes(query) || role.name?.toLowerCase().includes(query)
            );
        });
        const formMode = computed(() => editingUser.value ? FormMode.EDIT : FormMode.CREATE);


        const flash = computed(() => ({
            success: page.props.flash?.success ?? null,
            error: page.props.flash?.error ?? page.props.errors?.file ?? null,
        }));

        // Form State
        const form = useForm<UserFormData>({
            uuid: undefined,
            name: '',
            email: '',
            password: '',
            role_uuids: [],
            employee_uuid: undefined,
        });

        async function fetchUsers(filters: UserFilters = {}) {
            const finalFilters = {
                search: search.value,
                search_column: searchColumn.value,
                role: roleFilter.value,
                sort_by: sortBy.value,
                sort_direction: sortDirection.value,
                per_page: perPage.value,
                page: currentPage.value,
                ...filters
            };

            isLoading.value = true;
            const result = await fetchUsersUseCase.execute(finalFilters);
            result.fold(
                (failure: any) => {
                    isLoading.value = false;
                    alert(failure.message);
                },
                (data: any) => {
                    users.value = data.data;
                    meta.value = {
                        ...(data.meta || data),
                        links: mapApiPaginationLinksToWeb(data.links || data.meta?.links || []),
                    };
                    summary.value = data.summary || summary.value;
                    roles.value = data.roles || roles.value;
                    isLoading.value = false;
                }
            );
        }

        const updateFilters = debounce(() => {
            currentPage.value = 1;
            fetchUsers();
        }, 300);

        // Watchers for filters
        watch([search, searchColumn, roleFilter, perPage], () => {
            updateFilters();
        });

        watch(() => page.props.auth?.api_token, (token) => {
            if (token) void fetchUsers();
        }, { immediate: true });

        async function deleteUser(user: UserModel) {
            console.log(`DEBUG: Menjalankan deleteUser untuk ${user.email}, canManageUsers: ${canManageUsers.value}`);
            if (!canManageUsers.value) {
                return;
            }

            const confirmResult = await Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `User "${user.name}" akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--danger)',
                cancelButtonColor: 'var(--muted)',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            });

            if (confirmResult.isConfirmed) {
                const result = await deleteUserUseCase.execute(user.uuid);
                result.fold(
                    (failure: any) => { Swal.fire('Gagal!', failure.message, 'error'); },
                    () => {
                        Swal.fire({
                            title: 'Terhapus!',
                            text: 'User berhasil dihapus.',
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            showConfirmButton: false,
                            timerProgressBar: true,
                        });
                        fetchUsers(); // Refresh data via API
                    }
                );
            }
        }

        async function exportUsers() {
            isExporting.value = true;
            const result = await exportUsersUseCase.execute({
                search: search.value,
                search_column: searchColumn.value,
                role: roleFilter.value,
                sort_by: sortBy.value,
                sort_direction: sortDirection.value,
            });
            
            result.fold(
                (failure: any) => { Swal.fire('Gagal!', failure.message, 'error'); },
                () => {}
            );
            
            isExporting.value = false;
        }

        async function importUsers(file: File) {
            if (!canManageUsers.value) {
                return;
            }

            isImporting.value = true;
            const result = await importUsersUseCase.execute(file);
            
            result.fold(
                (failure: any) => {
                    if (failure instanceof ValidationFailure) {
                        alert(failure.message + '\n' + Object.values(failure.errors).flat().join('\n'));
                    } else {
                        alert(failure.message);
                    }
                },
                () => {
                    fetchUsers();
                }
            );
            
            isImporting.value = false;
        }

        function openCreate() {
            if (!canManageUsers.value) {
                return;
            }

            editingUser.value = null;
            form.defaults({
                uuid: undefined,
                name: '',
                email: '',
                password: '',
                role_uuids: [],
                employee_uuid: undefined,
            });
            form.reset();
            form.clearErrors();
            showForm.value = true;
        }

        function openEdit(user: UserModel) {
            if (!canManageUsers.value) {
                return;
            }

            editingUser.value = { ...user };
            form.defaults({
                uuid: user.uuid,
                name: user.name,
                email: user.email,
                password: '',
                role_uuids: user.role_uuids?.length ? user.role_uuids : (user.role_uuid ? [user.role_uuid] : []),
                employee_uuid: user.employee_uuid || undefined,
            });
            form.reset();
            form.clearErrors();
            showForm.value = true;
        }

        function closeForm() {
            showForm.value = false;
            editingUser.value = null;
            form.defaults({ uuid: undefined, name: '', email: '', password: '', role_uuids: [], employee_uuid: undefined });
            form.reset();
            form.clearErrors();
        }

        async function submitForm() {
            if (!canManageUsers.value) {
                return;
            }

            form.clearErrors();
            (form as typeof form & { processing: boolean }).processing = true;

            const result = await saveUserUseCase.execute({
                ...form.data(),
                uuid: editingUser.value?.uuid,
            });

            result.fold(
                (failure: any) => {
                    if (failure instanceof ValidationFailure) {
                        console.error('API 422 VALIDATION ERRORS:', JSON.stringify(failure.errors));
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
                        text: editingUser.value ? 'User diperbarui.' : 'User baru dibuat.',
                        icon: 'success',
                        toast: true,
                        position: 'top-end',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                    });
                    fetchUsers(); 
                    closeForm();
                }
            );

            (form as typeof form & { processing: boolean }).processing = false;
        }

        function openImportPicker() {
            if (!canManageUsers.value) {
                return;
            }

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

        function downloadImportTemplate() {
            showImportMenu.value = false;
            void downloadUserImportTemplateUseCase.execute();
        }

        async function handleImport(event: Event) {
            const target = event.target as HTMLInputElement;
            if (!target.files?.length) return;
            await importUsers(target.files[0]);
            target.value = '';
        }

        let unbindImportMenuDismiss: (() => void) | null = null;
        const initImportMenuDismiss = () => {
            unbindImportMenuDismiss = bindImportMenuDismiss(closeImportMenu);
        };
        const disposeImportMenuDismiss = () => {
            unbindImportMenuDismiss?.();
            unbindImportMenuDismiss = null;
        };

        function formatRoleLabels(user: UserModel) {
            return (user.roles || []).map((role) => role.label).join(', ');
        }

        function toggleSort(column: 'name' | 'email') {
            if (sortBy.value === column) {
                sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy.value = column;
                sortDirection.value = 'asc';
            }

            currentPage.value = 1;
            fetchUsers();
        }

        // Initialize filters and fetch data
        const initFilters = (filters: UserFilters): void => {
            search.value = filters.search || '';
            searchColumn.value = filters.search_column || 'all';
            roleFilter.value = filters.role || '';
            sortBy.value = filters.sort_by || 'name';
            sortDirection.value = filters.sort_direction || 'asc';
            perPage.value = filters.per_page || 5;
            currentPage.value = filters.page || 1;
            fetchUsers(); // Initial fetch
        };

        return {
            users,
            meta,
            summary,
            roles,
            isLoading,
            showForm,
            editingUser,
            isExporting,
            isImporting,
            showImportMenu,
            canManageUsers,
            roleSearch,
            filteredRoles,
            formMode,
            flash,
            searchColumns,
            perPageOptions,
            search,
            searchColumn,
            roleFilter,
            sortBy,
            sortDirection,
            perPage,
            currentPage,
            form,
            fetchUsers,
            deleteUser,
            exportUsers,
            importUsers,
            openCreate,
            openEdit,
            closeForm,
            submitForm,
            openImportPicker,
            toggleImportMenu,
            closeImportMenu,
            openImportData,
            downloadImportTemplate,
            handleImport,
            initImportMenuDismiss,
            disposeImportMenuDismiss,
            toggleSort,
            initFilters,
            formatRoleLabels,
        };
    });
}

export const useUserVM = createUserVMStore();
