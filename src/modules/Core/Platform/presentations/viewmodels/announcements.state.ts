import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export const ANNOUNCEMENTS_COLUMNS: DataTableColumn[] = [
    { key: 'title',      label: 'Judul',     sortable: true },
    { key: 'content',    label: 'Isi' },
    { key: 'start_date', label: 'Mulai',     width: '120px' },
    { key: 'end_date',   label: 'Selesai',   width: '120px' },
    { key: 'is_active',  label: 'Aktif',     width: '90px', align: 'center' },
];
