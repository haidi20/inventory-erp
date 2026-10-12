import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export const LOOKUP_VALUE_TABLE_COLUMNS: DataTableColumn[] = [
    { key: 'seq',          label: 'Urutan',        width: '70px',  align: 'center', sortable: true },
    { key: 'display_name', label: 'Nama Tampilan',                                  sortable: true },
    { key: 'program_code', label: 'Kode Program',  width: '14%',                    sortable: true },
    { key: 'primary_flag', label: 'Default',       width: '90px',  align: 'center' },
    { key: 'active_flag',  label: 'Status',        width: '90px',  align: 'center' },
    { key: 'start_date',   label: 'Sejak',         width: '110px', align: 'center' },
    { key: 'end_date',     label: 'Sampai',        width: '110px', align: 'center' },
    { key: 'max_stock',    label: 'Stok Max',      width: '80px',  align: 'center' },
    { key: '_actions',     label: 'Aksi',          width: '100px', align: 'center' },
];
