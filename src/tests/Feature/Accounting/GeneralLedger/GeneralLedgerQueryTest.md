# Dokumentasi Testing: GeneralLedgerQueryTest

## Deskripsi
Memverifikasi fleksibilitas filter laporan (periode, cabang) dan integrasi data Buku Besar ke dalam Laporan Keuangan formal (Neraca & Laba Rugi).

## Daftar Method Test & Penjelasan Detail

### 1. `test_period_filter_isolates_transactions` [✅ **PASS**]
- **Deskripsi**: Memastikan filter tanggal hanya menampilkan transaksi di dalam range tersebut.
- **Logika**: Posting di Februari dan Maret, lalu query harian untuk Maret saja.
- **Verifikasi**: 
    - Assert transaksi Februari tidak muncul di list detail.
    - Assert mutasi Februari masuk secara otomatis ke dalam `opening_balance` laporan Maret.

### 2. `test_opening_balance_accumulates_previous_periods` [✅ **PASS**]
- **Deskripsi**: Akumulasi saldo berjalan dari seluruh sejarah transaksi sebelum periode terpilih.
- **Verifikasi**: Assert `opening_balance` Maret mencakup seluruh mutasi yang terjadi di Januari dan Februari.

### 3. `test_income_statement_reflects_gl_data` [✅ **PASS**]
- **Deskripsi**: Sinkronisasi data antara Buku Besar dengan Laporan Laba Rugi.
- **Verifikasi**: Assert total Pendapatan dan Biaya di Laporan Laba Rugi sama dengan akumulasi saldo akun terkait di Buku Besar.

### 4. `test_balance_sheet_is_balanced` [✅ **PASS**]
- **Deskripsi**: Validasi kesetimbangan Neraca (Aset = Kewajiban + Ekuitas).
- **Verifikasi**: Assert `is_balanced = true` pada output Laporan Neraca konsolidasi.

---
*Dihasilkan secara otomatis oleh Antigravity.*
