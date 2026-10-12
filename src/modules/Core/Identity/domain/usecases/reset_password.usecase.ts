/**
 * ResetPasswordUseCase
 *
 * Encapsulates reset-password business logic.
 * Submission is handled via Inertia router (POST /reset-password),
 * so this usecase provides client-side pre-validation only.
 */
export class ResetPasswordUseCase {
    /**
     * Validate the reset password form fields.
     * Returns null on success, or an error message string.
     */
    validate(password: string, passwordConfirmation: string): string | null {
        if (!password || password.length < 8) {
            return 'Password minimal 8 karakter.';
        }
        if (password !== passwordConfirmation) {
            return 'Konfirmasi password tidak cocok.';
        }
        return null;
    }
}
