import { type Either } from '@modules/Core/shared/either';
import { type Failure } from '@modules/Core/shared/core/failures';
import type { FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import type { FarmacyStockAdjustmentOptions } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export class GetAdjustmentOptionsUseCase {
    constructor(private repository: FarmacyRepository) {}

    async execute(): Promise<Either<Failure, FarmacyStockAdjustmentOptions>> {
        return await this.repository.getStockAdjustmentOptions();
    }
}
