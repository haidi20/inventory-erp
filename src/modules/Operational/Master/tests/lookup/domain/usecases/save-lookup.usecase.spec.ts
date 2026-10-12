import { describe, expect, it, vi } from 'vitest';
import { right } from '@modules/Core/shared/either';
import { SaveLookupUseCase } from '@modules/Operational/Master/domain/usecases/save-lookup.usecase';

describe('SaveLookupUseCase', () => {
    it('meneruskan payload ke repository save', async () => {
        const repository = {
            save: vi.fn().mockResolvedValue(right({ uuid: 'lookup-1' })),
        } as any;

        const useCase = new SaveLookupUseCase(repository);
        const result = await useCase.execute({ name: 'Lookup', program_code: 'LOOKUP' });

        expect(result.isRight()).toBe(true);
        expect(repository.save).toHaveBeenCalledWith({ name: 'Lookup', program_code: 'LOOKUP' });
    });
});
