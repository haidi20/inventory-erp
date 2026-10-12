import { left, type Either } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';
import type { FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import type { FarmacyStockOpnameRow } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

const getErrorMessage = (error: unknown): string => error instanceof Error ? error.message : 'Gagal mengambil data stok opname.';

export class GetStockOpnamesUseCase {
    constructor(private repository: FarmacyRepository) {}

    async execute(): Promise<Either<Failure, FarmacyStockOpnameRow[]>> {
        try {
            return await this.repository.getStockOpnames();
        } catch (error: unknown) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }
}
