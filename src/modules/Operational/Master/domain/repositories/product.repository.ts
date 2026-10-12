import type { Either } from '@modules/Core/shared/either';
import type { Failure } from '@modules/Core/shared/core/failures';
import type { ProductFilterOptions, ProductFilters, ProductFormData } from '@modules/Operational/Master/domain/models/product.model';

export interface ProductRepository {
    paginate(filters: ProductFilters): Promise<Either<Failure, any>>;
    filterOptions(): Promise<Either<Failure, ProductFilterOptions>>;
    save(data: ProductFormData): Promise<Either<Failure, any>>;
    delete(uuid: string): Promise<Either<Failure, void>>;
    export(filters: ProductFilters): Promise<Either<Failure, void>>;
}
