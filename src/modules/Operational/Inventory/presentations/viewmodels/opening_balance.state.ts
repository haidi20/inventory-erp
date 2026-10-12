import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export const OPENING_BALANCE_COLUMNS: DataTableColumn[] = [
    { key: 'code', label: 'code', sortable: true },
    { key: 'name', label: 'name', sortable: true },
    { key: 'warehouse_name', label: 'warehouse_name', sortable: true },
    { key: 'opening_qty', label: 'opening_qty', sortable: true },
    { key: 'unit_name', label: 'unit_name', sortable: true },
];
