import { type Either } from '@modules/Core/shared/either';
import { type Failure } from '@modules/Core/shared/core/failures';
import type { ItemConversionRepository } from '@modules/Operational/Master/domain/repositories/item-conversion.repository';

export class DeleteItemConversionUseCase {
    constructor(private repository: ItemConversionRepository) {}

    async execute(uuid: string): Promise<Either<Failure, void>> {
        return await this.repository.delete(uuid);
    }
}
