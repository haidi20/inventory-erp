import type { UnitRepository } from '@modules/Operational/Master/domain/repositories/unit.repository';
import type { UnitFilters } from '@modules/Operational/Master/domain/models/unit.model';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class FetchUnitsUseCase {
    constructor(private unitRepository: UnitRepository) { }

    async execute(filters: UnitFilters): Promise<Either<Failure, any>> {
        try {
            return await this.unitRepository.paginate(filters);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
