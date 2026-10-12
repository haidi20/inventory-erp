import type { LookupRepository } from '@modules/Operational/Master/domain/repositories/lookup.repository';
import type { LookupFormData } from '@modules/Operational/Master/domain/models/lookup.model';
import { left, type Either } from '@modules/Core/shared/either';
import { ServerFailure, type Failure } from '@modules/Core/shared/core/failures';

export class SaveLookupUseCase {
    constructor(private readonly lookupRepository: LookupRepository) { }

    async execute(data: LookupFormData): Promise<Either<Failure, any>> {
        try {
            return await this.lookupRepository.save(data);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
