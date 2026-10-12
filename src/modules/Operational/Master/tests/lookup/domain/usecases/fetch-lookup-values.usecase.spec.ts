import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { FetchLookupValues } from '@modules/Operational/Master/domain/usecases/fetch-lookup-values.usecase';

describe('FetchLookupValues', () => {
    const mockRepo = { paginate: vi.fn() };
    const useCase = new FetchLookupValues(mockRepo as any);

    it('harus meneruskan setId dan filters ke repository.paginate', async () => {
        mockRepo.paginate.mockResolvedValue(right({ data: [] }));
        const filters = { search: 'test', page: 1, per_page: 10 } as any;
        await useCase.execute('set-uuid', filters);
        expect(mockRepo.paginate).toHaveBeenCalledWith('set-uuid', filters);
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.paginate.mockResolvedValue(right({ data: [] }));
        expect((await useCase.execute('set', {} as any)).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.paginate.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute('set', {} as any)).isLeft()).toBe(true);
    });
});
