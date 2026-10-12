import { describe, it, expect, vi } from 'vitest';
import { right, left } from '@modules/Core/shared/either';
import { ServerFailure } from '@modules/Core/shared/core/failures';
import { DeleteBackupUseCase } from '@modules/Core/Platform/domain/usecases/delete-backup.usecase';

describe('DeleteBackupUseCase', () => {
    const mockRepo = { delete: vi.fn() };
    const useCase = new DeleteBackupUseCase(mockRepo as any);

    it('harus meneruskan filename dan historyType ke repository.delete', async () => {
        mockRepo.delete.mockResolvedValue(right(undefined));
        await useCase.execute('backup-2026.zip', 'manual');
        expect(mockRepo.delete).toHaveBeenCalledWith('backup-2026.zip', 'manual');
    });

    it('harus return Right saat sukses', async () => {
        mockRepo.delete.mockResolvedValue(right(undefined));
        expect((await useCase.execute('f.zip', 'manual')).isRight()).toBe(true);
    });

    it('harus return Left saat gagal', async () => {
        mockRepo.delete.mockResolvedValue(left(new ServerFailure('Error')));
        expect((await useCase.execute('f.zip', 'manual')).isLeft()).toBe(true);
    });
});
