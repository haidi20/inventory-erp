import apiService from '@modules/Core/shared/core/api_service';
import type { RoleRepository } from '@modules/Core/Identity/domain/repositories/role.repository';
import type { RoleFormData, RoleFilters } from '@modules/Core/Identity/domain/models/role.model';
import { type Either, left, right } from '@modules/Core/shared/either';
import { type Failure, ServerFailure, ValidationFailure } from '@modules/Core/shared/core/failures';

export class RoleRepoImpl implements RoleRepository {
    async paginate(filters: RoleFilters): Promise<Either<Failure, any>> {
        try {
            const response = await apiService.get('/settings/roles', { 
                params: filters,
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

    async changePerPage(perPage: number): Promise<Either<Failure, any>> {
        return this.paginate({ per_page: perPage, page: 1 });
    }

    async changePage(page: number): Promise<Either<Failure, any>> {
        return this.paginate({ page });
    }

    async save(data: RoleFormData): Promise<Either<Failure, any>> {
        try {
            let response;
            if (data.uuid) {
                response = await apiService.put(`/settings/roles/${data.uuid}`, data);
            } else {
                response = await apiService.post('/settings/roles', data);
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
            await apiService.delete(`/settings/roles/${uuid}`);
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }

    async export(filters: RoleFilters): Promise<Either<Failure, void>> {
        try {
            const response = await apiService.get('/settings/roles/export', {
                params: filters,
                responseType: 'blob'
            });
            this.downloadFile(response.data, 'roles.xlsx');
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }

    async import(file: File): Promise<Either<Failure, void>> {
        try {
            const formData = new FormData();
            formData.append('file', file);
            await apiService.post('/settings/roles/import', formData, {
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
            const response = await apiService.get('/settings/roles/import/template', {
                responseType: 'blob'
            });
            this.downloadFile(response.data, 'role-import-template.xlsx');
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
