// reset_password.state.ts — state types untuk ResetPasswordVM
export interface ResetPasswordFormData {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
}

export interface ResetPasswordState {
    clientError: string | null;
    showPassword: boolean;
    showConfirmPassword: boolean;
}
