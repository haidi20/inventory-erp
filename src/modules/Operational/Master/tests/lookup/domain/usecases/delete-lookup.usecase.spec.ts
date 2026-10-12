import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { DeleteLookupUseCase } from '@modules/Operational/Master/domain/usecases/delete-lookup.usecase';

describe('DeleteLookupUseCase', () => {
    const mockRepo = { delete: vi.fn() };
    const useCase = new DeleteLookupUseCase(mockRepo as any);

    it('harus meneruskan uuid ke repository.delete', async () => {
        mockRepo.delete.mockResolvedValue(right(undefined));
        await useCase.execute('target-uuid');
        expect(mockRepo.delete).toHaveBeenCalledWith('target-uuid');
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.delete.mockResolvedValue(right(undefined));
        const result = await useCase.execute('uuid');
        expect(result.isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.delete.mockResolvedValue(left(new ServerFailure('Error')));
        const result = await useCase.execute('uuid');
        expect(result.isLeft()).toBe(true);
    });

    it('harus return Left saat exception', async () => {
        mockRepo.delete.mockRejectedValue(new Error('Network'));
        const result = await useCase.execute('uuid');
        expect(result.isLeft()).toBe(true);
        result.fold((f) => expect(f).toBeInstanceOf(ServerFailure), () => {});
    });
});
