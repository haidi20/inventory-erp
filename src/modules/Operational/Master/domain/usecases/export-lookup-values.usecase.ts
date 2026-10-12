import { Either } from '@modules/Core/shared/either';
import { Failure } from '@modules/Core/shared/core/failures';
import { LookupValueRepository } from '@modules/Operational/Master/domain/repositories/lookup_value.repository';
import { LookupValueFilters } from '@modules/Operational/Master/domain/models/lookup_value.model';

export class ExportLookupValues {
    constructor(private repository: LookupValueRepository) { }

    async execute(setId: string, filters: LookupValueFilters): Promise<Either<Failure, void>> {
        return await this.repository.export(setId, filters);
    }
}
