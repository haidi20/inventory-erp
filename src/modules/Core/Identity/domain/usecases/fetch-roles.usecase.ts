import type { RoleRepository } from '@modules/Core/Identity/domain/repositories/role.repository';
import type { RoleFilters, RoleModel } from '@modules/Core/Identity/domain/models/role.model';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class FetchRolesUseCase {
    constructor(private RoleRepository: RoleRepository) {}

    async execute(filters: RoleFilters): Promise<Either<Failure, any>> {
        try {
            return await this.RoleRepository.paginate(filters);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}


