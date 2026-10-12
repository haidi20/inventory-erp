import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createRoleVMStore } from '@modules/Core/Identity/presentations/viewmodels/role.vm';
import type { RoleModel } from '@modules/Core/Identity/domain/models/role.model';
import { right, left } from '@modules/Core/shared/either';
import { ValidationFailure } from '@modules/Core/shared/core/failures';

// Definisi mock di top level tanpa variabel eksternal untuk menghindari hoisting error
vi.mock('@inertiajs/vue3', () => ({
    usePage: vi.fn(() => ({
        props: {
            auth: {
                user: {
                    permissions: ['settings.role.view', 'settings.role.manage'],
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

describe('roleoleVM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('submits create form through save use case and closes modal on success', async () => {
        const saveRoleUseCase = {
            execute: vi.fn().mockResolvedValue(right({ success: true })),
        };

        const roleoleVM = createRoleVMStore('role-vm-submit-test', {
            fetchRolesUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveRoleUseCase,
            deleteRoleUseCase: { execute: vi.fn() },
            exportRolesUseCase: { execute: vi.fn() },
            importRolesUseCase: { execute: vi.fn() },
            downloadRoleImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = roleoleVM();
        vm.showForm = true;
        vm.form.name = 'role_baru';
        vm.form.label = 'Role Baru';
        vm.form.permissions = ['p1'];

        await vm.submitForm();

        expect(saveRoleUseCase.execute).toHaveBeenCalled();
        expect(vm.showForm).toBe(false);
    });

    it('maps validation errors from repository back into inertia form state', async () => {
        const failure = new ValidationFailure('Validation error', { name: ['Name invalid'] });
        const saveRoleUseCase = {
            execute: vi.fn().mockResolvedValue(left(failure)),
        };

        const roleoleVM = createRoleVMStore('role-vm-error-test', {
            fetchRolesUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveRoleUseCase,
            deleteRoleUseCase: { execute: vi.fn() },
            exportRolesUseCase: { execute: vi.fn() },
            importRolesUseCase: { execute: vi.fn() },
            downloadRoleImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = roleoleVM();
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
                        permissions: ['settings.role.view'], // Tidak ada manage
                    },
                },
            },
        } as any);

        const deleteRoleUseCase = {
            execute: vi.fn(),
        };

        const roleoleVM = createRoleVMStore('role-vm-permission-test', {
            fetchRolesUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveRoleUseCase: { execute: vi.fn() },
            deleteRoleUseCase,
            exportRolesUseCase: { execute: vi.fn() },
            importRolesUseCase: { execute: vi.fn() },
            downloadRoleImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = roleoleVM();
        const role = { uuid: 'role-1', label: 'Test' } as RoleModel;

        await vm.deleteRole(role);

        expect(vm.canManageRoles).toBe(false);
        expect(deleteRoleUseCase.execute).not.toHaveBeenCalled();
    });
});
