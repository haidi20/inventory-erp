import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { DownloadBackupUseCase } from '@modules/Core/Platform/domain/usecases/download-backup.usecase';

describe('DownloadBackupUseCase', () => {
    const mockRepo = { download: vi.fn() };
    const useCase = new DownloadBackupUseCase(mockRepo as any);

    it('harus meneruskan filename dan historyType ke repository.download', async () => {
        mockRepo.download.mockResolvedValue(right(undefined));
        await useCase.execute('backup-2026.zip', 'manual');
        expect(mockRepo.download).toHaveBeenCalledWith('backup-2026.zip', 'manual');
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.download.mockResolvedValue(right(undefined));
        expect((await useCase.execute('f.zip', 'auto')).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.download.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute('f.zip', 'auto')).isLeft()).toBe(true);
    });
});
