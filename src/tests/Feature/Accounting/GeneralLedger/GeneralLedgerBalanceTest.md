# Dokumentasi Testing: GeneralLedgerBalanceTest

## Deskripsi
Memverifikasi akurasi perhitungan saldo berjalan (*running balance*), mekanisme reset saldo akun Laba/Rugi pada awal tahun fiskal, dan kepatuhan terhadap persamaan akuntansi dasar.

## Daftar Method Test & Penjelasan Detail

### 1. `test_running_balance_accuracy_for_account` [✅ **PASS**]
- **Deskripsi**: Menjamin kolom `balance` pada laporan Buku Besar dihitung secara berurutan setelah setiap mutasi.
- **Logika**: 
    1. Melakukan 5 transaksi (debit/kredit) pada akun Kas (1101).
    2. Menarik laporan via `getLedgerReport()`.
- **Verifikasi**: Assert bahwa saldo di baris ke-N adalah hasil dari `Saldo N-1 + Debit N - Kredit N`.

### 2. `test_income_statement_accounts_reset_at_year_start` [✅ **PASS**]
- **Deskripsi**: Validasi reset saldo akun P&L (Revenue/Expense) menjadi nol pada awal tahun fiskal baru.
- **Logika**: Membandingkan opening balance akun pendapatan di bulan Maret (masih tahun yang sama) vs 1 Januari tahun fiskal baru.
- **Verifikasi**: Assert opening balance 1 Januari adalah 0.0, sedangkan Maret mencakup akumulasi mutasi Januari-Februari.

### 3. `test_accounting_equation_via_trial_balance` [✅ **PASS**]
- **Deskripsi**: Memastikan integritas data global (Total Debit == Total Kredit).
- **Logika**: Melakukan serangkaian jurnal seimbang dan memanggil `checkLedgerBalance()`.
- **Verifikasi**: Assert `is_balanced = true` dan selisih absolut mendekati 0.

### 4. `test_debit_credit_totals_consistency` [✅ **PASS**]
- **Deskripsi**: Menjamin konsistensi antara header laporan (Summary) dengan detail baris transaksi.
- **Logika**: Posting 3 transaksi biaya dan mengecek total mutasi pada akun terkait.
- **Verifikasi**: Assert `total_debit` di header laporan sesuai dengan jumlah transaksi yang diinput.

---
*Dihasilkan secara otomatis oleh Antigravity.*
