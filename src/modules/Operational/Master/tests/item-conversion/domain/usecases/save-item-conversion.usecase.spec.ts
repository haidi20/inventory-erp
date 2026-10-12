import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ValidationFailure } from '@modules/Core/shared/core/failures';
import { SaveItemConversionUseCase } from '@modules/Operational/Master/domain/usecases/save-item-conversion.usecase';

describe('SaveItemConversionUseCase', () => {
    const mockRepo = { save: vi.fn() };
    const useCase = new SaveItemConversionUseCase(mockRepo as any);

    it('harus meneruskan data ke repository.save', async () => {
        mockRepo.save.mockResolvedValue(right({ uuid: 'new' }));
        const data = { item_uuid: 'a', unit_uuid: 'b', conversion_value: 5 };
        await useCase.execute(data);
        expect(mockRepo.save).toHaveBeenCalledWith(data);
    });

    it('harus return Right dengan item baru saat sukses', async () => {
        mockRepo.save.mockResolvedValue(right({ uuid: 'new-uuid' }));
        const result = await useCase.execute({});
        expect(result.isRight()).toBe(true);
        result.fold(() => {}, (item) => expect(item.uuid).toBe('new-uuid'));
    });

    it('harus return Left(ValidationFailure) saat validasi gagal', async () => {
        mockRepo.save.mockResolvedValue(left(new ValidationFailure('Validasi gagal', {})));
        const result = await useCase.execute({});
        expect(result.isLeft()).toBe(true);
        result.fold((f) => expect(f).toBeInstanceOf(ValidationFailure), () => {});
    });
});
