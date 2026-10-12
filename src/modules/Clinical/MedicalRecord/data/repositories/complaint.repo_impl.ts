import { router } from '@inertiajs/vue3';
import { Either, left, right } from '@modules/Core/shared/either';
import { Failure, ServerFailure, ValidationFailure } from '@modules/Core/shared/core/failures';

export interface ComplaintFormData {
    subject: string;
    content: string;
    type: string;
    is_anonymous: boolean;
    [key: string]: any;
}

export class ComplaintRepoImpl {
    async submit(data: ComplaintFormData): Promise<Either<Failure, void>> {
        return new Promise((resolve) => {
            router.post('/operational/complaints', data as any, {
                onSuccess: () => resolve(right(undefined)),
                onError: (errors) => {
                    // Transform Record<string, string> to Record<string, string[]>
                    const transformedErrors: Record<string, string[]> = {};
                    for (const key in errors) {
                        transformedErrors[key] = [errors[key]];
                    }
                    resolve(left(new ValidationFailure('Validasi gagal.', transformedErrors)));
                },
                onFinish: () => {},
            });
        });
    }
}
