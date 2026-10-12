# Dokumentasi Testing: TrialBalanceAuditForensicTest

## Deskripsi
Memverifikasi jejak audit (*Audit Trail*) pada setiap perubahan data yang mempengaruhi Trial Balance.

## Daftar Method Test & Penjelasan Detail

### 1. `test_audit_log_captured_on_balance_sync` [✅ **PASS**]
- **Deskripsi**: Pencatatan aktivitas sinkronisasi massal oleh sistem.
- **Verifikasi**: Assert entri baru ada di tabel `audit_logs` dengan kategori `ACCOUNTING_SYNC`.

### 2. `test_forensic_detects_manual_entry_tampering` [✅ **PASS**]
- **Deskripsi**: Mendeteksi jika ada perubahan saldo langsung di database tanpa melalui dokumen jurnal.
- **Logika**: Mengubah `total_debit` di tabel `account_balances` via SQL, lalu menjalankan skrip integritas.
- **Verifikasi**: Assert skrip integritas melaporkan "Mismatch detected between Ledger sum and Account Balance".

---
*Dihasilkan secara otomatis oleh Antigravity.*
