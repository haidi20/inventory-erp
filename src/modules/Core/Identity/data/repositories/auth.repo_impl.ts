import { Either, left, right } from '@modules/Core/shared/either';
import { Failure, ServerFailure } from '@modules/Core/shared/core/failures';

export interface LoginFormData {
    email: string;
    password: string;
    remember: boolean;
}

export interface RoleOption {
    name: string;
    label: string;
}

export interface PreCheckResult {
    success: boolean;
    roles: RoleOption[];
}

export class AuthRepoImpl {
    async preCheckRoles(
        email: string,
        password: string,
        csrfToken: string
    ): Promise<Either<Failure, PreCheckResult>> {
        try {
            const response = await fetch('/login/pre-check', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ email, password }),
            });

            if (!response.ok) {
                return left(new ServerFailure('Kredensial tidak valid.'));
            }

            const data: PreCheckResult = await response.json();
            return right(data);
        } catch {
            return left(new ServerFailure('Gagal terhubung ke server.'));
        }
    }
}
