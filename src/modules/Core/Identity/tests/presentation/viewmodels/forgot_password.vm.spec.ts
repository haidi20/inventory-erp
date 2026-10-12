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

import { useForgotPasswordVM } from '@modules/Core/Identity/presentations/viewmodels/forgot_password.vm';

describe('useForgotPasswordVM', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('harus inisialisasi state dengan nilai default', () => {
        const vm = useForgotPasswordVM();
        expect(vm.form.email).toBe('');
        expect(vm.clientError).toBeNull();
    });

    it('submit harus set clientError saat email kosong', () => {
        const vm = useForgotPasswordVM();
        vm.form.email = '';
        vm.submit();
        expect(vm.clientError).toBe('Email wajib diisi.');
        expect(vm.form.post).not.toHaveBeenCalled();
    });

    it('submit harus set clientError saat format email tidak valid', () => {
        const vm = useForgotPasswordVM();
        vm.form.email = 'bukan-email';
        vm.submit();
        expect(vm.clientError).toBe('Format email tidak valid.');
        expect(vm.form.post).not.toHaveBeenCalled();
    });

    it('submit harus clear clientError dan memanggil form.post saat email valid', () => {
        const vm = useForgotPasswordVM();
        vm.clientError = 'error lama';
        vm.form.email = 'superadmin@klinik.local';
        vm.submit();
        expect(vm.clientError).toBeNull();
        expect(vm.form.post).toHaveBeenCalledWith('/forgot-password', expect.any(Object));
    });

    it('reset harus bersihkan form dan clientError', () => {
        const vm = useForgotPasswordVM();
        vm.form.email = 'superadmin@klinik.local';
        vm.clientError = 'ada error';
        vm.reset();
        expect(vm.clientError).toBeNull();
        expect(vm.form.reset).toHaveBeenCalled();
    });
});
