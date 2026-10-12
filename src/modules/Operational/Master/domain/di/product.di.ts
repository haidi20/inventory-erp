import { ProductRepoImpl } from '@modules/Operational/Master/data/repositories/product.repo_impl';
import { FetchProductsUseCase } from '@modules/Operational/Master/domain/usecases/fetch-products.usecase';
import { FetchProductFilterOptionsUseCase } from '@modules/Operational/Master/domain/usecases/fetch-product-filter-options.usecase';
import { SaveProductUseCase } from '@modules/Operational/Master/domain/usecases/save-product.usecase';
import { DeleteProductUseCase } from '@modules/Operational/Master/domain/usecases/delete-product.usecase';
import { ExportProductsUseCase } from '@modules/Operational/Master/domain/usecases/export-products.usecase';

export interface ProductVMDependencies {
    fetchProductsUseCase: Pick<FetchProductsUseCase, 'execute'>;
    fetchProductFilterOptionsUseCase: Pick<FetchProductFilterOptionsUseCase, 'execute'>;
    saveProductUseCase: Pick<SaveProductUseCase, 'execute'>;
    deleteProductUseCase: Pick<DeleteProductUseCase, 'execute'>;
    exportProductsUseCase: Pick<ExportProductsUseCase, 'execute'>;
}

export function createProductDependencies(repository = new ProductRepoImpl()) {
    return {
        fetchProductsUseCase: new FetchProductsUseCase(repository),
        fetchProductFilterOptionsUseCase: new FetchProductFilterOptionsUseCase(repository),
        saveProductUseCase: new SaveProductUseCase(repository),
        deleteProductUseCase: new DeleteProductUseCase(repository),
        exportProductsUseCase: new ExportProductsUseCase(repository),
    };
}

export const {
    fetchProductsUseCase,
    fetchProductFilterOptionsUseCase,
    saveProductUseCase,
    deleteProductUseCase,
    exportProductsUseCase,
} = createProductDependencies();
