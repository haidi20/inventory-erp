import type { UserRepository } from '@modules/Core/Identity/domain/repositories/user.repository';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class DeleteUserUseCase {
    constructor(private userRepository: UserRepository) {}

    async execute(uuid: string): Promise<Either<Failure, void>> {
        try {
            return await this.userRepository.delete(uuid);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
