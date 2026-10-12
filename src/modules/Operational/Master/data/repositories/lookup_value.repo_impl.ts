import { Either, right, left } from '@modules/Core/shared/either';
import { Failure, ServerFailure } from '@modules/Core/shared/core/failures';
import apiService from '@modules/Core/shared/core/api_service';
import { LookupValueRepository } from '@modules/Operational/Master/domain/repositories/lookup_value.repository';
import { LookupValueModel, LookupValueFilters, LookupValueFormData } from '@modules/Operational/Master/domain/models/lookup_value.model';

export class LookupValueRepoImpl implements LookupValueRepository {
    async paginate(setId: string, filters: LookupValueFilters): Promise<Either<Failure, any>> {
        try {
            const response = await apiService.get(`/lov/sets/${setId}/values`, {
                params: filters
            });
            return right(response.data);
        } catch (error: any) {
            if (error.response?.status === 401) {
                return left(new ServerFailure('Sesi API berakhir. Silakan login kembali.'));
            }
            return left(new ServerFailure(error.response?.data?.message || 'Gagal mengambil data nilai lookup'));
        }
    }

    async save(setId: string, data: LookupValueFormData): Promise<Either<Failure, LookupValueModel>> {
        try {
            let response;
            if (data.uuid) {
                response = await apiService.put(`/lov/sets/${setId}/values/${data.uuid}`, data);
            } else {
                response = await apiService.post(`/lov/sets/${setId}/values`, data);
            }
            return right(response.data);
        } catch (error: any) {
            return left(new ServerFailure(error.response?.data?.message || 'Gagal menyimpan data nilai lookup'));
        }
    }

    async delete(uuid: string): Promise<Either<Failure, void>> {
        try {
            await apiService.delete(`/lov/sets/values/${uuid}`);
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.response?.data?.message || 'Gagal menghapus data nilai lookup'));
        }
    }

    async export(setId: string, filters: LookupValueFilters): Promise<Either<Failure, void>> {
        // Implementation for exporting values (not yet in API routes, but preparing based on LookupSet patterns)
        try {
            const response = await apiService.get(`/lov/sets/${setId}/values/export`, {
                params: filters,
                responseType: 'blob'
            });
            this.downloadFile(response.data, `lookup-values-${setId}.xlsx`);
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure('Gagal mengekspor data'));
        }
    }

    async import(setId: string, file: File): Promise<Either<Failure, void>> {
        try {
            const formData = new FormData();
            formData.append('file', file);
            await apiService.post(`/lov/sets/${setId}/values/import`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.response?.data?.message || 'Gagal mengimpor data'));
        }
    }

    async downloadTemplate(): Promise<Either<Failure, void>> {
        try {
            const response = await apiService.get('/lov/sets/import/template-values', {
                responseType: 'blob'
            });
            this.downloadFile(response.data, 'template-import-lookup-values.xlsx');
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure('Gagal mengunduh template'));
        }
    }

    private downloadFile(data: Blob, filename: string) {
        const url = window.URL.createObjectURL(new Blob([data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
    }
}
