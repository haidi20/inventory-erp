// role.state.ts � state types untuk RoleVM
// Model types ada di domain/models/role.model.ts; file ini mendefinisikan state VM level.
import { ref } from 'vue';

export interface RoleFilters {
    search?: string;
    search_column?: 'all' | 'name' | 'label';
    sort_by?: 'name' | 'label';
    sort_direction?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
}

export interface RoleSummary {
    total: number;
}

export const ROLE_SEARCH_COLUMNS = [
    { id: 'all',   text: 'Semua Kolom' },
    { id: 'name',  text: 'Nama' },
    { id: 'label', text: 'Label' },
] as const;

export const ROLE_PER_PAGE_OPTIONS = [
    { id: 5,   text: '5' },
    { id: 10,  text: '10' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const ROLE_DEFAULT_FILTERS: Required<RoleFilters> = {
    search: '',
    search_column: 'all',
    sort_by: 'name',
    sort_direction: 'asc',
    per_page: 5,
    page: 1,
};

export interface RolePageProps extends Record<string, unknown> {
    auth?: {
        api_token?: string;
        user?: {
            uuid?: string;
            name?: string;
            email?: string;
            roles?: string[];
            permissions?: string[];
        } | null;
    };
    flash?: {
        success?: string | null;
        error?: string | null;
    };
    errors?: Record<string, string | undefined>;
}

export function createRoleFormUIState() {
    const searchInputRef = ref<HTMLInputElement | null>(null);

    return { searchInputRef };
}
