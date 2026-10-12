// forgot_password.state.ts — state types untuk ForgotPasswordVM
export interface ForgotPasswordFormData {
    email: string;
}

export interface ForgotPasswordState {
    clientError: string | null;
}
