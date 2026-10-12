import type { DemoAccount } from './login.state';
import type { RoleOption } from '@modules/Core/Identity/data/repositories/auth.repo_impl';
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { PreCheckRolesUseCase } from '@modules/Core/Identity/domain/usecases/pre_check_roles.usecase';
import { ServerFailure } from '@modules/Core/shared/core/failures';

export const useLoginVM = defineStore('login-vm', () => {
    // Form state
    const email = ref('');
    const password = ref('');
    const remember = ref(false);

    // UI state
    const isLoading = ref(false);
    const clientError = ref<string | null>(null);
    const isInfoModalOpen = ref(false);
    const isRoleModalOpen = ref(false);
    const roles = ref<RoleOption[]>([]);
    const roleConfirmed = ref(false);
    const csrfToken = ref('');
    const selectedRole = ref('');
    const formElement = ref<HTMLFormElement | null>(null);
    const showPassword = ref(false);

    // Demo accounts list (static, no need for reactive ref)
    const demoAccounts: DemoAccount[] = [
        // { role: 'Owner',       email: 'superadmin@klinik.local',   password: 'superadmin' },
        // { role: 'Pendaftaran', email: 'pendaftaran@klinik.local',  password: 'superadmin' },
        // { role: 'Perawat',     email: 'perawat@klinik.local',      password: 'superadmin' },
        // { role: 'Apoteker',    email: 'apoteker@klinik.local',     password: 'superadmin' },
        // { role: 'Kasir',       email: 'kasir@klinik.local',        password: 'superadmin' },
        // { role: 'Lab',         email: 'lab@klinik.local',          password: 'superadmin' },
    ];

    // Use case
    const useCase = new PreCheckRolesUseCase();

    // Demo accounts modal
    const openInfoModal = () => { isInfoModalOpen.value = true; };
    const closeInfoModal = () => { isInfoModalOpen.value = false; };
    const closeRoleModal = () => { isRoleModalOpen.value = false; };

    // Fill form with demo account credentials
    const useDemoAccount = (demoEmail: string, demoPassword: string) => {
        email.value = demoEmail;
        password.value = demoPassword;
        closeInfoModal();
    };

    // Pre-check roles before submitting the form
    const preCheck = async (csrfToken: string): Promise<'submit' | 'roles' | 'error'> => {
        isLoading.value = true;

        const result = await useCase.execute(email.value, password.value, csrfToken);

        isLoading.value = false;

        return result.fold(
            (failure) => {
                // Invalid credentials — let Laravel handle the error via normal form submit
                if (failure instanceof ServerFailure) {
                    return 'submit';
                }
                clientError.value = 'Terjadi kesalahan saat memverifikasi kredensial.';
                return 'error';
            },
            (data) => {
                if (data.success && data.roles && data.roles.length > 1) {
                    roles.value = data.roles;
                    isRoleModalOpen.value = true;
                    return 'roles';
                }
                return 'submit';
            }
        );
    };

    const confirmRole = () => {
        roleConfirmed.value = true;
        isRoleModalOpen.value = false;
    };

    /**
     * Handle form submit — intercept to pre-check roles.
     * Use e.target as the form element so no template ref is needed.
     */
    const handleSubmit = async (e: Event) => {
        if (roleConfirmed.value) return; // already confirmed, let native submit proceed

        e.preventDefault();

        const formEl = e.target as HTMLFormElement;
        const csrfToken = (formEl.querySelector('input[name="_token"]') as HTMLInputElement)?.value ?? '';
        const outcome = await preCheck(csrfToken);

        if (outcome === 'submit') {
            roleConfirmed.value = true;
            formEl.submit();
        }
        // outcome === 'roles' → modal is now open, waiting for user to pick a role
    };

    /**
     * Called when user picks a role from the role-selector modal.
     * Confirms the role and submits the original form.
     */
    const initCsrf = () => {
        csrfToken.value = (window as any).__csrf || '';
    };

    const selectRole = (roleName: string, formEl: HTMLFormElement) => {
        selectedRole.value = roleName;
        confirmRole();
        formEl.submit();
    };

    const setFormElement = (element: unknown) => {
        formElement.value = element instanceof HTMLFormElement ? element : null;
    };

    const togglePasswordVisibility = () => {
        showPassword.value = !showPassword.value;
    };

    const selectRoleFromForm = (roleName: string) => {
        if (formElement.value) {
            selectRole(roleName, formElement.value as HTMLFormElement);
        }
    };

    const getFirstError = (errors?: Record<string, string | string[]>): string => {
        const value = Object.values(errors ?? {})[0];
        if (Array.isArray(value)) {
            return value.join(' ');
        }
        return value ?? '';
    };

    return {
        email,
        password,
        remember,
        isLoading,
        clientError,
        isInfoModalOpen,
        isRoleModalOpen,
        roles,
        roleConfirmed,
        csrfToken,
        selectedRole,
        demoAccounts,
        openInfoModal,
        closeInfoModal,
        closeRoleModal,
        useDemoAccount,
        preCheck,
        confirmRole,
        initCsrf,
        handleSubmit,
        selectRole,
        setFormElement,
        selectRoleFromForm,
        showPassword,
        togglePasswordVisibility,
        getFirstError,
    };
});
