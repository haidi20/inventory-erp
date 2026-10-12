import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { FetchItemConversionsUseCase } from '@modules/Operational/Master/domain/usecases/fetch-item-conversions.usecase';

describe('FetchItemConversionsUseCase', () => {
    const mockRepo = { fetch: vi.fn() };
    const useCase = new FetchItemConversionsUseCase(mockRepo as any);

    it('harus meneruskan filters ke repository.fetch', async () => {
        mockRepo.fetch.mockResolvedValue(right({ items: [], total: 0, current_page: 1, per_page: 15 }));
        const filters = { search: 'test', page: 1, per_page: 15, sort_by: 'updated_at' as any, sort_dir: 'desc' as any };
        await useCase.execute(filters);
        expect(mockRepo.fetch).toHaveBeenCalledWith(filters);
    });

    it('harus return Right saat repository sukses', async () => {
        const summary = { items: [{ uuid: '1' }], total: 1, current_page: 1, per_page: 15 };
        mockRepo.fetch.mockResolvedValue(right(summary));
        const result = await useCase.execute({} as any);
        expect(result.isRight()).toBe(true);
    });

    it('harus return Left saat repository gagal', async () => {
        mockRepo.fetch.mockResolvedValue(left(new ServerFailure('Error')));
        const result = await useCase.execute({} as any);
        expect(result.isLeft()).toBe(true);
    });
});
