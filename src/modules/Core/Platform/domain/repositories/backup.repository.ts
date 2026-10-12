import type { BackupFormData, BackupFilters } from '@modules/Core/Platform/domain/models/backup.model';
import type { Either } from '@modules/Core/shared/either';
import type { Failure } from '@modules/Core/shared/core/failures';

export interface BackupRepository {
    fetchHistory(filters: BackupFilters): Promise<Either<Failure, any>>;
    export(data: { notes?: string; download_after_export?: boolean }): Promise<Either<Failure, void>>;
    restore(data: BackupFormData): Promise<Either<Failure, void>>;
    download(filename: string, historyType: string): Promise<Either<Failure, void>>;
}
