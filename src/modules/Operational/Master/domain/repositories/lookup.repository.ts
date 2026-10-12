import type { LookupFilters, LookupFormData, LookupSearchColumn } from '@modules/Operational/Master/domain/models/lookup.model';
import type { Either } from '@modules/Core/shared/either';
import type { Failure } from '@modules/Core/shared/core/failures';

export interface LookupRepository {
    paginate(filters: LookupFilters): Promise<Either<Failure, any>>;
    search(query: string, column?: LookupSearchColumn): Promise<Either<Failure, any>>;
    changePerPage(perPage: number): Promise<Either<Failure, any>>;
    changePage(page: number): Promise<Either<Failure, any>>;
    save(data: LookupFormData): Promise<Either<Failure, any>>;
    delete(uuid: string): Promise<Either<Failure, void>>;
    export(filters: LookupFilters): Promise<Either<Failure, void>>;
    import(file: File): Promise<Either<Failure, void>>;
    downloadImportTemplate(): Promise<Either<Failure, void>>;
}
