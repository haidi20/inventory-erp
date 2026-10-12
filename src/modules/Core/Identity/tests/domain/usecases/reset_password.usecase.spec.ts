import { describe, it, expect } from 'vitest';
import { ResetPasswordUseCase } from '@modules/Core/Identity/domain/usecases/reset_password.usecase';

describe('ResetPasswordUseCase', () => {
    const useCase = new ResetPasswordUseCase();

    describe('validate', () => {
        it('harus return null saat password valid dan cocok', () => {
            expect(useCase.validate('password123', 'password123')).toBeNull();
            expect(useCase.validate('12345678', '12345678')).toBeNull();
        });

        it('harus return pesan error saat password kurang dari 8 karakter', () => {
            expect(useCase.validate('short', 'short')).toBe('Password minimal 8 karakter.');
            expect(useCase.validate('1234567', '1234567')).toBe('Password minimal 8 karakter.');
        });

        it('harus return pesan error saat password kosong', () => {
            expect(useCase.validate('', '')).toBe('Password minimal 8 karakter.');
        });

        it('harus return pesan error saat konfirmasi tidak cocok', () => {
            expect(useCase.validate('password123', 'berbeda123')).toBe('Konfirmasi password tidak cocok.');
            expect(useCase.validate('abcdefgh', 'ABCDEFGH')).toBe('Konfirmasi password tidak cocok.');
        });
    });
});
