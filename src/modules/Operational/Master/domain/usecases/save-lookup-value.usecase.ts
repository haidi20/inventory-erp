import { Either } from '@modules/Core/shared/either';
import { Failure } from '@modules/Core/shared/core/failures';
import { LookupValueRepository } from '@modules/Operational/Master/domain/repositories/lookup_value.repository';
import { LookupValueModel, LookupValueFormData } from '@modules/Operational/Master/domain/models/lookup_value.model';

export class SaveLookupValue {
    constructor(private repository: LookupValueRepository) { }

    async execute(setId: string, data: LookupValueFormData): Promise<Either<Failure, LookupValueModel>> {
        return await this.repository.save(setId, data);
    }
}
