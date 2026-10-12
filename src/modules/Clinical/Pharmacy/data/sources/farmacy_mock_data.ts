export * from './mocks/dashboard.mock';
export * from './mocks/recipes.mock';
export * from './mocks/transactions.mock';
export * from './mocks/inventory.mock';
export * from './mocks/accounting.mock';
export * from './mocks/contacts.mock';
export * from './mocks/menus.mock';
export * from './mocks/stock_adjustment.mock';
export * from './mocks/stock_opname.mock';
export * from './mocks/stock_mutation.mock';

// Tetap pertahankan MOCK_REPORTS di sini atau pindahkan jika perlu
export const MOCK_REPORTS = [
    { name: 'Sales Report', description: 'Daily/monthly drug sales summary' },
    { name: 'Stock Report', description: 'Availability status and inventory value' },
    { name: 'Psychotropic Report', description: 'Specific reporting for certain drugs' },
    { name: 'Product Margin Report', description: 'Profit analysis per product' },
    { name: 'Stock Card Report', description: 'Item movement history' },
];
