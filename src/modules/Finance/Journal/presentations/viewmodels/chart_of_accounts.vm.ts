import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import { usePage } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import apiService from '@modules/Core/shared/core/api_service';
import { ACCOUNT_TYPE_OPTIONS } from './chart_of_accounts.state';
import type { AccountType, ChartOfAccountsFormData, ChartOfAccountsModel } from './chart_of_accounts.state';

type AccountFormErrors = Partial<Record<'code' | 'name' | 'type', string>>;

export function useChartOfAccountsVM(storeId = 'chart-of-accounts-vm') {
    return defineStore(storeId, () => {
        const page = usePage<{
            auth?: {
                user?: {
                    roles?: string[];
                    permissions?: string[];
                } | null;
            };
        }>();

        const showForm = ref(false);
        const editingAccount = ref<ChartOfAccountsModel | null>(null);
        const processing = ref(false);
        const deletingUuid = ref<string | null>(null);
        const formErrors = ref<AccountFormErrors>({});
        const reloadCallback = ref<(() => void) | null>(null);

        const form = ref<ChartOfAccountsFormData>({
            code: '',
            name: '',
            type: 'asset',
            is_active: true,
        });

        const formMode = computed(() => editingAccount.value ? 'edit' : 'create');
        const roles = computed(() => page.props.auth?.user?.roles ?? []);
        const permissions = computed(() => page.props.auth?.user?.permissions ?? []);
        const canManageAccounts = computed(() =>
            ['owner', 'superadmin', 'admin'].some((role) => roles.value.includes(role))
            || permissions.value.includes('finance.manage'),
        );

        function registerReload(callback: () => void): void {
            reloadCallback.value = callback;
        }

        function clearErrors(): void {
            formErrors.value = {};
        }

        function resetForm(): void {
            form.value = {
                code: '',
                name: '',
                type: 'asset',
                is_active: true,
            };
        }

        function openCreate(): void {
            editingAccount.value = null;
            resetForm();
            clearErrors();
            showForm.value = true;
        }

        function openEdit(account: ChartOfAccountsModel): void {
            editingAccount.value = account;
            form.value = {
                code: account.code,
                name: account.name,
                type: account.type,
                is_active: account.is_active,
            };
            clearErrors();
            showForm.value = true;
        }

        function closeForm(): void {
            showForm.value = false;
            editingAccount.value = null;
            resetForm();
            clearErrors();
        }

        async function submitForm(): Promise<void> {
            if (!canManageAccounts.value || processing.value) return;

            const errors: AccountFormErrors = {};
            if (!form.value.code.trim()) errors.code = 'Kode akun wajib diisi.';
            if (!form.value.name.trim()) errors.name = 'Nama akun wajib diisi.';

            if (Object.keys(errors).length) {
                formErrors.value = errors;
                return;
            }

            processing.value = true;

            try {
                const payload = {
                    code: form.value.code.trim(),
                    name: form.value.name.trim(),
                    type: form.value.type,
                    is_active: form.value.is_active,
                };

                if (editingAccount.value) {
                    await apiService.put(`/accounts/${editingAccount.value.uuid}`, payload);
                } else {
                    await apiService.post('/accounts', payload);
                }

                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: editingAccount.value ? 'Akun berhasil diperbarui.' : 'Akun berhasil dibuat.',
                    timer: 2500,
                    showConfirmButton: false,
                });
                closeForm();
                reloadCallback.value?.();
            } catch (error: any) {
                const responseErrors = error?.response?.data?.errors;

                if (responseErrors) {
                    formErrors.value = responseErrors;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal menyimpan akun',
                        text: error?.response?.data?.message ?? 'Terjadi kesalahan tak terduga.',
                    });
                }
            } finally {
                processing.value = false;
            }
        }

        async function deleteAccount(account: ChartOfAccountsModel): Promise<void> {
            if (!canManageAccounts.value || deletingUuid.value) return;

            const confirmation = await Swal.fire({
                icon: 'warning',
                title: 'Hapus akun?',
                text: `${account.code} - ${account.name} akan dihapus.`,
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
            });

            if (!confirmation.isConfirmed) return;

            deletingUuid.value = account.uuid;

            try {
                await apiService.delete(`/accounts/${account.uuid}`);
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Akun berhasil dihapus.',
                    timer: 2500,
                    showConfirmButton: false,
                });
                reloadCallback.value?.();
            } catch (error: any) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal menghapus akun',
                    text: error?.response?.data?.message ?? 'Terjadi kesalahan tak terduga.',
                });
            } finally {
                deletingUuid.value = null;
            }
        }

        return {
            showForm,
            formMode,
            editingAccount,
            processing,
            deletingUuid,
            formErrors,
            form,
            canManageAccounts,
            accountTypeOptions: ACCOUNT_TYPE_OPTIONS,
            openCreate,
            openEdit,
            closeForm,
            submitForm,
            deleteAccount,
            registerReload,
        };
    })();
}
