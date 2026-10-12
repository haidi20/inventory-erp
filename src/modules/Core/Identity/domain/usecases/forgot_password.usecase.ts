/**
 * ForgotPasswordUseCase
 *
 * Encapsulates forgot-password business logic.
 * Submission is handled via Inertia router (POST /forgot-password),
 * so this usecase serves as a boundary layer for future extension
 * (e.g. rate-limiting UI, pre-validation, analytics).
 */
export class ForgotPasswordUseCase {
    /**
     * Validate that the email field is not empty before submitting.
     * Returns null on success, or an error message string.
     */
    validate(email: string): string | null {
        if (!email || !email.trim()) {
            return 'Email wajib diisi.';
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.trim())) {
            return 'Format email tidak valid.';
        }
        return null;
    }
}
