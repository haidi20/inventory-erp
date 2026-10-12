# Dokumentasi Testing: JournalTaxValidationTest

## Deskripsi
Validasi khusus untuk otomatisasi kalkulasi Pajak (PPN/PPh) agar sesuai dengan aturan persentase yang dikonfigurasi.

## Daftar Method Test & Penjelasan Detail

### 1. `test_automatic_tax_generation_and_balance` [✅ **PASS**]
- **Deskripsi**: Menjamin sistem menambahkan baris pajak secara otomatis tanpa input manual.
- **Logika**: User menginput biaya bruto (misal 1jt) + Tag pajak (11%).
- **Verifikasi**: 
    - Assert sistem secara otomatis memecah nominal menjadi 3 baris: DPB (1jt), PPN (110rb), dan Kas (1.11jt).
    - Assert saldo total tetap Balance.

---
*Dihasilkan secara otomatis oleh Antigravity.*
