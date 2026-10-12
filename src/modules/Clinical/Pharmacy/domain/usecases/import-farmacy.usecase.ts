import { type Either } from '@modules/Core/shared/either';
import { type Failure } from '@modules/Core/shared/core/failures';
import { type FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import type { FarmacyImportResult } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export class importFarmacyUseCase {
    constructor(private repository: FarmacyRepository) {}

    async execute(file: File): Promise<Either<Failure, FarmacyImportResult>> {
        return await this.repository.import(file);
    }
}
