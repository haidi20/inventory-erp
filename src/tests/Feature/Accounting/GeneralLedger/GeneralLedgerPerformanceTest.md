# Dokumentasi Testing: GeneralLedgerPerformanceTest

## Deskripsi
Pengujian performa sinkronisasi Buku Besar (Stored Procedure) dan efisiensi query laporan pada dataset besar.

## Daftar Method Test & Penjelasan Detail

### 1. `test_bulk_journal_processing_performance` [✅ **PASS**]
- **Deskripsi**: Mengukur kecepatan Stored Procedure `SyncGeneralLedgerByPeriod` memproses 100 jurnal.
- **Logika**: Posting 100 jurnal sekaligus, kemudian menjalankan sinkronisasi.
- **Verifikasi**: Assert proses sinkronisasi selesai dalam waktu kurang dari 10 detik.

### 2. `test_large_dataset_query_performance` [✅ **PASS**]
- **Deskripsi**: Mengukur latency query laporan Buku Besar pada akun dengan banyak mutasi.
- **Logika**: Menarik laporan GL pada akun yang memiliki 50+ baris transaksi.
- **Verifikasi**: Assert respon query selesai dalam waktu kurang dari 3 detik.

### 3. `test_stored_procedure_data_integrity` [✅ **PASS**]
- **Deskripsi**: Memastikan data yang dihasilkan oleh Stored Procedure akurat secara matematis.
- **Verifikasi**: Assert `closing_balance` sama dengan `opening + total_debit - total_credit` setelah sinkronisasi massal.

---
*Dihasilkan secara otomatis oleh Antigravity.*
