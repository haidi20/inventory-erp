import type { LookupRepository } from '@modules/Operational/Master/domain/repositories/lookup.repository';
import { left, type Either } from '@modules/Core/shared/either';
import { ServerFailure, type Failure } from '@modules/Core/shared/core/failures';

export class ImportLookupsUseCase {
    constructor(private readonly lookupRepository: LookupRepository) { }

    async execute(file: File): Promise<Either<Failure, void>> {
        try {
            return await this.lookupRepository.import(file);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
