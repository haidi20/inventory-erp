import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export const ADVANCE_CLASSIFICATION_COLUMNS: DataTableColumn[] = [
    { key: 'program_code', label: 'Kode',       width: '150px', sortable: true },
    { key: 'name',         label: 'Nama',        sortable: true },
    { key: 'description',  label: 'Keterangan' },
    { key: 'actions',      label: 'Aksi',        width: '100px', align: 'center' },
];
