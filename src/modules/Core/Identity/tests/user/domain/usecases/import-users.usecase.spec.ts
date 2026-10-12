import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { ImportUsersUseCase } from '@modules/Core/Identity/domain/usecases/import-users.usecase';

describe('ImportUsersUseCase', () => {
    const mockRepo = { import: vi.fn() };
    const useCase = new ImportUsersUseCase(mockRepo as any);

    it('harus meneruskan file ke repository.import', async () => {
        mockRepo.import.mockResolvedValue(right(undefined));
        const file = new File([''], 'users.xlsx');
        await useCase.execute(file);
        expect(mockRepo.import).toHaveBeenCalledWith(file);
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.import.mockResolvedValue(right(undefined));
        expect((await useCase.execute(new File([''], 'u.xlsx'))).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.import.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute(new File([''], 'u.xlsx'))).isLeft()).toBe(true);
    });

    it('harus wrap exception menjadi Left(ServerFailure)', async () => {
        mockRepo.import.mockRejectedValue(new Error('Network error'));
        const result = await useCase.execute(new File([''], 'u.xlsx'));
        expect(result.isLeft()).toBe(true);
        result.fold((f) => expect(f).toBeInstanceOf(ServerFailure), () => {});
    });
});
