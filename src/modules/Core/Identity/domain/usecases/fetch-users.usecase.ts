import type { UserRepository } from '@modules/Core/Identity/domain/repositories/user.repository';
import type { UserFilters } from '@modules/Core/Identity/domain/models/user.model';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class FetchUsersUseCase {
    constructor(private userRepository: UserRepository) {}

    async execute(filters: UserFilters): Promise<Either<Failure, any>> {
        try {
            return await this.userRepository.paginate(filters);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
