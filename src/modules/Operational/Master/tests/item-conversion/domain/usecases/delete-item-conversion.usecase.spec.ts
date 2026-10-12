import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { DeleteItemConversionUseCase } from '@modules/Operational/Master/domain/usecases/delete-item-conversion.usecase';

describe('DeleteItemConversionUseCase', () => {
    const mockRepo = { delete: vi.fn() };
    const useCase = new DeleteItemConversionUseCase(mockRepo as any);

    it('harus meneruskan uuid ke repository.delete', async () => {
        mockRepo.delete.mockResolvedValue(right(undefined));
        await useCase.execute('target-uuid');
        expect(mockRepo.delete).toHaveBeenCalledWith('target-uuid');
    });

    it('harus return Right saat delete sukses', async () => {
        mockRepo.delete.mockResolvedValue(right(undefined));
        const result = await useCase.execute('uuid');
        expect(result.isRight()).toBe(true);
    });

    it('harus return Left saat delete gagal', async () => {
        mockRepo.delete.mockResolvedValue(left(new ServerFailure('Server error')));
        const result = await useCase.execute('uuid');
        expect(result.isLeft()).toBe(true);
    });
});
