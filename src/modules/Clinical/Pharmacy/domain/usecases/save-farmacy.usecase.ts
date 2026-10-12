import { type Either } from '@modules/Core/shared/either';
import { type Failure } from '@modules/Core/shared/core/failures';
import { type FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import type { FarmacyFormData, FarmacyMutationResult } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export class saveFarmacyUseCase {
    constructor(private repository: FarmacyRepository) {}

    async execute(data: FarmacyFormData): Promise<Either<Failure, FarmacyMutationResult>> {
        return await this.repository.save(data);
    }
}
