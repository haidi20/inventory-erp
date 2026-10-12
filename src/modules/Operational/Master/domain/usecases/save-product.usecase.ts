import type { ProductRepository } from '@modules/Operational/Master/domain/repositories/product.repository';
import type { ProductFormData } from '@modules/Operational/Master/domain/models/product.model';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class SaveProductUseCase {
    constructor(private productRepository: ProductRepository) {}

    async execute(data: ProductFormData): Promise<Either<Failure, any>> {
        try {
            return await this.productRepository.save(data);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
