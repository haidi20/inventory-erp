import { Either } from '@modules/Core/shared/either';
import { Failure } from '@modules/Core/shared/core/failures';
import { LookupValueRepository } from '@modules/Operational/Master/domain/repositories/lookup_value.repository';

export class DeleteLookupValue {
    constructor(private repository: LookupValueRepository) { }

    async execute(uuid: string): Promise<Either<Failure, void>> {
        return await this.repository.delete(uuid);
    }
}
