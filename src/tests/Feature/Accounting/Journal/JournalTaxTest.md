# Dokumentasi Testing: JournalTaxTest

## Deskripsi
Fokus pada validasi perhitungan PPN (Pajak Pertambahan Nilai) otomatis untuk transaksi Penjualan.

## Daftar Method Test & Penjelasan Detail

### 1. `test_sales_tax_output_calculation` [✅ **PASS**]
- **Deskripsi**: Verifikasi pembentukan baris PPN Keluaran secara otomatis.
- **Logika**: 
    1. Membuat jurnal penjualan dengan total tagihan 1.110.000.
    2. Menandai baris pendapatan sebagai `taxable` dengan rate 11%.
- **Verifikasi**: 
    - Assert sistem secara otomatis menyisipkan baris ketiga (PPN) dengan akun `2106`.
    - Assert nilai PPN adalah 110.000 (11% dari DPP 1.000.000).

---
*Dihasilkan secara otomatis oleh Antigravity.*
