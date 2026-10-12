import { describe, it, expect, vi } from 'vitest';
import { FetchBackupHistoryUseCase } from '@modules/Core/Platform/domain/usecases/fetch-backup-history.usecase';
import type { BackupRepository } from '@modules/Core/Platform/domain/repositories/backup.repository';
import { right } from '@modules/Core/shared/either';

describe('FetchBackupHistoryUseCase', () => {
    it('harus memanggil repository.fetchHistory dengan parameter yang benar', async () => {
        const mockRepo: BackupRepository = {
            fetchHistory: vi.fn().mockResolvedValue(right({ data: [] })),
            export: vi.fn(),
            restore: vi.fn(),
            download: vi.fn(),
        };

        const usecase = new FetchBackupHistoryUseCase(mockRepo);
        const filters = { search: 'test', history_type: 'manual' as const };
        
        const result = await usecase.execute(filters);

        expect(result.isRight()).toBe(true);
        expect(mockRepo.fetchHistory).toHaveBeenCalledWith(filters);
    });
});
