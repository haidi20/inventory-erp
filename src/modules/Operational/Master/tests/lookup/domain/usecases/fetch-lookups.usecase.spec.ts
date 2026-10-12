import { describe, expect, it, vi } from 'vitest';
import { right } from '@modules/Core/shared/either';
import { FetchLookupsUseCase } from '@modules/Operational/Master/domain/usecases/fetch-lookups.usecase';

describe('FetchLookupsUseCase', () => {
    it('meneruskan filter ke repository paginate', async () => {
        const repository = {
            paginate: vi.fn().mockResolvedValue(right({ data: [] })),
        } as any;

        const useCase = new FetchLookupsUseCase(repository);
        const result = await useCase.execute({ search: 'master' });

        expect(result.isRight()).toBe(true);
        expect(repository.paginate).toHaveBeenCalledWith({ search: 'master' });
    });
});
