# Dokumentasi Testing: JournalBatchImportSimulationTest

## Deskripsi
Simulasi performa dan integritas data saat melakukan import jurnal dalam jumlah besar sekaligus (Batch Import).

## Daftar Method Test & Penjelasan Detail

### 1. `test_large_batch_import_performance_and_integrity` [✅ **PASS**]
- **Deskripsi**: Menjamin keunikan nomor transaksi dan performa penyimpanan 100+ jurnal.
- **Logika**: Menjalankan loop pembuatan 100 jurnal dalam satu sesi testing.
- **Verifikasi**: 
    - Assert jumlah jurnal di database tepat 100.
    - Assert Stored Procedure `SyncGeneralLedgerByPeriod` berhasil memproses seluruh batch tanpa error.
    - Assert setiap `transaction_code` yang di-generate sistem unik (tidak ada tabrakan nomor).

---
*Dihasilkan secara otomatis oleh Antigravity.*
