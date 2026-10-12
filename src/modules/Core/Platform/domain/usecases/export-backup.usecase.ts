import type { BackupRepository } from '@modules/Core/Platform/domain/repositories/backup.repository';

export class ExportBackupUseCase {
    constructor(private repository: BackupRepository) {}

    async execute(data: { notes?: string; download_after_export?: boolean }) {
        return await this.repository.export(data);
    }
}
