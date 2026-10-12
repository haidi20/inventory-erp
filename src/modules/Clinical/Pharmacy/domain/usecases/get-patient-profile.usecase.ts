import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';
import { type FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import { type PatientProfile } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

const getErrorMessage = (error: unknown): string => error instanceof Error ? error.message : 'Terjadi kesalahan tidak terduga.';

export class GetPatientProfileUseCase {
    constructor(private repository: FarmacyRepository) {}
    async execute(uuid: string): Promise<Either<Failure, PatientProfile>> {
        try {
            return await this.repository.getPatientProfile(uuid);
        } catch (error: unknown) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }
}
