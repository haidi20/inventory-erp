import { Either } from '@modules/Core/shared/either';
import { Failure } from '@modules/Core/shared/core/failures';
import { LookupValueRepository } from '@modules/Operational/Master/domain/repositories/lookup_value.repository';

export class DownloadLookupValueTemplate {
    constructor(private repository: LookupValueRepository) { }

    async execute(): Promise<Either<Failure, void>> {
        return await this.repository.downloadTemplate();
    }
}
