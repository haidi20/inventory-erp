import type { BackupRepository } from '@modules/Core/Platform/domain/repositories/backup.repository';
import type { BackupFilters } from '@modules/Core/Platform/domain/models/backup.model';

export class FetchBackupHistoryUseCase {
    constructor(private repository: BackupRepository) {}

    async execute(filters: BackupFilters) {
        return await this.repository.fetchHistory(filters);
    }
}
