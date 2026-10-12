// user.state.ts � state types untuk UserVM
// Model types ada di domain/models/user.model.ts; file ini mendefinisikan state VM level.
export interface UserFilters {
    search?: string;
    search_column?: string;
    role?: string;
    sort_by?: 'name' | 'email';
    sort_direction?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
}

export interface UserSummary {
    total: number;
    active: number;
}

export const USER_SEARCH_COLUMNS = [
    { id: 'all',   text: 'Semua Kolom' },
    { id: 'name',  text: 'Nama' },
    { id: 'email', text: 'Email' },
] as const;

export const USER_PER_PAGE_OPTIONS = [
    { id: 5,   text: '5' },
    { id: 10,  text: '10' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const USER_DEFAULT_FILTERS: Required<UserFilters> = {
    search: '',
    search_column: 'all',
    role: '',
    sort_by: 'name',
    sort_direction: 'asc',
    per_page: 5,
    page: 1,
};

export interface UserPageProps extends Record<string, unknown> {
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
