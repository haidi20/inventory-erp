import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { left, right } from '@modules/Core/shared/either';
import { ValidationFailure } from '@modules/Core/shared/core/failures';
import type { LookupModel } from '@modules/Operational/Master/domain/models/lookup.model';
import { createLookupVMStore } from '@modules/Operational/Master/presentations/viewmodels/lookup.vm';

vi.mock('@inertiajs/vue3', () => ({
    usePage: vi.fn(() => ({
        props: {
            auth: {
                user: {
                    permissions: ['master.lookup.manage'],
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

describe('useLookupVM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('submits create form through save use case and closes modal on success', async () => {
        const saveLookupUseCase = {
            execute: vi.fn().mockResolvedValue(right({ success: true })),
        };

        const useLookupVM = createLookupVMStore('lookup-vm-submit-test', {
            fetchLookupsUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveLookupUseCase,
            deleteLookupUseCase: { execute: vi.fn() },
            exportLookupsUseCase: { execute: vi.fn() },
            importLookupsUseCase: { execute: vi.fn() },
            downloadLookupImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = useLookupVM();
        vm.showForm = true;
        vm.form.name = 'Lookup Baru';
        vm.form.program_code = 'LOOKUP-BARU';

        await vm.submitForm();

        expect(saveLookupUseCase.execute).toHaveBeenCalled();
        expect(vm.showForm).toBe(false);
    });

    it('maps validation errors from repository back into inertia form state', async () => {
        const failure = new ValidationFailure('Validation error', { name: ['Nama wajib diisi'] });
        const saveLookupUseCase = {
            execute: vi.fn().mockResolvedValue(left(failure)),
        };

        const useLookupVM = createLookupVMStore('lookup-vm-error-test', {
            fetchLookupsUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveLookupUseCase,
            deleteLookupUseCase: { execute: vi.fn() },
            exportLookupsUseCase: { execute: vi.fn() },
            importLookupsUseCase: { execute: vi.fn() },
            downloadLookupImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = useLookupVM();
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
                        permissions: [],
                    },
                },
            },
        } as any);

        const deleteLookupUseCase = {
            execute: vi.fn(),
        };

        const useLookupVM = createLookupVMStore('lookup-vm-permission-test', {
            fetchLookupsUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            saveLookupUseCase: { execute: vi.fn() },
            deleteLookupUseCase,
            exportLookupsUseCase: { execute: vi.fn() },
            importLookupsUseCase: { execute: vi.fn() },
            downloadLookupImportTemplateUseCase: { execute: vi.fn() },
        });

        const vm = useLookupVM();
        const lookup = { uuid: 'lookup-1', name: 'Master Lookup', program_code: 'MASTER' } as LookupModel;

        await vm.deleteLookup(lookup);

        expect(vm.canManageLookups).toBe(false);
        expect(deleteLookupUseCase.execute).not.toHaveBeenCalled();
    });
});
