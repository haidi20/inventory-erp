import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createUnitVMStore } from '@modules/Operational/Master/presentations/viewmodels/unit.vm';
import type { UnitModel } from '@modules/Operational/Master/domain/models/unit.model';
import { right, left } from '@modules/Core/shared/either';
import { ValidationFailure } from '@modules/Core/shared/core/failures';

vi.mock('@inertiajs/vue3', () => ({
    usePage: vi.fn(() => ({
        props: {
            auth: {
                user: {
                    permissions: ['master.manage'],
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
}));

vi.mock('sweetalert2', () => ({
    default: {
        fire: vi.fn().mockResolvedValue({ isConfirmed: true }),
    },
}));

describe('useUnitVM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('submits create form through save use case and closes modal on success', async () => {
        const saveUnitUseCase = {
            execute: vi.fn().mockResolvedValue(right({ success: true })),
        };

        const useUnitVM = createUnitVMStore('unit-vm-submit-test', {
            fetchUnitsUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveUnitUseCase,
            deleteUnitUseCase: { execute: vi.fn() },
            exportUnitsUseCase: { execute: vi.fn() },
            importUnitsUseCase: { execute: vi.fn() },
            downloadUnitImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = useUnitVM();
        vm.showForm = true;
        vm.form.code = 'PCS';
        vm.form.name = 'Pieces';

        await vm.submitForm();

        expect(saveUnitUseCase.execute).toHaveBeenCalled();
        expect(vm.showForm).toBe(false);
    });

    it('maps validation errors from repository back into inertia form state', async () => {
        const failure = new ValidationFailure('Validation error', { code: ['Kode wajib'] });
        const saveUnitUseCase = {
            execute: vi.fn().mockResolvedValue(left(failure)),
        };

        const useUnitVM = createUnitVMStore('unit-vm-error-test', {
            fetchUnitsUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveUnitUseCase,
            deleteUnitUseCase: { execute: vi.fn() },
            exportUnitsUseCase: { execute: vi.fn() },
            importUnitsUseCase: { execute: vi.fn() },
            downloadUnitImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = useUnitVM();
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
                        permissions: ['master.view'],
                    },
                },
            },
        } as any);

        const deleteUnitUseCase = {
            execute: vi.fn(),
        };

        const useUnitVM = createUnitVMStore('unit-vm-permission-test', {
            fetchUnitsUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveUnitUseCase: { execute: vi.fn() },
            deleteUnitUseCase,
            exportUnitsUseCase: { execute: vi.fn() },
            importUnitsUseCase: { execute: vi.fn() },
            downloadUnitImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = useUnitVM();
        const unit = { uuid: 'unit-1', code: 'PCS', name: 'Pieces', is_active: true } as UnitModel;

        await vm.deleteUnit(unit);

        expect(vm.canManageUnits).toBe(false);
        expect(deleteUnitUseCase.execute).not.toHaveBeenCalled();
    });
}
);
