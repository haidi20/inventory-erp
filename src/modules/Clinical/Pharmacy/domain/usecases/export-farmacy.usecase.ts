import type { FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import type { FarmacyFilters } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

const getErrorMessage = (error: unknown): string => error instanceof Error ? error.message : 'Terjadi kesalahan tidak terduga.';

export class exportFarmacyUseCase {
    constructor(private FarmacyRepository: FarmacyRepository) {}

    async execute(filters: FarmacyFilters): Promise<Either<Failure, void>> {
        try {
            return await this.FarmacyRepository.export(filters);
        } catch (error: unknown) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }
}



