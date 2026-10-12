import { ItemConversionRepoImpl } from '@modules/Operational/Master/data/repositories/item-conversion.repo_impl';
import { FetchItemConversionsUseCase } from '@modules/Operational/Master/domain/usecases/fetch-item-conversions.usecase';
import { SaveItemConversionUseCase } from '@modules/Operational/Master/domain/usecases/save-item-conversion.usecase';
import { DeleteItemConversionUseCase } from '@modules/Operational/Master/domain/usecases/delete-item-conversion.usecase';

const itemConversionRepo = new ItemConversionRepoImpl();

export const fetchItemConversionsUseCase = new FetchItemConversionsUseCase(itemConversionRepo);
export const saveItemConversionUseCase = new SaveItemConversionUseCase(itemConversionRepo);
export const deleteItemConversionUseCase = new DeleteItemConversionUseCase(itemConversionRepo);
