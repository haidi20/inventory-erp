import { type Either } from '@modules/Core/shared/either';
import { type Failure } from '@modules/Core/shared/core/failures';
import type { ItemConversionRepository } from '@modules/Operational/Master/domain/repositories/item-conversion.repository';
import type { ItemConversion } from '@modules/Operational/Master/domain/models/item-conversion.model';

export class SaveItemConversionUseCase {
    constructor(private repository: ItemConversionRepository) {}

    async execute(data: Partial<ItemConversion>): Promise<Either<Failure, ItemConversion>> {
        return await this.repository.save(data);
    }
}
