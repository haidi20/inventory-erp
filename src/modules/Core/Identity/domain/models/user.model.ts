export enum UserSearchColumn {
    ALL = 'all',
    NAME = 'name',
    EMAIL = 'email',
    ROLE = 'role_name'
}

export interface UserRoleItem {
    uuid: string;
    name: string;
    label: string;
}

export interface UserModel {
    uuid: string;
    name: string;
    email: string;
    role_uuid: string;
    role_name: string;
    role_label: string;
    role_uuids: string[];
    roles: UserRoleItem[];
    employee_uuid?: string;
    employee_name?: string;
    created_at_label: string;
    avatar_initial: string;
    avatar_color: string;
    is_current_user: boolean;
}

export interface UserFormData {
    uuid?: string;
    name: string;
    email: string;
    password?: string;
    role_uuids: string[];
    employee_uuid?: string;
}

export interface UserFilters {
    search?: string;
    search_column?: string;
    role?: string;
    sort_by?: 'name' | 'email';
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export interface UserSummary {
    total: number;
    dokter: number;
    other: number;
}
