import { Either } from '@modules/Core/shared/either';
import { Failure } from '@modules/Core/shared/core/failures';
import { AuthRepoImpl, PreCheckResult } from '@modules/Core/Identity/data/repositories/auth.repo_impl';

export class PreCheckRolesUseCase {
    constructor(private repository: AuthRepoImpl = new AuthRepoImpl()) {}

    async execute(
        email: string,
        password: string,
        csrfToken: string
    ): Promise<Either<Failure, PreCheckResult>> {
        return await this.repository.preCheckRoles(email, password, csrfToken);
    }
}
