import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { ImportUnitsUseCase } from '@modules/Operational/Master/domain/usecases/import-units.usecase';

describe('ImportUnitsUseCase', () => {
    const mockRepo = { import: vi.fn() };
    const useCase = new ImportUnitsUseCase(mockRepo as any);

    it('harus meneruskan file ke repository.import', async () => {
        mockRepo.import.mockResolvedValue(right(undefined));
        const file = new File([''], 'units.xlsx');
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
});
