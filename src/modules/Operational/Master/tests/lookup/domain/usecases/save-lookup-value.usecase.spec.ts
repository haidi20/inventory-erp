import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ValidationFailure } from '@modules/Core/shared/core/failures';
import { SaveLookupValue } from '@modules/Operational/Master/domain/usecases/save-lookup-value.usecase';

describe('SaveLookupValue', () => {
    const mockRepo = { save: vi.fn() };
    const useCase = new SaveLookupValue(mockRepo as any);

    it('harus meneruskan setId dan data ke repository.save', async () => {
        mockRepo.save.mockResolvedValue(right({ uuid: 'v1' }));
        const data = { display_name: 'Val', program_code: 'VAL', description: '', seq: 1, active_flag: 'Y' as const };
        await useCase.execute('set-uuid', data);
        expect(mockRepo.save).toHaveBeenCalledWith('set-uuid', data);
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.save.mockResolvedValue(right({ uuid: 'v1' }));
        const data = { display_name: 'Val', program_code: 'VAL', description: '', seq: 1, active_flag: 'Y' as const };
        expect((await useCase.execute('set', data)).isRight()).toBe(true);
    });

    it('harus return Left(ValidationFailure) saat validasi gagal', async () => {
        mockRepo.save.mockResolvedValue(left(new ValidationFailure('Error', {})));
        const data = { display_name: '', program_code: '', description: '', seq: 0, active_flag: 'Y' as const };
        const result = await useCase.execute('set', data);
        expect(result.isLeft()).toBe(true);
        result.fold((f) => expect(f).toBeInstanceOf(ValidationFailure), () => {});
    });
});
