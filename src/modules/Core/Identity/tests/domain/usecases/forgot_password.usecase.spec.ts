import { describe, it, expect } from 'vitest';
import { ForgotPasswordUseCase } from '@modules/Core/Identity/domain/usecases/forgot_password.usecase';

describe('ForgotPasswordUseCase', () => {
    const useCase = new ForgotPasswordUseCase();

    describe('validate', () => {
        it('harus return null saat email valid', () => {
            expect(useCase.validate('superadmin@klinik.local')).toBeNull();
            expect(useCase.validate('test.user+alias@mail.co.id')).toBeNull();
        });

        it('harus return pesan error saat email kosong', () => {
            expect(useCase.validate('')).toBe('Email wajib diisi.');
            expect(useCase.validate('   ')).toBe('Email wajib diisi.');
        });

        it('harus return pesan error saat format email tidak valid', () => {
            expect(useCase.validate('bukan-email')).toBe('Format email tidak valid.');
            expect(useCase.validate('tanpa@domain')).toBe('Format email tidak valid.');
            expect(useCase.validate('@tanpa-lokal.com')).toBe('Format email tidak valid.');
        });
    });
});
