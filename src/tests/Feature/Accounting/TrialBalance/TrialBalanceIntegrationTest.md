# Dokumentasi Testing: TrialBalanceIntegrationTest

## Deskripsi
Menguji alur integrasi dasar pembuatan Neraca Saldo (*Trial Balance*) dari akumulasi mutasi Buku Besar.

## Daftar Method Test & Penjelasan Detail

### 1. `test_trial_balance_generation_from_ledger` [✅ **PASS**]
- **Deskripsi**: Memasukkan transaksi di Ledger dan memastikan TB terakumulasi dengan benar.
- **Logika**: Posting Debit 500rb -> Sync Ledger -> Generate TB.
- **Verifikasi**: Assert total saldo akun terkait adalah 500rb.

### 2. `test_accounting_equation_consistency` [✅ **PASS**]
- **Deskripsi**: Menjamin Total Aset == Total Kewajiban + Total Ekuitas.
- **Verifikasi**: Assert `Total Debit - Total Kredit = 0` pada level Trial Balance.

---
*Dihasilkan secara otomatis oleh Antigravity.*
