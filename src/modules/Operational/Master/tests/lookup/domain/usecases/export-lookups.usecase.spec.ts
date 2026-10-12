import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { ExportLookupsUseCase } from '@modules/Operational/Master/domain/usecases/export-lookups.usecase';

describe('ExportLookupsUseCase', () => {
    const mockRepo = { export: vi.fn() };
    const useCase = new ExportLookupsUseCase(mockRepo as any);

    it('harus meneruskan filters ke repository.export', async () => {
        mockRepo.export.mockResolvedValue(right(undefined));
        const filters = { search: 'test' } as any;
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

    it('harus return Left saat exception', async () => {
        mockRepo.export.mockRejectedValue(new Error('Network'));
        const result = await useCase.execute({} as any);
        expect(result.isLeft()).toBe(true);
        result.fold((f) => expect(f).toBeInstanceOf(ServerFailure), () => {});
    });
});
