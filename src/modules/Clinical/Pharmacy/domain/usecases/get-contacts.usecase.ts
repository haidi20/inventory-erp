import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';
import { type FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import { type ManagementContactModel } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

const getErrorMessage = (error: unknown): string => error instanceof Error ? error.message : 'Terjadi kesalahan tidak terduga.';

export class GetContactsUseCase {
    constructor(private repository: FarmacyRepository) {}
    async execute(): Promise<Either<Failure, ManagementContactModel[]>> {
        try {
            return await this.repository.getContacts();
        } catch (error: unknown) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }
}
