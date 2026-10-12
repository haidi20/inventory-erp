import type { UserRepository } from '@modules/Core/Identity/domain/repositories/user.repository';
import type { UserFormData } from '@modules/Core/Identity/domain/models/user.model';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class SaveUserUseCase {
    constructor(private userRepository: UserRepository) {}

    async execute(data: UserFormData): Promise<Either<Failure, any>> {
        try {
            return await this.userRepository.save(data);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
