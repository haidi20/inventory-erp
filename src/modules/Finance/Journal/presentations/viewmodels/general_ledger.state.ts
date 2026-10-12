import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export const GENERAL_LEDGER_COLUMNS: DataTableColumn[] = [
    { key: 'date',        label: 'Tanggal',  width: '120px', sortable: true },
    { key: 'description', label: 'Keterangan' },
    { key: 'debit',       label: 'Debit',    width: '140px', align: 'right' },
    { key: 'credit',      label: 'Kredit',   width: '140px', align: 'right' },
];
