import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';
import { type FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import type { FarmacyTransactionHubData } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

const getErrorMessage = (error: unknown): string => error instanceof Error ? error.message : 'Terjadi kesalahan tidak terduga.';

export class GetTransactionHubDataUseCase {
    constructor(private repository: FarmacyRepository) {}
    async execute(): Promise<Either<Failure, FarmacyTransactionHubData>> {
        try {
            return await this.repository.getTransactionHubData();
        } catch (error: unknown) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }
}
