import { Either } from '@modules/Core/shared/either';
import { Failure } from '@modules/Core/shared/core/failures';
import { LookupValueModel, LookupValueFilters, LookupValueFormData } from '@modules/Operational/Master/domain/models/lookup_value.model';

export interface LookupValueRepository {
    paginate(setId: string, filters: LookupValueFilters): Promise<Either<Failure, any>>;
    save(setId: string, data: LookupValueFormData): Promise<Either<Failure, LookupValueModel>>;
    delete(uuid: string): Promise<Either<Failure, void>>;
    export(setId: string, filters: LookupValueFilters): Promise<Either<Failure, void>>;
    import(setId: string, file: File): Promise<Either<Failure, void>>;
    downloadTemplate(): Promise<Either<Failure, void>>;
}
