import { beforeEach, describe, it, expect, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';

// Kontrol execute dari luar test
let mockExecute = vi.fn().mockResolvedValue(right({ success: true, roles: [] }));

// Mock menggunakan class agar bisa dipakai dengan `new`
vi.mock('@modules/Core/Identity/domain/usecases/pre_check_roles.usecase', () => ({
    PreCheckRolesUseCase: class {
        execute(...args: any[]) { return mockExecute(...args); }
    },
}));

vi.mock('@modules/Core/Identity/data/repositories/auth.repo_impl', () => ({
    AuthRepoImpl: class {},
}));

import { useLoginVM } from '@modules/Core/Identity/presentations/viewmodels/login.vm';

describe('useLoginVM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        mockExecute = vi.fn().mockResolvedValue(right({ success: true, roles: [] }));
    });

    // ── State default ────────────────────────────────────────────────────────

    it('harus inisialisasi state dengan nilai default', () => {
        const vm = useLoginVM();
        expect(vm.email).toBe('');
        expect(vm.password).toBe('');
        expect(vm.remember).toBe(false);
        expect(vm.isLoading).toBe(false);
        expect(vm.isInfoModalOpen).toBe(false);
        expect(vm.isRoleModalOpen).toBe(false);
        expect(vm.roles).toEqual([]);
        expect(vm.roleConfirmed).toBe(false);
    });

    it('harus memiliki 2 demo accounts aktif dengan email @klinik.local', () => {
        const vm = useLoginVM();
        expect(vm.demoAccounts).toHaveLength(2);
        const roles = vm.demoAccounts.map(a => a.role);
        expect(roles).toEqual(['Owner', 'Apoteker']);
        vm.demoAccounts.forEach(a => expect(a.email).toContain('@klinik.local'));
    });

    // ── Modal controls ───────────────────────────────────────────────────────

    it('openInfoModal harus set isInfoModalOpen = true', () => {
        const vm = useLoginVM();
        vm.openInfoModal();
        expect(vm.isInfoModalOpen).toBe(true);
    });

    it('closeInfoModal harus set isInfoModalOpen = false', () => {
        const vm = useLoginVM();
        vm.openInfoModal();
        vm.closeInfoModal();
        expect(vm.isInfoModalOpen).toBe(false);
    });

    it('closeRoleModal harus set isRoleModalOpen = false', () => {
        const vm = useLoginVM();
        vm.isRoleModalOpen = true;
        vm.closeRoleModal();
        expect(vm.isRoleModalOpen).toBe(false);
    });

    // ── useDemoAccount ───────────────────────────────────────────────────────

    it('useDemoAccount harus mengisi email dan password lalu tutup modal', () => {
        const vm = useLoginVM();
        vm.openInfoModal();
        vm.useDemoAccount('superadmin', 'superadmin');
        expect(vm.email).toBe('superadmin');
        expect(vm.password).toBe('superadmin');
        expect(vm.isInfoModalOpen).toBe(false);
    });

    // ── preCheck ─────────────────────────────────────────────────────────────

    it('preCheck harus return "submit" saat server return ServerFailure (401)', async () => {
        mockExecute.mockResolvedValue(left(new ServerFailure('Kredensial tidak valid.')));
        const vm = useLoginVM();
        const outcome = await vm.preCheck('csrf-token');
        expect(outcome).toBe('submit');
    });

    it('preCheck harus return "submit" saat hanya ada satu role', async () => {
        mockExecute.mockResolvedValue(right({
            success: true,
            roles: [{ name: 'owner', label: 'Owner' }],
        }));
        const vm = useLoginVM();
        const outcome = await vm.preCheck('csrf-token');
        expect(outcome).toBe('submit');
        expect(vm.isRoleModalOpen).toBe(false);
    });

    it('preCheck harus return "roles" dan buka modal saat ada 2+ roles', async () => {
        mockExecute.mockResolvedValue(right({
            success: true,
            roles: [
                { name: 'owner', label: 'Owner' },
                { name: 'dokter', label: 'Dokter' },
            ],
        }));
        const vm = useLoginVM();
        const outcome = await vm.preCheck('csrf-token');
        expect(outcome).toBe('roles');
        expect(vm.isRoleModalOpen).toBe(true);
        expect(vm.roles).toHaveLength(2);
    });

    it('preCheck harus set isLoading = true saat proses, false setelah selesai', async () => {
        let resolveExec!: (v: any) => void;
        mockExecute.mockReturnValue(new Promise(res => { resolveExec = res; }));

        const vm = useLoginVM();
        const checking = vm.preCheck('csrf');

        expect(vm.isLoading).toBe(true);
        resolveExec(right({ success: true, roles: [] }));
        await checking;
        expect(vm.isLoading).toBe(false);
    });

    // ── confirmRole ──────────────────────────────────────────────────────────

    it('confirmRole harus set roleConfirmed = true dan tutup role modal', () => {
        const vm = useLoginVM();
        vm.isRoleModalOpen = true;
        vm.confirmRole();
        expect(vm.roleConfirmed).toBe(true);
        expect(vm.isRoleModalOpen).toBe(false);
    });

    // ── handleSubmit ─────────────────────────────────────────────────────────

    it('handleSubmit harus preventDefault dan submit form saat preCheck return "submit"', async () => {
        mockExecute.mockResolvedValue(right({ success: true, roles: [] }));
        const vm = useLoginVM();
        const mockForm = {
            querySelector: vi.fn().mockReturnValue({ value: 'csrf-token' }),
            submit: vi.fn(),
        };
        const mockEvent = { preventDefault: vi.fn(), target: mockForm };

        await vm.handleSubmit(mockEvent as any);

        expect(mockEvent.preventDefault).toHaveBeenCalled();
        expect(mockForm.submit).toHaveBeenCalled();
        expect(vm.roleConfirmed).toBe(true);
    });

    it('handleSubmit harus skip jika roleConfirmed sudah true', async () => {
        const vm = useLoginVM();
        vm.roleConfirmed = true;
        const mockEvent = { preventDefault: vi.fn(), target: {} };

        await vm.handleSubmit(mockEvent as any);
        expect(mockEvent.preventDefault).not.toHaveBeenCalled();
    });

    it('handleSubmit harus tidak submit form saat preCheck return "roles" (modal open)', async () => {
        mockExecute.mockResolvedValue(right({
            success: true,
            roles: [
                { name: 'owner', label: 'Owner' },
                { name: 'dokter', label: 'Dokter' },
            ],
        }));
        const vm = useLoginVM();
        const mockForm = {
            querySelector: vi.fn().mockReturnValue({ value: 'csrf' }),
            submit: vi.fn(),
        };
        await vm.handleSubmit({ preventDefault: vi.fn(), target: mockForm } as any);
        expect(mockForm.submit).not.toHaveBeenCalled();
        expect(vm.isRoleModalOpen).toBe(true);
    });

    // ── Remember me ──────────────────────────────────────────────────────────

    it('remember harus bisa di-toggle dua arah', () => {
        const vm = useLoginVM();
        expect(vm.remember).toBe(false);

        vm.remember = true;
        expect(vm.remember).toBe(true);

        vm.remember = false;
        expect(vm.remember).toBe(false);
    });

    it('handleSubmit tetap submit form saat remember aktif', async () => {
        mockExecute.mockResolvedValue(right({ success: true, roles: [] }));
        const vm = useLoginVM();
        vm.remember = true;

        const mockForm = {
            querySelector: vi.fn().mockReturnValue({ value: 'csrf-token' }),
            submit: vi.fn(),
        };

        await vm.handleSubmit({ preventDefault: vi.fn(), target: mockForm } as any);

        expect(mockForm.submit).toHaveBeenCalled();
    });
});
