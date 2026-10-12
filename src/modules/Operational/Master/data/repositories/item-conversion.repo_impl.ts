import apiService from '@modules/Core/shared/core/api_service';
import { left, right, type Either } from '@modules/Core/shared/either';
import { ServerFailure, ValidationFailure, type Failure } from '@modules/Core/shared/core/failures';
import type { ItemConversionRepository } from '@modules/Operational/Master/domain/repositories/item-conversion.repository';
import type { ItemConversion, ItemConversionFilters, ItemConversionSummary } from '@modules/Operational/Master/domain/models/item-conversion.model';

export class ItemConversionRepoImpl implements ItemConversionRepository {
    async fetch(filters: ItemConversionFilters): Promise<Either<Failure, ItemConversionSummary>> {
        try {
            const response = await apiService.get('/masters/item-conversions', { params: filters });
            return right({
                items: response.data.data || response.data.items || [],
                total: response.data.meta?.total ?? response.data.total ?? 0,
                current_page: response.data.meta?.current_page ?? response.data.current_page ?? 1,
                per_page: response.data.meta?.per_page ?? response.data.per_page ?? 5,
            });
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }

    async save(data: Partial<ItemConversion>): Promise<Either<Failure, ItemConversion>> {
        try {
            const response = await apiService.post('/masters/item-conversions', data);
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
            await apiService.delete(`/masters/item-conversions/${uuid}`);
            return right(undefined);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
