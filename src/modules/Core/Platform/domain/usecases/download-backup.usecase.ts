import type { BackupRepository } from '@modules/Core/Platform/domain/repositories/backup.repository';

export class DownloadBackupUseCase {
    constructor(private repository: BackupRepository) {}

    async execute(filename: string, historyType: string) {
        return await this.repository.download(filename, historyType);
    }
}
