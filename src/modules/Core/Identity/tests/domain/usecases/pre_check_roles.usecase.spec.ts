import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { PreCheckRolesUseCase } from '@modules/Core/Identity/domain/usecases/pre_check_roles.usecase';

describe('PreCheckRolesUseCase', () => {
    const mockRepo = {
        preCheckRoles: vi.fn(),
    };

    const useCase = new PreCheckRolesUseCase(mockRepo as any);

    it('harus meneruskan email, password, csrfToken ke repository', async () => {
        mockRepo.preCheckRoles.mockResolvedValue(right({ success: true, roles: [] }));

        await useCase.execute('superadmin', 'superadmin', 'csrf-abc');

        expect(mockRepo.preCheckRoles).toHaveBeenCalledWith(
            'superadmin',
            'superadmin',
            'csrf-abc'
        );
    });

    it('harus return Right dengan data roles saat repository sukses', async () => {
        const payload = { success: true, roles: [{ name: 'owner', label: 'Owner' }] };
        mockRepo.preCheckRoles.mockResolvedValue(right(payload));

        const result = await useCase.execute('superadmin', 'superadmin', 'csrf');

        expect(result.isRight()).toBe(true);
        result.fold(
            () => { throw new Error('should not be left'); },
            (data) => {
                expect(data.success).toBe(true);
                expect(data.roles[0].name).toBe('owner');
            }
        );
    });

    it('harus return Left saat repository gagal', async () => {
        mockRepo.preCheckRoles.mockResolvedValue(left(new ServerFailure('Kredensial tidak valid.')));

        const result = await useCase.execute('wrong@klinik.local', 'wrongpass', 'csrf');

        expect(result.isLeft()).toBe(true);
        result.fold(
            (failure) => expect(failure.message).toBe('Kredensial tidak valid.'),
            () => { throw new Error('should not be right'); }
        );
    });
});
