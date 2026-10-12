import { BackupRepoImpl } from '@modules/Core/Platform/data/repositories/backup.repo_impl';
import { FetchBackupHistoryUseCase } from '@modules/Core/Platform/domain/usecases/fetch-backup-history.usecase';
import { RestoreBackupUseCase } from '@modules/Core/Platform/domain/usecases/restore-backup.usecase';
import { ExportBackupUseCase } from '@modules/Core/Platform/domain/usecases/export-backup.usecase';
import { DownloadBackupUseCase } from '@modules/Core/Platform/domain/usecases/download-backup.usecase';
 
export interface BackupVMDependencies {
    fetchBackupHistoryUseCase: Pick<ReturnType<typeof createBackupDependencies>['fetchBackupHistoryUseCase'], 'execute'>;
    restoreBackupUseCase: Pick<ReturnType<typeof createBackupDependencies>['restoreBackupUseCase'], 'execute'>;
    exportBackupUseCase: Pick<ReturnType<typeof createBackupDependencies>['exportBackupUseCase'], 'execute'>;
    downloadBackupUseCase: Pick<ReturnType<typeof createBackupDependencies>['downloadBackupUseCase'], 'execute'>;
}

export function createBackupDependencies(repository = new BackupRepoImpl()) {
    return {
        fetchBackupHistoryUseCase: new FetchBackupHistoryUseCase(repository),
        restoreBackupUseCase: new RestoreBackupUseCase(repository),
        exportBackupUseCase: new ExportBackupUseCase(repository),
        downloadBackupUseCase: new DownloadBackupUseCase(repository),
    };
}

export const {
    fetchBackupHistoryUseCase,
    restoreBackupUseCase,
    exportBackupUseCase,
    downloadBackupUseCase,
} = createBackupDependencies();
