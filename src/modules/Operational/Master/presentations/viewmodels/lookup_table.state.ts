import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export const LOOKUP_TABLE_COLUMNS: DataTableColumn[] = [
    { key: 'user_can_edit_flag', label: 'Status', width: '160px', align: 'center' },
    { key: 'name', label: 'Nama Master', sortable: true },
    { key: 'program_code', label: 'Kode Program', sortable: true },
    { key: 'description', label: 'Keterangan' },
    { key: '_actions', label: 'Aksi', width: '120px', align: 'center' },
];

export const LOOKUP_TABLE_SEARCH_COLUMNS = [
    { id: 'all', text: 'Semua Kolom' },
    { id: 'name', text: 'Nama' },
    { id: 'program_code', text: 'Kode Program' },
];

