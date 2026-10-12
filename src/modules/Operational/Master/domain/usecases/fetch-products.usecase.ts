import type { ProductRepository } from '@modules/Operational/Master/domain/repositories/product.repository';
import type { ProductFilters } from '@modules/Operational/Master/domain/models/product.model';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class FetchProductsUseCase {
    constructor(private productRepository: ProductRepository) {}

    async execute(filters: ProductFilters): Promise<Either<Failure, any>> {
        try {
            return await this.productRepository.paginate(filters);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
