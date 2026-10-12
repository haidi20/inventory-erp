import { Either } from '@modules/Core/shared/either';
import { Failure } from '@modules/Core/shared/core/failures';
import { LookupValueRepository } from '@modules/Operational/Master/domain/repositories/lookup_value.repository';

export class ImportLookupValues {
    constructor(private repository: LookupValueRepository) { }

    async execute(setId: string, file: File): Promise<Either<Failure, void>> {
        return await this.repository.import(setId, file);
    }
}
