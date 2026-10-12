import {
    PelayananStatus,
    type RecipeModel,
    type MedicationItem,
    type PatientProfile
} from '@modules/Clinical/Pharmacy/domain/models/farmacy.model';

export const MOCK_RECIPES: RecipeModel[] = [
    { uuid: '1', recipe_no: 'RC-260401453', patient_name: 'Pasien Demo Satu', service_type: 'Rawat Jalan', doctor_name: 'Dr. Denny Octavia', status: 'completed', recipe_type: 'Non-Racikan', created_at: '2026-04-27 10:00' },
    { uuid: '2', recipe_no: 'RC-260401454', patient_name: 'Pasien Demo Dua', service_type: 'IGD', doctor_name: 'Dr. Eko Setiana', status: 'pending', recipe_type: 'Non-Racikan', created_at: '2026-04-27 11:30' },
    { uuid: '3', recipe_no: 'RC-260401455', patient_name: 'Pasien Demo Tiga', service_type: 'Rawat Inap', doctor_name: 'Dr. Denny Octavia', status: 'completed', recipe_type: 'Non-Racikan', created_at: '2026-04-27 13:00' },
    { uuid: '4', recipe_no: 'RC-260401456', patient_name: 'Pasien Demo Empat', service_type: 'Rawat Jalan', doctor_name: 'Dr. Eko Setiana', status: 'cancelled', recipe_type: 'Non-Racikan', created_at: '2026-04-27 14:15' },
    { uuid: '5', recipe_no: 'RC-260401457', patient_name: 'Budi Utomo', service_type: 'MCU', doctor_name: 'Dr. Denny Octavia', status: 'pending', recipe_type: 'Non-Racikan', created_at: '2026-04-27 15:00' },
];

export const MOCK_RECIPE_DETAILS: MedicationItem[] = [
    { uuid: 'M1', name: 'Rostin 10', batch: 'V2045', qty: '2 Strip', price: 4000, subtotal: 8000, usage: '1 X 1 TABLET SESUDAH MAKAN', note: 'OBAT KOLESTEROL (MALAM)' },
    { uuid: 'M2', name: 'FENOLIP 160', batch: 'ASL374A', qty: '1 Strip', price: 12000, subtotal: 12000, usage: '1 X 1 TABLET SESUDAH MAKAN', note: '-' }
];

export const MOCK_PATIENT_PROFILE: PatientProfile = {
    recipe_no: 'RC-260401453',
    name: 'AHMAD SURYANI',
    birth_date: 'Pagar Haur, 05 Dec 1992',
    age: '33 Tahun 4 Bulan 22 Hari',
    gender: 'Laki Laki',
    nik: '6306020512920002',
    phone: '083845967751',
    admission_type: 'Rawat Jalan',
    company: '-',
    classification: 'Dewasa',
    pharmacist: 'Nilam Sylvia Ramadhani, A. Md. Farm',
    unit: 'Klinik Utama',
    doctor: 'dr. Andika Putra, Sp.PD',
    date: '27 Apr 2026, 09:30',
    notes: 'Kontrol 1 minggu lagi. Pastikan obat diminum rutin sesuai aturan pakai.',
    pelayanan_status: PelayananStatus.PREPARING
};

export const MOCK_PHARMACISTS = [
    { id: '1', name: 'Nilam Sylvia Ramadhani, A. Md. Farm' },
    { id: '2', name: 'Riska Sari, A. Md. RMIK' },
    { id: '3', name: 'Andi Wijaya, S. Farm' },
];

export const MOCK_RECIPE_WAREHOUSES = [
    { id: 'G01', name: 'Gudang Utama' },
    { id: 'G02', name: 'Gudang Farmasi Lt. 1' },
    { id: 'G03', name: 'Gudang IGD' },
];

export const MOCK_RECIPE_BUSINESS_UNITS = ['Klinik Utama', 'Klinik Berkat Medika', 'Apotek Pusat'];
