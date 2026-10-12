import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { ExportUnitsUseCase } from '@modules/Operational/Master/domain/usecases/export-units.usecase';

describe('ExportUnitsUseCase', () => {
    const mockRepo = { export: vi.fn() };
    const useCase = new ExportUnitsUseCase(mockRepo as any);

    it('harus meneruskan filters ke repository.export', async () => {
        mockRepo.export.mockResolvedValue(right(undefined));
        const filters = { search: 'kg' } as any;
        await useCase.execute(filters);
        expect(mockRepo.export).toHaveBeenCalledWith(filters);
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.export.mockResolvedValue(right(undefined));
        expect((await useCase.execute({} as any)).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.export.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute({} as any)).isLeft()).toBe(true);
    });
});
