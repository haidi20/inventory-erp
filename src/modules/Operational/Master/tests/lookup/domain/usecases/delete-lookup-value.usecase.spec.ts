import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { DeleteLookupValue } from '@modules/Operational/Master/domain/usecases/delete-lookup-value.usecase';

describe('DeleteLookupValue', () => {
    const mockRepo = { delete: vi.fn() };
    const useCase = new DeleteLookupValue(mockRepo as any);

    it('harus meneruskan uuid ke repository.delete', async () => {
        mockRepo.delete.mockResolvedValue(right(undefined));
        await useCase.execute('val-uuid');
        expect(mockRepo.delete).toHaveBeenCalledWith('val-uuid');
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
