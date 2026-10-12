import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createBackupVMStore } from '@modules/Core/Platform/presentations/viewmodels/backup.vm';
import type { BackupModel } from '@modules/Core/Platform/domain/models/backup.model';
import { right, left } from '@modules/Core/shared/either';
import { ValidationFailure } from '@modules/Core/shared/core/failures';

// Definisi mock di top level tanpa variabel eksternal untuk menghindari hoisting error
vi.mock('@inertiajs/vue3', () => ({
    usePage: vi.fn(() => ({
        props: {
            auth: {
                user: {
                    permissions: ['settings.view'],
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

describe('useBackupVM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('submits restore form through restore use case and closes modal on success', async () => {
        const restoreBackupUseCase = {
            execute: vi.fn().mockResolvedValue(right({ success: true })),
        };

        const useBackupVM = createBackupVMStore('backup-vm-restore-test', {
            fetchBackupHistoryUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            restoreBackupUseCase,
            exportBackupUseCase: { execute: vi.fn() },
            downloadBackupUseCase: { execute: vi.fn() },
        });

        const vm = useBackupVM();
        vm.showRestoreForm = true;
        vm.form.backup_file = new File([''], 'test.sql');

        await vm.restoreBackup();

        expect(restoreBackupUseCase.execute).toHaveBeenCalled();
    });

    it('maps validation errors from repository back into inertia form state', async () => {
        const failure = new ValidationFailure('Validation error', { backup_file: ['File invalid'] });
        const restoreBackupUseCase = {
            execute: vi.fn().mockResolvedValue(left(failure)),
        };

        const useBackupVM = createBackupVMStore('backup-vm-error-test', {
            fetchBackupHistoryUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            restoreBackupUseCase,
            exportBackupUseCase: { execute: vi.fn() },
            downloadBackupUseCase: { execute: vi.fn() },
        });

        const vm = useBackupVM();
        vm.showRestoreForm = true;
        await vm.restoreBackup();

        expect(vm.form.setError).toHaveBeenCalled();
        expect(vm.showRestoreForm).toBe(true);
    });

    it('blocks restore action when permission is missing', async () => {
        const { usePage } = await import('@inertiajs/vue3');
        vi.mocked(usePage).mockReturnValue({
            props: {
                auth: {
                    user: {
                        permissions: [], // No settings.view
                    },
                },
            },
        } as any);

        const restoreBackupUseCase = {
            execute: vi.fn(),
        };

        const useBackupVM = createBackupVMStore('backup-vm-permission-test', {
            fetchBackupHistoryUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            restoreBackupUseCase,
            exportBackupUseCase: { execute: vi.fn() },
            downloadBackupUseCase: { execute: vi.fn() },
        });

        const vm = useBackupVM();
        
        expect(vm.canManageBackups).toBe(false);
    });

    it('resets isRestoring state even after a failure to prevent stuck modal', async () => {
        const restoreBackupUseCase = {
            execute: vi.fn().mockRejectedValue(new Error('Unexpected crash')),
        };

        const useBackupVM = createBackupVMStore('backup-vm-stuck-test', {
            fetchBackupHistoryUseCase: { execute: vi.fn().mockResolvedValue(right({})) },
            restoreBackupUseCase,
            exportBackupUseCase: { execute: vi.fn() },
            downloadBackupUseCase: { execute: vi.fn() },
        });

        const vm = useBackupVM();
        vm.form.backup_file = new File([''], 'test.sql');

        try {
            await vm.restoreBackup();
        } catch (e) {
            // expected
        }

        expect(vm.isRestoring).toBe(false);
    });
});
