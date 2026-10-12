import type { FarmacyTransactionModel } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const MOCK_TRANSACTIONS: FarmacyTransactionModel[] = [
    { uuid: 'T1', invoice_no: 'INV/20260427/001', customer_name: 'Pasien Demo Satu', total_amount: 150000, payment_status: 'paid', date: '2026-04-27 10:30' },
    { uuid: 'T2', invoice_no: 'INV/20260427/002', customer_name: 'Pasien Demo Dua', total_amount: 85000, payment_status: 'unpaid', date: '2026-04-27 11:45' },
    { uuid: 'T3', invoice_no: 'INV/20260427/003', customer_name: 'Pasien Demo Tiga', total_amount: 320000, payment_status: 'paid', date: '2026-04-27 13:20' },
    { uuid: 'T4', invoice_no: 'INV/20260427/004', customer_name: 'Pasien Demo Empat', total_amount: 45000, payment_status: 'partial', date: '2026-04-27 14:40' },
    { uuid: 'T5', invoice_no: 'INV/20260427/005', customer_name: 'Budi Utomo', total_amount: 125000, payment_status: 'paid', date: '2026-04-27 15:15' },
];
