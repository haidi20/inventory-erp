export interface BackupModel {
    name: string; // Filename
    size: number;
    status: string;
    source: string;
    history_type: 'manual' | 'system';
    updated_at: string;
    // Formatted labels from API
    size_label: string;
    status_label: string;
    source_label: string;
    history_type_label: string;
    updated_at_label: string;
    download_url: string;
}

export interface BackupFormData {
    backup_file: File | null;
    create_pre_restore_backup: boolean;
}

export interface BackupFilters {
    search?: string;
    history_type: 'manual' | 'system';
    page?: number;
    per_page?: number;
    order_column?: string | number;
    order_direction?: 'asc' | 'desc';
}
