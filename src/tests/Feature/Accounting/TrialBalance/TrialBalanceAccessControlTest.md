# Dokumentasi Testing: TrialBalanceAccessControlTest

## Deskripsi
Memverifikasi isolasi data keuangan antar cabang (Multi-tenancy) dan pembatasan akses laporan berdasarkan hak akses pengguna.

## Daftar Method Test & Penjelasan Detail

### 1. `test_trial_balance_data_isolation_between_branches` [✅ **PASS**]
- **Deskripsi**: Menjamin transaksi Cabang A tidak muncul di laporan Trial Balance Cabang B.
- **Logika**: Posting di Branch 1, menarik TB untuk Branch 2.
- **Verifikasi**: Assert total saldo di TB Branch 2 adalah 0 (atau tidak terpengaruh Branch 1).

### 2. `test_user_cannot_view_trial_balance_of_other_branch` [✅ **PASS**]
- **Deskripsi**: Validasi keamanan level API/Controller.
- **Verifikasi**: Assert status code `403 Forbidden` saat mencoba akses `/trial-balance?branch_id=X` (cabang lain).

### 3. `test_restricted_role_access_to_financial_reports` [✅ **PASS**]
- **Deskripsi**: Role non-accounting tidak boleh melihat laporan keuangan.
- **Verifikasi**: Assert status code `403` untuk user dengan role `Operator`.

---
*Dihasilkan secara otomatis oleh Antigravity.*
