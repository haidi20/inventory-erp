export interface RoleModel {
    uuid: string;
    name: string;
    label: string;
    users_count: number;
    permissions_count: number;
    created_at_label: string;
}

export interface PermissionModel {
    uuid: string;
    name: string;
    label: string;
}

export interface RoleFormData {
    uuid?: string;
    name: string;
    label: string;
    permissions: string[];
}

export interface RoleFilters {
    search?: string;
    search_column?: 'all' | 'name' | 'label';
    sort_by?: 'name' | 'label';
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}
