import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export const BARCODE_PRINT_COLUMNS: DataTableColumn[] = [
    { key: 'code', label: 'code', sortable: true },
    { key: 'name', label: 'name', sortable: true },
    { key: 'barcode', label: 'barcode', sortable: true },
    { key: 'unit_name', label: 'unit_name', sortable: true },
];
