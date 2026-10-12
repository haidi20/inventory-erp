import type { AccountingAccountModel } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const MOCK_ACCOUNTS: AccountingAccountModel[] = [
    { uuid: 'A1', code: '1101', name: 'Main Cash', group: 'Cash & Bank', balance: 25000000 },
    { uuid: 'A2', code: '1102', name: 'Petty Cash', group: 'Cash & Bank', balance: 5000000 },
    { uuid: 'A3', code: '1201', name: 'Accounts Receivable', group: 'Receivable', balance: 15200000 },
    { uuid: 'A4', code: '1301', name: 'Medicine Inventory', group: 'Inventory', balance: 45000000 },
    { uuid: 'A5', code: '4102', name: 'Sales Revenue', group: 'Revenue', balance: 125000000 },
];
