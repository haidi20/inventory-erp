import type { InventoryModel } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const MOCK_INVENTORY: InventoryModel[] = [
    { uuid: 'P1', product_code: 'AMX-500', product_name: 'Amoxicillin 500mg', category: 'Antibiotic', stock_qty: 150, unit: 'Capsule', min_stock: 50, expiry_date: '2027-12-31' },
    { uuid: 'P2', product_code: 'PCT-500', product_name: 'Paracetamol 500mg', category: 'OTC', stock_qty: 12, unit: 'Tablet', min_stock: 100, expiry_date: '2026-05-15' },
    { uuid: 'P3', product_code: 'CTZ-10', product_name: 'Cetirizine 10mg', category: 'Antihistamine', stock_qty: 85, unit: 'Tablet', min_stock: 20, expiry_date: '2028-01-20' },
    { uuid: 'P4', product_code: 'DEX-05', product_name: 'Dexamethasone 0.5mg', category: 'Corticosteroid', stock_qty: 200, unit: 'Tablet', min_stock: 50, expiry_date: '2027-06-10' },
    { uuid: 'P5', product_code: 'ANT-SYR', product_name: 'Antacid Syrup', category: 'Antacid', stock_qty: 5, unit: 'Bottle', min_stock: 10, expiry_date: '2026-08-25' },
];
