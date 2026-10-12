import { describe, it, expect, vi } from 'vitest';
import { ComplaintRepoImpl } from '@modules/Clinical/MedicalRecord/data/repositories/complaint.repo_impl';
import { router } from '@inertiajs/vue3';
import { ValidationFailure } from '@modules/Core/shared/core/failures';

vi.mock('@inertiajs/vue3', () => ({
    router: {
        post: vi.fn(),
    }
}));

describe('Complaint Repository Implementation', () => {
    it('should submit a complaint successfully', async () => {
        const repo = new ComplaintRepoImpl();
        const mockData = {
            subject: 'Test Subject',
            content: 'Test Content',
            type: 'pengaduan',
            is_anonymous: false
        };

        (router.post as any).mockImplementation((url: string, data: any, options: any) => {
            options.onSuccess();
        });

        const result = await repo.submit(mockData);

        expect(result.isRight()).toBe(true);
        expect(router.post).toHaveBeenCalledWith('/operational/complaints', expect.anything(), expect.anything());
    });

    it('should handle validation errors', async () => {
        const repo = new ComplaintRepoImpl();
        const mockErrors = { subject: 'Required' };

        (router.post as any).mockImplementation((url: string, data: any, options: any) => {
            options.onError(mockErrors);
        });

        const result = await repo.submit({} as any);

        expect(result.isLeft()).toBe(true);
        result.fold(
            (failure) => {
                expect(failure).toBeInstanceOf(ValidationFailure);
                expect((failure as ValidationFailure).errors.subject).toContain('Required');
            },
            () => {}
        );
    });
});
