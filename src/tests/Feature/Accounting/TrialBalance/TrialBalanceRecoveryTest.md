# Dokumentasi Testing: TrialBalanceRecoveryTest

## Deskripsi
Simulasi skenario pemulihan data (*Disaster Recovery*) untuk memastikan Neraca Saldo dapat dibangun ulang 100% dari data mentah jurnal jika terjadi kerusakan pada tabel agregat.

## Daftar Method Test & Penjelasan Detail

### 1. `test_reconstruct_account_balances_from_raw_journals` [✅ **PASS**]
- **Deskripsi**: Menghapus isi tabel `account_balances` dan membangunnya kembali dari `journal_lines`.
- **Logika**: 
    1. Backup data `journal_lines`.
    2. TRUNCATE `account_balances`.
    3. Jalankan `TrialBalanceService::recalculateAll()`.
- **Verifikasi**: Assert saldo akhir kembali identik dengan kondisi sebelum penghapusan.

### 2. `test_integrity_after_database_rollback` [✅ **PASS**]
- **Deskripsi**: Memastikan rollback transaksi SQL tidak merusak status periode akuntansi.
- **Verifikasi**: Assert status periode tetap `open` jika proses rekonstruksi gagal di tengah jalan.

---
*Dihasilkan secara otomatis oleh Antigravity.*
