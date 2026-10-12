import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createUserVMStore } from '@modules/Core/Identity/presentations/viewmodels/user.vm';
import type { UserModel } from '@modules/Core/Identity/domain/models/user.model';
import { right, left } from '@modules/Core/shared/either';
import { ValidationFailure } from '@modules/Core/shared/core/failures';

// Definisi mock di top level tanpa variabel eksternal untuk menghindari hoisting error
vi.mock('@inertiajs/vue3', () => ({
    usePage: vi.fn(() => ({
        props: {
            auth: {
                user: {
                    permissions: ['settings.user.view', 'settings.user.manage'],
                },
            },
        },
    })),
    useForm: vi.fn((data) => ({
        ...data,
        processing: false,
        errors: {} as Record<string, string>,
        defaults: vi.fn(),
        reset: vi.fn(),
        clearErrors: vi.fn(),
        setError: vi.fn(),
        data: vi.fn(() => data),
    })),
    router: { reload: vi.fn() }
}));

vi.mock('sweetalert2', () => ({
    default: {
        fire: vi.fn().mockResolvedValue({ isConfirmed: true })
    }
}));

describe('useUserVM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('submits create form through save use case and closes modal on success', async () => {
        const saveUserUseCase = {
            execute: vi.fn().mockResolvedValue(right({ success: true })),
        };

        const useUserVM = createUserVMStore('user-vm-submit-test', {
            fetchUsersUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveUserUseCase,
            deleteUserUseCase: { execute: vi.fn() },
            exportUsersUseCase: { execute: vi.fn() },
            importUsersUseCase: { execute: vi.fn() },
            downloadUserImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = useUserVM();
        vm.showForm = true;
        vm.form.name = 'User Baru';
        vm.form.email = 'baru@example.com';
        vm.form.role_uuids = ['role-1'];

        await vm.submitForm();

        expect(saveUserUseCase.execute).toHaveBeenCalled();
        expect(vm.showForm).toBe(false);
    });

    it('maps validation errors from repository back into inertia form state', async () => {
        const failure = new ValidationFailure('Validation error', { email: ['Email invalid'] });
        const saveUserUseCase = {
            execute: vi.fn().mockResolvedValue(left(failure)),
        };

        const useUserVM = createUserVMStore('user-vm-error-test', {
            fetchUsersUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveUserUseCase,
            deleteUserUseCase: { execute: vi.fn() },
            exportUsersUseCase: { execute: vi.fn() },
            importUsersUseCase: { execute: vi.fn() },
            downloadUserImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = useUserVM();
        vm.showForm = true;
        await vm.submitForm();

        expect(vm.form.setError).toHaveBeenCalled();
        expect(vm.showForm).toBe(true);
    });

    it('blocks manage actions when permission is missing', async () => {
        const { usePage } = await import('@inertiajs/vue3');
        vi.mocked(usePage).mockReturnValue({
            props: {
                auth: {
                    user: {
                        permissions: ['settings.user.view'], // Tidak ada manage
                    },
                },
            },
        } as any);

        const deleteUserUseCase = {
            execute: vi.fn(),
        };

        const useUserVM = createUserVMStore('user-vm-permission-test', {
            fetchUsersUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveUserUseCase: { execute: vi.fn() },
            deleteUserUseCase,
            exportUsersUseCase: { execute: vi.fn() },
            importUsersUseCase: { execute: vi.fn() },
            downloadUserImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = useUserVM();
        const user = { uuid: 'user-1', name: 'Test' } as UserModel;

        await vm.deleteUser(user);

        expect(vm.canManageUsers).toBe(false);
        expect(deleteUserUseCase.execute).not.toHaveBeenCalled();
    });
});
