import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export const ICD10_COLUMNS: DataTableColumn[] = [
    { key: 'code',        label: 'Kode',    width: '120px', sortable: true },
    { key: 'description', label: 'Deskripsi' },
    { key: 'actions',     label: 'Aksi',    width: '90px', align: 'center' },
];
