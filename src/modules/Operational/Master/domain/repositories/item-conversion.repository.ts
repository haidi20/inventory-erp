import { type Either } from '@modules/Core/shared/either';
import { type Failure } from '@modules/Core/shared/core/failures';
import type { ItemConversion, ItemConversionFilters, ItemConversionSummary } from '@modules/Operational/Master/domain/models/item-conversion.model';

export interface ItemConversionRepository {
    fetch(filters: ItemConversionFilters): Promise<Either<Failure, ItemConversionSummary>>;
    save(data: Partial<ItemConversion>): Promise<Either<Failure, ItemConversion>>;
    delete(uuid: string): Promise<Either<Failure, void>>;
}
