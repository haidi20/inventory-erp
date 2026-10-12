import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { ExportLookupValues } from '@modules/Operational/Master/domain/usecases/export-lookup-values.usecase';

describe('ExportLookupValues', () => {
    const mockRepo = { export: vi.fn() };
    const useCase = new ExportLookupValues(mockRepo as any);

    it('harus meneruskan setId dan filters ke repository.export', async () => {
        mockRepo.export.mockResolvedValue(right(undefined));
        const filters = { search: 'test' } as any;
        await useCase.execute('set-uuid', filters);
        expect(mockRepo.export).toHaveBeenCalledWith('set-uuid', filters);
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.export.mockResolvedValue(right(undefined));
        expect((await useCase.execute('set', {} as any)).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.export.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute('set', {} as any)).isLeft()).toBe(true);
    });
});
