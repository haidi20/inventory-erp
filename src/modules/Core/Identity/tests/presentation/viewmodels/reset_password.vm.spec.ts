import { beforeEach, describe, it, expect, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';

// Mock Inertia useForm
vi.mock('@inertiajs/vue3', () => ({
    useForm: vi.fn((initialData) => ({
        ...initialData,
        processing: false,
        errors: {} as Record<string, string>,
        reset: vi.fn(),
        clearErrors: vi.fn(),
        post: vi.fn(),
    })),
}));

import { useResetPasswordVM } from '@modules/Core/Identity/presentations/viewmodels/reset_password.vm';

describe('useResetPasswordVM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('harus inisialisasi state dengan nilai default', () => {
        const vm = useResetPasswordVM();
        expect(vm.form.token).toBe('');
        expect(vm.form.email).toBe('');
        expect(vm.form.password).toBe('');
        expect(vm.form.password_confirmation).toBe('');
        expect(vm.clientError).toBeNull();
        expect(vm.showPassword).toBe(false);
        expect(vm.showConfirmPassword).toBe(false);
    });

    it('initForm harus set token dan email ke form', () => {
        const vm = useResetPasswordVM();
        vm.initForm('reset-token-abc', 'superadmin');
        expect(vm.form.token).toBe('reset-token-abc');
        expect(vm.form.email).toBe('superadmin');
    });

    it('toggleShowPassword harus membalik nilai showPassword', () => {
        const vm = useResetPasswordVM();
        expect(vm.showPassword).toBe(false);
        vm.toggleShowPassword();
        expect(vm.showPassword).toBe(true);
        vm.toggleShowPassword();
        expect(vm.showPassword).toBe(false);
    });

    it('toggleShowConfirmPassword harus membalik nilai showConfirmPassword', () => {
        const vm = useResetPasswordVM();
        vm.toggleShowConfirmPassword();
        expect(vm.showConfirmPassword).toBe(true);
        vm.toggleShowConfirmPassword();
        expect(vm.showConfirmPassword).toBe(false);
    });

    it('submit harus set clientError saat password kurang dari 8 karakter', () => {
        const vm = useResetPasswordVM();
        vm.form.password = 'short';
        vm.form.password_confirmation = 'short';
        vm.submit();
        expect(vm.clientError).toBe('Password minimal 8 karakter.');
        expect(vm.form.post).not.toHaveBeenCalled();
    });

    it('submit harus set clientError saat konfirmasi tidak cocok', () => {
        const vm = useResetPasswordVM();
        vm.form.password = 'password123';
        vm.form.password_confirmation = 'berbeda123';
        vm.submit();
        expect(vm.clientError).toBe('Konfirmasi password tidak cocok.');
        expect(vm.form.post).not.toHaveBeenCalled();
    });

    it('submit harus memanggil form.post ke /reset-password saat validasi lolos', () => {
        const vm = useResetPasswordVM();
        vm.form.token = 'valid-token';
        vm.form.email = 'superadmin';
        vm.form.password = 'newpassword123';
        vm.form.password_confirmation = 'newpassword123';
        vm.submit();
        expect(vm.clientError).toBeNull();
        expect(vm.form.post).toHaveBeenCalledWith('/reset-password', expect.any(Object));
    });

    it('submit harus clear clientError sebelum validasi ulang', () => {
        const vm = useResetPasswordVM();
        vm.clientError = 'error lama';
        vm.form.password = 'newpassword123';
        vm.form.password_confirmation = 'newpassword123';
        vm.submit();
        expect(vm.clientError).toBeNull();
    });
});
