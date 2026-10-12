# Dokumentasi Testing: GeneralLedgerTest

## Deskripsi
Pengujian dasar fungsionalitas Buku Besar, mencakup akurasi kalkulasi mutasi dan penarikan saldo awal (*opening balance*) lintas periode.

## Daftar Method Test & Penjelasan Detail

### 1. `test_general_ledger_report_calculation` [✅ **PASS**]
- **Deskripsi**: Verifikasi perhitungan saldo akhir berdasarkan mutasi Debit dan Kredit.
- **Logika**: Input Debit 100rb -> Input Kredit 30rb pada akun Kas.
- **Verifikasi**: Assert saldo akhir bertambah bersih Rp 70.000 dari saldo pembukaan.

### 2. `test_opening_balance_calculation_before_period` [✅ **PASS**]
- **Deskripsi**: Validasi penarikan saldo awal dari transaksi yang terjadi di periode sebelumnya.
- **Logika**: Posting di bulan Februari dan menarik laporan di bulan Maret.
- **Verifikasi**: Assert saldo penutupan Februari terbaca secara akurat sebagai saldo pembukaan Maret.

---
*Dihasilkan secara otomatis oleh Antigravity.*
