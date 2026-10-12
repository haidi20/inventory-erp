import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { ExportUsersUseCase } from '@modules/Core/Identity/domain/usecases/export-users.usecase';

describe('ExportUsersUseCase', () => {
    const mockRepo = { export: vi.fn() };
    const useCase = new ExportUsersUseCase(mockRepo as any);

    it('harus meneruskan filters ke repository.export', async () => {
        mockRepo.export.mockResolvedValue(right(undefined));
        const filters = { search: 'john' };
        await useCase.execute(filters);
        expect(mockRepo.export).toHaveBeenCalledWith(filters);
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.export.mockResolvedValue(right(undefined));
        expect((await useCase.execute({})).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.export.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute({})).isLeft()).toBe(true);
    });

    it('harus wrap exception menjadi Left(ServerFailure)', async () => {
        mockRepo.export.mockRejectedValue(new Error('Network error'));
        const result = await useCase.execute({});
        expect(result.isLeft()).toBe(true);
        result.fold((f) => expect(f).toBeInstanceOf(ServerFailure), () => {});
    });
});
