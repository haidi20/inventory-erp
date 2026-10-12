import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { ImportBackupsUseCase } from '@modules/Core/Platform/domain/usecases/import-backups.usecase';

describe('ImportBackupsUseCase', () => {
    const mockRepo = { import: vi.fn() };
    const useCase = new ImportBackupsUseCase(mockRepo as any);

    it('harus meneruskan file ke repository.import', async () => {
        mockRepo.import.mockResolvedValue(right(undefined));
        const file = new File([''], 'backup.zip');
        await useCase.execute(file);
        expect(mockRepo.import).toHaveBeenCalledWith(file);
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.import.mockResolvedValue(right(undefined));
        expect((await useCase.execute(new File([''], 'b.zip'))).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.import.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute(new File([''], 'b.zip'))).isLeft()).toBe(true);
    });
});
