import { left, type Either } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';
import type { FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import type { FarmacyStockMutationRow } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

const getErrorMessage = (error: unknown): string => error instanceof Error ? error.message : 'Gagal mengambil data stok mutasi.';

export class GetStockMutationsUseCase {
    constructor(private repository: FarmacyRepository) {}

    async execute(): Promise<Either<Failure, FarmacyStockMutationRow[]>> {
        try {
            return await this.repository.getStockMutations();
        } catch (error: unknown) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }
}
