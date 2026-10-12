import { Either } from '@modules/Core/shared/either';
import { Failure } from '@modules/Core/shared/core/failures';
import { LookupValueRepository } from '@modules/Operational/Master/domain/repositories/lookup_value.repository';
import { LookupValueFilters } from '@modules/Operational/Master/domain/models/lookup_value.model';

export class FetchLookupValues {
    constructor(private repository: LookupValueRepository) { }

    async execute(setId: string, filters: LookupValueFilters): Promise<Either<Failure, any>> {
        return await this.repository.paginate(setId, filters);
    }
}
