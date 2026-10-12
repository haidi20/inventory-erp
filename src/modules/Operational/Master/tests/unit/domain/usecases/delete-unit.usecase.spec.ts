import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { DeleteUnitUseCase } from '@modules/Operational/Master/domain/usecases/delete-unit.usecase';

describe('DeleteUnitUseCase', () => {
    const mockRepo = { delete: vi.fn() };
    const useCase = new DeleteUnitUseCase(mockRepo as any);

    it('harus meneruskan uuid ke repository.delete', async () => {
        mockRepo.delete.mockResolvedValue(right(undefined));
        await useCase.execute('unit-uuid');
        expect(mockRepo.delete).toHaveBeenCalledWith('unit-uuid');
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.delete.mockResolvedValue(right(undefined));
        expect((await useCase.execute('uuid')).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.delete.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute('uuid')).isLeft()).toBe(true);
    });
});
