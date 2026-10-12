import type { RoleRepository } from '@modules/Core/Identity/domain/repositories/role.repository';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class DownloadRoleImportTemplateUseCase {
    constructor(private roleRepository: RoleRepository) {}

    async execute(): Promise<Either<Failure, void>> {
        try {
            return await this.roleRepository.downloadImportTemplate();
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}


