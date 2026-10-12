import type { BackupRepository } from '@modules/Core/Platform/domain/repositories/backup.repository';
import type { BackupFormData } from '@modules/Core/Platform/domain/models/backup.model';

export class RestoreBackupUseCase {
    constructor(private repository: BackupRepository) {}

    async execute(data: BackupFormData) {
        return await this.repository.restore(data);
    }
}
