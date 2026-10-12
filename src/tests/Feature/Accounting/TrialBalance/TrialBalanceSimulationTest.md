# Dokumentasi Testing: TrialBalanceSimulationTest

## Deskripsi
Test suite ini melakukan simulasi tingkat lanjut untuk mendeteksi anomali data (Fraud/Error) dan memastikan konsolidasi Neraca Saldo (Trial Balance) 100% konsisten antara Ledger dan Saldo Akun.

## Daftar Method Test & Penjelasan Detail

### 1. `test_consistency_general_ledger_vs_account_balances` [✅ **PASS**]
- **Deskripsi**: Memastikan sinkronisasi data Buku Besar (GL) ke Neraca Saldo (Account Balance) akurat.
- **Logika**: 
    1. Mencatat jurnal transaksi.
    2. Menjalankan sinkronisasi GL.
- **Verifikasi**: Assert total mutasi di GL sama persis dengan `total_debit` di tabel `account_balances`.

### 2. `test_anomaly_header_account_balance` [✅ **PASS**]
- **Deskripsi**: Deteksi Fraud/Error jika ada saldo yang "nyangkut" di akun Header.
- **Logika**: Memaksa (force) inject transaksi ke akun header via raw SQL, lalu menjalankan skrip deteksi.
- **Verifikasi**: Assert skrip deteksi berhasil menemukan record anomali tersebut (Header account must have 0 balance).

### 3. `test_anomaly_negative_asset_balance` [✅ **PASS**]
- **Deskripsi**: Deteksi anomali saldo negatif pada akun Aset (seperti Kas minus).
- **Logika**: Membuat kondisi saldo akun Aset menjadi negatif.
- **Verifikasi**: Assert sistem pendeteksi berhasil menangkap akun tipe `asset` yang memiliki `closing_balance` < 0.

---
*Dihasilkan secara otomatis oleh Antigravity.*
