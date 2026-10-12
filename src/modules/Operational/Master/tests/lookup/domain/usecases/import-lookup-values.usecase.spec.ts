import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { ImportLookupValues } from '@modules/Operational/Master/domain/usecases/import-lookup-values.usecase';

describe('ImportLookupValues', () => {
    const mockRepo = { import: vi.fn() };
    const useCase = new ImportLookupValues(mockRepo as any);

    it('harus meneruskan setId dan file ke repository.import', async () => {
        mockRepo.import.mockResolvedValue(right(undefined));
        const file = new File([''], 'test.xlsx');
        await useCase.execute('set-uuid', file);
        expect(mockRepo.import).toHaveBeenCalledWith('set-uuid', file);
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.import.mockResolvedValue(right(undefined));
        expect((await useCase.execute('set', new File([''], 'f.xlsx'))).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.import.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute('set', new File([''], 'f.xlsx'))).isLeft()).toBe(true);
    });
});
