import { left, type Either } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';
import type { FarmacyRepository } from '@modules/Clinical/Pharmacy/domain/repositories/farmacy.repository';
import type { FarmacyStockAdjustmentRow } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

const getErrorMessage = (error: unknown): string => error instanceof Error ? error.message : 'Gagal mengambil data penyesuaian persediaan.';

export class GetStockAdjustmentsUseCase {
    constructor(private repository: FarmacyRepository) {}

    async execute(): Promise<Either<Failure, FarmacyStockAdjustmentRow[]>> {
        try {
            return await this.repository.getStockAdjustments();
        } catch (error: unknown) {
            return left(new ServerFailure(getErrorMessage(error)));
        }
    }
}
