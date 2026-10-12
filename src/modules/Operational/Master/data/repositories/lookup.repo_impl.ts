import apiService from '@modules/Core/shared/core/api_service';
import type { LookupRepository } from '@modules/Operational/Master/domain/repositories/lookup.repository';
import { LookupSearchColumn, type LookupFilters, type LookupFormData } from '@modules/Operational/Master/domain/models/lookup.model';
import { left, right, type Either } from '@modules/Core/shared/either';
import { ServerFailure, ValidationFailure, type Failure } from '@modules/Core/shared/core/failures';

export class LookupRepoImpl implements LookupRepository {
    async paginate(filters: LookupFilters): Promise<Either<Failure, any>> {
        try {
            const response = await apiService.get('/lov/sets', {
                params: filters,
                headers: { 'Cache-Control': 'no-cache' },
            });

            return right(response.data);
        } catch (error: any) {
            if (error.response?.status === 401) {
                return left(new ServerFailure('Sesi API berakhir, silakan muat ulang halaman.'));
            }

            return left(new ServerFailure(error.message));
        }
    }

    async search(query: string, column: LookupSearchColumn = LookupSearchColumn.ALL): Promise<Either<Failure, any>> {
        return this.paginate({ search: query, search_column: column, page: 1 });
    }

    async changePerPage(perPage: number): Promise<Either<Failure, any>> {
        return this.paginate({ per_page: perPage, page: 1 });
    }

    async changePage(page: number): Promise<Either<Failure, any>> {
        return this.paginate({ page });
    }

    async save(data: LookupFormData): Promise<Either<Failure, any>> {
        try {
            const response = data.uuid
                ? await apiService.put(`/lov/sets/${data.uuid}`, data)
                : await apiService.post('/lov/sets', data);

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
            await apiService.delete(`/lov/sets/${uuid}`);
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }

    async export(filters: LookupFilters): Promise<Either<Failure, void>> {
        try {
            const response = await apiService.get('/lov/sets/export', {
                params: filters,
                responseType: 'blob',
            });

            this.downloadFile(response.data, 'lookup-sets-export.xlsx');
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }

    async import(file: File): Promise<Either<Failure, void>> {
        try {
            const formData = new FormData();
            formData.append('file', file);
            await apiService.post('/lov/sets/import', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
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
            const response = await apiService.get('/lov/sets/import/template', {
                responseType: 'blob',
            });

            this.downloadFile(response.data, 'lookup-set-import-template.xlsx');
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
