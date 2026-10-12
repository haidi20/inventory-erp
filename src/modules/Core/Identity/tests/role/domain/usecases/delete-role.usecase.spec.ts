import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { DeleteRoleUseCase } from '@modules/Core/Identity/domain/usecases/delete-role.usecase';

describe('DeleteRoleUseCase', () => {
    const mockRepo = { delete: vi.fn() };
    const useCase = new DeleteRoleUseCase(mockRepo as any);

    it('harus meneruskan uuid ke repository.delete', async () => {
        mockRepo.delete.mockResolvedValue(right(undefined));
        await useCase.execute('uuid-123');
        expect(mockRepo.delete).toHaveBeenCalledWith('uuid-123');
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.delete.mockResolvedValue(right(undefined));
        expect((await useCase.execute('uuid-123')).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.delete.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute('uuid-123')).isLeft()).toBe(true);
    });

    it('harus wrap exception menjadi Left(ServerFailure)', async () => {
        mockRepo.delete.mockRejectedValue(new Error('Network error'));
        const result = await useCase.execute('uuid-bad');
        expect(result.isLeft()).toBe(true);
        result.fold((f) => expect(f).toBeInstanceOf(ServerFailure), () => {});
    });
});
