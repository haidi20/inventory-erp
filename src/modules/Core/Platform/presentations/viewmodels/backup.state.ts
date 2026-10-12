// backup.state.ts — state types untuk BackupVM
export interface BackupModel {
    uuid: string;
    filename: string;
    file_size?: number;
    notes?: string;
    history_type: 'manual' | 'auto';
    created_at?: number;
    created_by?: string;
}

export interface BackupFormData {
    file: File | null;
    history_type: string;
}

export interface BackupFilters {
    search?: string;
    history_type?: 'manual' | 'auto';
    per_page?: number;
    page?: number;
    order_column?: number;
    order_direction?: 'asc' | 'desc';
}

export interface BackupMeta {
    total: number;
    current_page: number;
    per_page: number;
    last_page?: number;
}

export const BACKUP_HISTORY_TYPES = [
    { id: 'manual', text: 'Manual' },
    { id: 'auto',   text: 'Otomatis' },
] as const;

export const BACKUP_PER_PAGE_OPTIONS = [
    { id: 5,   text: '5' },
    { id: 10,  text: '10' },
    { id: 25,  text: '25' },
    { id: 50,  text: '50' },
] as const;

export const BACKUP_DEFAULT_FILTERS: Required<BackupFilters> = {
    search: '',
    history_type: 'manual',
    per_page: 5,
    page: 1,
    order_column: 0,
    order_direction: 'desc',
};
