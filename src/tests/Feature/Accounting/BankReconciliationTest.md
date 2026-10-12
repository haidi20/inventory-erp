# Dokumentasi Testing: BankReconciliationTest

## Deskripsi
Memastikan sistem mampu mencocokkan saldo Buku Besar (GL) Bank dengan saldo Rekening Koran (Bank Statement) melalui penyesuaian outstanding items.

## Daftar Method Test & Penjelasan Detail

### 1. `test_bank_reconciliation_with_adjustments` [✅ **PASS**]
- **Deskripsi**: Verifikasi akurasi penyesuaian rekonsiliasi.
- **Logika**: Menghitung saldo bank yang disesuaikan (Bank Balance - Outstanding Checks).
- **Verifikasi**: Assert saldo bank yang disesuaikan sama dengan saldo GL Bank (Rp 50.000.000) dan status adalah `MATCH`.

---
*Dihasilkan secara otomatis oleh Antigravity.*
