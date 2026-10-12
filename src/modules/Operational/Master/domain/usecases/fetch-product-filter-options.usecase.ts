import type { ProductRepository } from '@modules/Operational/Master/domain/repositories/product.repository';
import type { ProductFilterOptions } from '@modules/Operational/Master/domain/models/product.model';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class FetchProductFilterOptionsUseCase {
    constructor(private productRepository: ProductRepository) {}

    async execute(): Promise<Either<Failure, ProductFilterOptions>> {
        try {
            return await this.productRepository.filterOptions();
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
