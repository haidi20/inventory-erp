import apiService from '@modules/Core/shared/core/api_service';
import type { BackupRepository } from '@modules/Core/Platform/domain/repositories/backup.repository';
import { type BackupFormData, type BackupFilters } from '@modules/Core/Platform/domain/models/backup.model';
import { type Either, left, right } from '@modules/Core/shared/either';
import { type Failure, ServerFailure, ValidationFailure } from '@modules/Core/shared/core/failures';

export class BackupRepoImpl implements BackupRepository {
    async fetchHistory(filters: BackupFilters): Promise<Either<Failure, any>> {
        try {
            const response = await apiService.get('/settings/backups/history-datatable', { 
                params: filters,
                headers: { 'Cache-Control': 'no-cache' } 
            });
            return right(response.data);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }

    async export(data: { notes?: string; download_after_export?: boolean }): Promise<Either<Failure, void>> {
        try {
            const response = await apiService.post('/settings/backups/export', data, {
                responseType: 'blob'
            });
            
            const filename = `backup_${new Date().toISOString().split('T')[0]}.sql`;
            this.downloadFile(response.data, filename);
            
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }

    async restore(data: BackupFormData): Promise<Either<Failure, void>> {
        try {
            const formData = new FormData();
            if (data.backup_file) {
                formData.append('backup_file', data.backup_file);
            }
            formData.append('create_pre_restore_backup', String(data.create_pre_restore_backup));

            await apiService.post('/settings/backups/restore', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            return right(undefined);
        } catch (error: any) {
            if (error.response?.status === 422) {
                return left(new ValidationFailure(error.response.data.message, error.response.data.errors));
            }
            return left(new ServerFailure(error.message));
        }
    }

    async download(filename: string, historyType: string): Promise<Either<Failure, void>> {
        try {
            const response = await apiService.get(`/settings/backups/download/${filename}`, {
                params: { history_type: historyType },
                responseType: 'blob'
            });
            this.downloadFile(response.data, filename);
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }

    private downloadFile(blob: Blob, filename: string): void {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    }
}
