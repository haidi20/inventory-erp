import apiService from '@modules/Core/shared/core/api_service';
import type { UserRepository } from '@modules/Core/Identity/domain/repositories/user.repository';
import { UserSearchColumn, type UserFormData, type UserFilters } from '@modules/Core/Identity/domain/models/user.model';
import { type Either, left, right } from '@modules/Core/shared/either';
import { type Failure, ServerFailure, ValidationFailure } from '@modules/Core/shared/core/failures';

export class UserRepoImpl implements UserRepository {
    async paginate(filters: UserFilters): Promise<Either<Failure, any>> {
        try {
            const response = await apiService.get('/settings/users', { 
                params: filters,
                // Pastikan cache tidak mengganggu token baru
                headers: { 'Cache-Control': 'no-cache' } 
            });
            return right(response.data);
        } catch (error: any) {
            if (error.response?.status === 401) {
                return left(new ServerFailure('Sesi API berakhir, silakan muat ulang halaman.'));
            }
            return left(new ServerFailure(error.message));
        }
    }

    async search(query: string, column: UserSearchColumn = UserSearchColumn.ALL): Promise<Either<Failure, any>> {
        return this.paginate({ search: query, search_column: column, page: 1 });
    }

    async changePerPage(perPage: number): Promise<Either<Failure, any>> {
        return this.paginate({ per_page: perPage, page: 1 });
    }

    async changePage(page: number): Promise<Either<Failure, any>> {
        return this.paginate({ page });
    }

    async save(data: UserFormData): Promise<Either<Failure, any>> {
        try {
            let response;
            if (data.uuid) {
                response = await apiService.put(`/settings/users/${data.uuid}`, data);
            } else {
                response = await apiService.post('/settings/users', data);
            }
            return right(response.data);
        } catch (error: any) {
            if (error.response?.status === 422) {
                return left(new ValidationFailure(error.response.data.message, error.response.data.errors));
            }
            return left(new ServerFailure(error.message));
        }
    }

    async delete(uuid: string): Promise<Either<Failure, void>> {
        try {
            await apiService.delete(`/settings/users/${uuid}`);
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }

    async export(filters: UserFilters): Promise<Either<Failure, void>> {
        try {
            const response = await apiService.get('/settings/users/export', {
                params: filters,
                responseType: 'blob'
            });
            this.downloadFile(response.data, 'users-export.xlsx');
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }

    async import(file: File): Promise<Either<Failure, void>> {
        try {
            const formData = new FormData();
            formData.append('file', file);
            await apiService.post('/settings/users/import', formData, {
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

    async downloadImportTemplate(): Promise<Either<Failure, void>> {
        try {
            const response = await apiService.get('/settings/users/import/template', {
                responseType: 'blob'
            });
            this.downloadFile(response.data, 'user-import-template.xlsx');
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
