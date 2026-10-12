import type { UserRepository } from '@modules/Core/Identity/domain/repositories/user.repository';
import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export class DownloadUserImportTemplateUseCase {
    constructor(private userRepository: UserRepository) {}

    async execute(): Promise<Either<Failure, void>> {
        try {
            return await this.userRepository.downloadImportTemplate();
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
