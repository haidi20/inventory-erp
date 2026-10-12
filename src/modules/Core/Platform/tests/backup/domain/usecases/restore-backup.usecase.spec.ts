import { describe, it, expect, vi } from 'vitest';
import { RestoreBackupUseCase } from '@modules/Core/Platform/domain/usecases/restore-backup.usecase';
import type { BackupRepository } from '@modules/Core/Platform/domain/repositories/backup.repository';
import { right } from '@modules/Core/shared/either';

describe('RestoreBackupUseCase', () => {
    it('harus memanggil repository.restore dengan data yang benar', async () => {
        const mockRepo: BackupRepository = {
            fetchHistory: vi.fn(),
            export: vi.fn(),
            restore: vi.fn().mockResolvedValue(right(undefined)),
            download: vi.fn(),
        };

        const usecase = new RestoreBackupUseCase(mockRepo);
        const formData = {
            backup_file: new File([''], 'backup.sql'),
            create_pre_restore_backup: true
        };

        const result = await usecase.execute(formData);

        expect(result.isRight()).toBe(true);
        expect(mockRepo.restore).toHaveBeenCalledWith(formData);
    });
});
