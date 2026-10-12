# Dokumentasi Testing: GeneralLedgerSubLedgerTest

## Deskripsi
Memastikan akumulasi nilai dari detail transaksi (*Journal Lines*) sesuai dengan saldo yang tersimpan di tabel ringkasan (*Account Balance*).

## Daftar Method Test & Penjelasan Detail

### 1. `test_sub_ledger_aggregate_matches_account_balance` [✅ **PASS**]
- **Deskripsi**: Rekonsiliasi antara total mutasi per periode dengan saldo akhir akun.
- **Logika**: 
    1. Input 2 jurnal penjualan (Rp 250rb & Rp 150rb).
    2. Menghitung `Sum(debit)` dari `journal_lines` pada periode terkait.
- **Verifikasi**: Assert bahwa total debit di `account_balances` sama dengan hasil penjumlahan manual dari baris jurnal (Rp 400.000).

---
*Dihasilkan secara otomatis oleh Antigravity.*
