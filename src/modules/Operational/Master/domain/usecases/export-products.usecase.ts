import type { ProductRepository } from '@modules/Operational/Master/domain/repositories/product.repository';
import type { ProductFilters } from '@modules/Operational/Master/domain/models/product.model';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class ExportProductsUseCase {
    constructor(private productRepository: ProductRepository) {}

    async execute(filters: ProductFilters): Promise<Either<Failure, void>> {
        try {
            return await this.productRepository.export(filters);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
