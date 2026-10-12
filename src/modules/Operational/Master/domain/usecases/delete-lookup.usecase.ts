import type { LookupRepository } from '@modules/Operational/Master/domain/repositories/lookup.repository';
import { left, type Either } from '@modules/Core/shared/either';
import { ServerFailure, type Failure } from '@modules/Core/shared/core/failures';

export class DeleteLookupUseCase {
    constructor(private readonly lookupRepository: LookupRepository) { }

    async execute(uuid: string): Promise<Either<Failure, void>> {
        try {
            return await this.lookupRepository.delete(uuid);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
