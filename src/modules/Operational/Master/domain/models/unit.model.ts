export enum UnitSearchColumn {
    ALL = 'all',
    CODE = 'code',
    NAME = 'name',
}

export interface UnitModel {
    uuid: string;
    code: string;
    name: string;
    symbol?: string | null;
    description?: string | null;
    is_active: boolean;
}

export interface UnitFormData {
    uuid?: string;
    code: string;
    name: string;
    symbol?: string;
    description?: string;
    is_active: boolean;
}

export interface UnitFilters {
    search?: string;
    search_column?: string;
    sort_by?: 'code' | 'name';
    sort_direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export interface UnitSummary {
    total: number;
}
