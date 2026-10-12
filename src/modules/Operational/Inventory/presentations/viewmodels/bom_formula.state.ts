import type { DataTableColumn } from '@modules/Core/shared/Components/common/DataTable-types';

export const BOM_FORMULA_COLUMNS: DataTableColumn[] = [
    { key: 'code', label: 'code', sortable: true },
    { key: 'name', label: 'name', sortable: true },
    { key: 'base_product', label: 'base_product', sortable: true },
    { key: 'qty', label: 'qty', sortable: true },
    { key: 'unit_name', label: 'unit_name', sortable: true },
];
