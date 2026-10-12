import type { RoleFormData, RoleFilters } from '@modules/Core/Identity/domain/models/role.model';
import type { Either } from '@modules/Core/shared/either';
import type { Failure } from '@modules/Core/shared/core/failures';

export interface RoleRepository {
    paginate(filters: RoleFilters): Promise<Either<Failure, any>>;
    changePerPage(perPage: number): Promise<Either<Failure, any>>;
    changePage(page: number): Promise<Either<Failure, any>>;
    save(data: RoleFormData): Promise<Either<Failure, any>>;
    delete(uuid: string): Promise<Either<Failure, void>>;
    export(filters: RoleFilters): Promise<Either<Failure, void>>;
    import(file: File): Promise<Either<Failure, void>>;
    downloadImportTemplate(): Promise<Either<Failure, void>>;
}
