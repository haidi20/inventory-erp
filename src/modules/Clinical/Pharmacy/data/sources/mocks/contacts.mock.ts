import type { ManagementContactModel } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const MOCK_CONTACTS: ManagementContactModel[] = [
    { uuid: 'C1', name: 'PT Global Health', type: 'supplier', phone: '021-552022', address: 'Jakarta' },
    { uuid: 'C2', name: 'PT Lestari Medika', type: 'supplier', phone: '021-551544', address: 'Surabaya' },
    { uuid: 'C3', name: 'Pasien Demo Satu', type: 'customer', phone: '08000000001', address: 'Bandung' },
    { uuid: 'C4', name: 'Pasien Demo Dua', type: 'customer', phone: '08000000002', address: 'Medan' },
    { uuid: 'C5', name: 'PT Sentosa Farma', type: 'supplier', phone: '021-559512', address: 'Semarang' },
];
