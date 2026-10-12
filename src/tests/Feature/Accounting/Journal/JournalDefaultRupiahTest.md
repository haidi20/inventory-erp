# Dokumentasi Testing: JournalDefaultRupiahTest

## Deskripsi
Uji coba standar untuk transaksi menggunakan mata uang fungsional (Rupiah/IDR) tanpa komponen Valas, memastikan sistem fallback ke IDR dengan benar.

## Daftar Method Test & Penjelasan Detail

### 1. `test_journal_with_default_rupiah` [✅ **PASS**]
- **Deskripsi**: Penjualan standar dalam Rupiah.
- **Logika**: Mengirim DTO jurnal tanpa mengisi field mata uang asing.
- **Verifikasi**: 
    - Assert total debit sesuai input.
    - Assert field `foreign_currency` pada baris jurnal bernilai Null.
    - Assert `foreign_amount` bernilai 0.

---
*Dihasilkan secara otomatis oleh Antigravity.*
