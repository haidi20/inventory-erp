import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export const STOCK_VALUE_LIST_COLUMNS: DataTableColumn[] = [
    { key: 'code', label: 'code', sortable: true },
    { key: 'name', label: 'name', sortable: true },
    { key: 'warehouse_name', label: 'warehouse_name', sortable: true },
    { key: 'on_hand_qty', label: 'on_hand_qty', sortable: true },
    { key: 'unit_name', label: 'unit_name', sortable: true },
    { key: 'value', label: 'value', sortable: true },
];
