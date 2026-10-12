import type { FarmacyStockOpnameRow } from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const MOCK_STOCK_OPNAME_ROWS: FarmacyStockOpnameRow[] = [
    {
        uuid: 'stock-opname-001',
        date: '28 Apr 2026',
        referenceNo: 'ST000000001',
        businessUnit: 'Klinik Utama',
        warehouse: 'Gudang Farmasi',
        description: 'Opname persediaan bulanan',
        status: 'Draft',
    },
    {
        uuid: 'stock-opname-002',
        date: '27 Apr 2026',
        referenceNo: 'ST000000002',
        businessUnit: 'Klinik Utama',
        warehouse: 'Gudang Pusat',
        description: 'Validasi stok fisik pagi',
        status: 'Diposting',
    },
    {
        uuid: 'stock-opname-003',
        date: '26 Apr 2026',
        referenceNo: 'ST000000003',
        businessUnit: 'Klinik Utama',
        warehouse: 'Gudang Farmasi',
        description: 'Opname obat fast moving',
        status: 'Diposting',
    },
    {
        uuid: 'stock-opname-004',
        date: '25 Apr 2026',
        referenceNo: 'ST000000004',
        businessUnit: 'Cabang A',
        warehouse: 'Gudang Cabang A',
        description: 'Pemeriksaan stok akhir shift',
        status: 'Dibatalkan',
    },
    {
        uuid: 'stock-opname-005',
        date: '24 Apr 2026',
        referenceNo: 'ST000000005',
        businessUnit: 'Klinik Utama',
        warehouse: 'Gudang Farmasi',
        description: 'Rekap opname item expired',
        status: 'Draft',
    },
];
