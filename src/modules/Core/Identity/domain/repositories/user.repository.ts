import type { UserFormData, UserFilters, UserSearchColumn } from '@modules/Core/Identity/domain/models/user.model';
import type { Either } from '@modules/Core/shared/either';
import type { Failure } from '@modules/Core/shared/core/failures';

export interface UserRepository {
    paginate(filters: UserFilters): Promise<Either<Failure, any>>;
    search(query: string, column?: UserSearchColumn): Promise<Either<Failure, any>>;
    changePerPage(perPage: number): Promise<Either<Failure, any>>;
    changePage(page: number): Promise<Either<Failure, any>>;
    save(data: UserFormData): Promise<Either<Failure, any>>;
    delete(uuid: string): Promise<Either<Failure, void>>;
    export(filters: UserFilters): Promise<Either<Failure, void>>;
    import(file: File): Promise<Either<Failure, void>>;
    downloadImportTemplate(): Promise<Either<Failure, void>>;
}
