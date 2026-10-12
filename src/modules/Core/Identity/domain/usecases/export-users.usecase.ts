import type { UserRepository } from '@modules/Core/Identity/domain/repositories/user.repository';
import type { UserFilters } from '@modules/Core/Identity/domain/models/user.model';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class ExportUsersUseCase {
    constructor(private userRepository: UserRepository) {}

    async execute(filters: UserFilters): Promise<Either<Failure, void>> {
        try {
            return await this.userRepository.export(filters);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
