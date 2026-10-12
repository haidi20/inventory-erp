import apiService from '@modules/Core/shared/core/api_service';
import type { ProductRepository } from '@modules/Operational/Master/domain/repositories/product.repository';
import type { ProductFilterOptions, ProductFilters, ProductFormData } from '@modules/Operational/Master/domain/models/product.model';
import { type Either, left, right } from '@modules/Core/shared/either';
import { type Failure, ServerFailure, ValidationFailure } from '@modules/Core/shared/core/failures';

export class ProductRepoImpl implements ProductRepository {
    async paginate(filters: ProductFilters): Promise<Either<Failure, any>> {
        try {
            const response = await apiService.get('/products', { params: filters });
            return right(response.data);
        } catch (error: any) {
            if (error.response?.status === 401) {
                return left(new ServerFailure('Sesi API berakhir, silakan muat ulang halaman.'));
            }
            return left(new ServerFailure(error.response?.data?.message || error.message));
        }
    }

    async filterOptions(): Promise<Either<Failure, ProductFilterOptions>> {
        try {
            const response = await apiService.get('/products/filter-options');
            return right(response.data?.data ?? response.data);
        } catch (error: any) {
            return left(new ServerFailure(error.response?.data?.message || error.message));
        }
    }

    async save(data: ProductFormData): Promise<Either<Failure, any>> {
        try {
            let payload: ProductFormData | FormData = data;

            if (data.image instanceof File || data.images?.length) {
                const form = new FormData();
                Object.entries(data).forEach(([key, value]) => {
                    if (key === 'image') {
                        if (value instanceof File) {
                            form.append('image', value);
                        }
                        return;
                    }
                    if (key === 'images') {
                        (value as File[]).forEach((file) => {
                            form.append('images[]', file);
                        });
                        return;
                    }
                    if (key === 'existing_image_paths') {
                        (value as string[]).forEach((path) => {
                            form.append('existing_image_paths[]', path);
                        });
                        return;
                    }
                    if (key === 'units' || key === 'opening_balances' || key === 'prices' || key === 'discounts' || key === 'accounts') {
                        form.append(key, JSON.stringify(value ?? []));
                        return;
                    }
                    if (value !== undefined && value !== null) {
                        form.append(key, String(value));
                    }
                });
                if (data.uuid) {
                    form.append('_method', 'PUT');
                }
                payload = form;
            }

            const response = data.uuid && !(payload instanceof FormData)
                ? await apiService.put(`/products/${data.uuid}`, payload)
                : await apiService.post('/products', payload);
            return right(response.data);
        } catch (error: any) {
            if (error.response?.status === 422) {
                return left(new ValidationFailure(error.response.data.message, error.response.data.errors));
            }
            return left(new ServerFailure(error.response?.data?.message || error.message));
        }
    }

    async delete(uuid: string): Promise<Either<Failure, void>> {
        try {
            await apiService.delete(`/products/${uuid}`);
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.response?.data?.message || error.message));
        }
    }

    async export(filters: ProductFilters): Promise<Either<Failure, void>> {
        try {
            const response = await apiService.get('/products/export', {
                params: filters,
                responseType: 'blob',
            });
            this.downloadFile(response.data, 'products-export.csv');
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.response?.data?.message || error.message));
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
