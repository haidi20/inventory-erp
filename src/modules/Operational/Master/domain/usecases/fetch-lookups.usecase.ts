import type { LookupRepository } from '@modules/Operational/Master/domain/repositories/lookup.repository';
import type { LookupFilters } from '@modules/Operational/Master/domain/models/lookup.model';
import { left, type Either } from '@modules/Core/shared/either';
import { ServerFailure, type Failure } from '@modules/Core/shared/core/failures';

export class FetchLookupsUseCase {
    constructor(private readonly lookupRepository: LookupRepository) { }

    async execute(filters: LookupFilters): Promise<Either<Failure, any>> {
        try {
            return await this.lookupRepository.paginate(filters);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
