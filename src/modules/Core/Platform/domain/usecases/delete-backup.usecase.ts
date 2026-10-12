import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

type DeleteBackupRepository = {
    delete(filename: string, historyType: string): Promise<Either<Failure, void>>;
};

export class DeleteBackupUseCase {
    constructor(private repository: DeleteBackupRepository) {}

    async execute(filename: string, historyType: string): Promise<Either<Failure, void>> {
        try {
            return await this.repository.delete(filename, historyType);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
