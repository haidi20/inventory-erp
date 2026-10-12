import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { ImportLookupsUseCase } from '@modules/Operational/Master/domain/usecases/import-lookups.usecase';

describe('ImportLookupsUseCase', () => {
    const mockRepo = { import: vi.fn() };
    const useCase = new ImportLookupsUseCase(mockRepo as any);

    it('harus meneruskan file ke repository.import', async () => {
        mockRepo.import.mockResolvedValue(right(undefined));
        const file = new File([''], 'test.xlsx');
        await useCase.execute(file);
        expect(mockRepo.import).toHaveBeenCalledWith(file);
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.import.mockResolvedValue(right(undefined));
        expect((await useCase.execute(new File([''], 'f.xlsx'))).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.import.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute(new File([''], 'f.xlsx'))).isLeft()).toBe(true);
    });

    it('harus return Left saat exception', async () => {
        mockRepo.import.mockRejectedValue(new Error('Network'));
        const result = await useCase.execute(new File([''], 'f.xlsx'));
        expect(result.isLeft()).toBe(true);
        result.fold((f) => expect(f).toBeInstanceOf(ServerFailure), () => {});
    });
});
