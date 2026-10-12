import { type Either, left } from '@modules/Core/shared/either';
import { type Failure, ServerFailure } from '@modules/Core/shared/core/failures';

type ImportBackupsRepository = {
    import(file: File): Promise<Either<Failure, void>>;
};

export class ImportBackupsUseCase {
    constructor(private repository: ImportBackupsRepository) {}

    async execute(file: File): Promise<Either<Failure, void>> {
        try {
            return await this.repository.import(file);
        } catch (error: any) {
            return left(new ServerFailure(error.message));
        }
    }
}
