import { type Either } from '@modules/Core/shared/either';
import { type Failure } from '@modules/Core/shared/core/failures';
import type { ItemConversionRepository } from '@modules/Operational/Master/domain/repositories/item-conversion.repository';
import type { ItemConversionFilters, ItemConversionSummary } from '@modules/Operational/Master/domain/models/item-conversion.model';

export class FetchItemConversionsUseCase {
    constructor(private repository: ItemConversionRepository) {}

    async execute(filters: ItemConversionFilters): Promise<Either<Failure, ItemConversionSummary>> {
        return await this.repository.fetch(filters);
    }
}
