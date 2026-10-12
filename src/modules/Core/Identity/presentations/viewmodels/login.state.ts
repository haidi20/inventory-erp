// login.state.ts — state types untuk LoginVM
export interface DemoAccount {
    role: string;
    email: string;
    password: string;
}

export interface LoginFormData {
    email: string;
    password: string;
    remember: boolean;
}

export interface LoginState {
    isLoading: boolean;
    isInfoModalOpen: boolean;
    isRoleModalOpen: boolean;
    roleConfirmed: boolean;
}
