import type { ProductRepository } from '@modules/Operational/Master/domain/repositories/product.repository';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class DeleteProductUseCase {
    constructor(private productRepository: ProductRepository) {}

    async execute(uuid: string): Promise<Either<Failure, void>> {
        try {
            return await this.productRepository.delete(uuid);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
