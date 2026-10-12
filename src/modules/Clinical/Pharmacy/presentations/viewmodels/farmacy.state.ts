import type { FarmacyFilters, FarmacyFormData } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export type { FarmacyFilters, FarmacyFormData };

export interface FarmacyAuthPageProps {
    auth?: {
        api_token?: string;
        user?: {
            permissions?: string[];
        };
    };
    flash?: {
        success?: string | null;
        error?: string | null;
    };
    errors?: Record<string, string | undefined>;
}

export type FarmacySortableColumn = NonNullable<FarmacyFilters['sort_by']>;
export type FarmacyFormField = keyof FarmacyFormData;
// farmacy.state.ts � state types untuk FarmacyVM (CRUD farmasi/user)
export interface FarmacyMeta {
    total: number;
    current_page: number;
    per_page: number;
    last_page?: number;
    links?: { url: string | null; label: string; active: boolean }[];
}

export interface FarmacySummary {
    total: number;
}

export const FARMACY_SEARCH_COLUMNS = [
    { id: 'all',   text: 'Semua Kolom' },
    { id: 'name',  text: 'Nama' },
    { id: 'email', text: 'Email' },
] as const;

export const FARMACY_PER_PAGE_OPTIONS = [
    { id: 5,   text: '5' },
    { id: 10,  text: '10' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
    { id: 100, text: '100' },
] as const;

export const FARMACY_DEFAULT_FILTERS: Required<FarmacyFilters> = {
    search: '',
    search_column: 'all',
    role: '',
    sort_by: 'name',
    sort_direction: 'asc',
    per_page: 5,
    page: 1,
};
