import { type Either } from '@modules/Core/shared/either';
import { type Failure } from '@modules/Core/shared/core/failures';
import type { FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import type { FarmacyRecipeOptions } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export class GetRecipeOptionsUseCase {
    constructor(private repository: FarmacyRepository) {}

    async execute(): Promise<Either<Failure, FarmacyRecipeOptions>> {
        return await this.repository.getRecipeOptions();
    }
}
