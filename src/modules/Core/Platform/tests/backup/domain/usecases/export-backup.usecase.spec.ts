import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { ExportBackupUseCase } from '@modules/Core/Platform/domain/usecases/export-backup.usecase';

describe('ExportBackupUseCase', () => {
    const mockRepo = { export: vi.fn() };
    const useCase = new ExportBackupUseCase(mockRepo as any);

    it('harus meneruskan options ke repository.export', async () => {
        mockRepo.export.mockResolvedValue(right(undefined));
        const opts = { notes: 'Backup manual', download_after_export: true };
        await useCase.execute(opts);
        expect(mockRepo.export).toHaveBeenCalledWith(opts);
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.export.mockResolvedValue(right(undefined));
        expect((await useCase.execute({})).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.export.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute({})).isLeft()).toBe(true);
    });
});
