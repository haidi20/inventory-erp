import type { UnitFormData, UnitFilters, UnitSearchColumn } from '@modules/Operational/Master/domain/models/unit.model';
import type { Either } from '@modules/Core/shared/either';
import type { Failure } from '@modules/Core/shared/core/failures';

export interface UnitRepository {
    paginate(filters: UnitFilters): Promise<Either<Failure, any>>;
    search(query: string, column?: UnitSearchColumn): Promise<Either<Failure, any>>;
    changePerPage(perPage: number): Promise<Either<Failure, any>>;
    changePage(page: number): Promise<Either<Failure, any>>;
    save(data: UnitFormData): Promise<Either<Failure, any>>;
    delete(uuid: string): Promise<Either<Failure, void>>;
    export(filters: UnitFilters): Promise<Either<Failure, void>>;
    import(file: File): Promise<Either<Failure, void>>;
    downloadImportTemplate(): Promise<Either<Failure, void>>;
}
