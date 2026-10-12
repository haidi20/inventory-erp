# Dokumentasi Testing: JournalServiceTest

## Deskripsi
Test foundational yang mencakup logika inti pembuatan jurnal, update, dan penghapusan (CRUD) di layer service.

## Daftar Method Test & Penjelasan Detail

### 1. `test_klinik_daily_transactions_period_27_to_26_from_db` [✅ **PASS**]
- **Deskripsi**: Memverifikasi bahwa seeder `JournalSimulation...` telah berhasil membuat 12 transaksi per hari di database.
- **Logika**: Melakukan iterasi tanggal dari 2026-03-01 hingga 2026-03-31, mencari jurnal dengan prefix tertentu (INV, EXP, PO, dll).
- **Verifikasi**: 
    - Assert jumlah jurnal per hari = 12.
    - Assert setiap jurnal balance (Debit - Kredit = 0).

### 2. `test_verify_seeded_general_journal_data` [✅ **PASS**]
- **Deskripsi**: Memastikan data jurnal hasil seeder memiliki format kode transaksi yang standar.
- **Logika**: Mengambil semua jurnal dengan referensi penjualan (INV) dan mengecek field `transaction_code`.
- **Verifikasi**: Assert regex format `[BRANCH]/[TYPE]/[YYYYMM]/[SEQ]`.

### 3. `test_klinik_daily_transactions_period_27_to_26_from_dummy` [✅ **PASS**]
- **Deskripsi**: Simulasi pembuatan 5 transaksi penjualan baru per hari menggunakan array (Bulk Create).
- **Logika**: Menjalankan `JournalService::createManyByArray`.
- **Verifikasi**: Assert jumlah hasil kembalian (5 per hari).

### 4. `test_batch_balancing_validation` [✅ **PASS**]
- **Deskripsi**: Memastikan sistem menolak input batch jika ada satu jurnal yang tidak balance.
- **Logika**: Mengirimkan data dengan selisih Rp 1 antara Debit dan Kredit.
- **Verifikasi**: Assert melempar `Exception`.

### 5. `test_transaction_code_format` [✅ **PASS**]
- **Deskripsi**: Validasi penomoran otomatis saat pembuatan jurnal tunggal.
- **Verifikasi**: Assert `transaction_code` sesuai standar perusahaan.

### 6. `test_sale_with_cogs_scenario_from_dummy` [✅ **PASS**]
- **Deskripsi**: Menguji skenario penjualan stok yang menghasilkan dua pasang jurnal (Revenue & HPP).
- **Logika**: Satu transaksi menghasilkan 2 entri jurnal terkait.
- **Verifikasi**: Assert saldo pendapatan dan saldo persediaan terupdate benar.

### 7. `test_branch_data_isolation` [✅ **PASS**]
- **Deskripsi**: Uji multi-tenancy (isolasi data antar cabang).
- **Logika**: User Cabang B mencoba mengakses data Cabang A.
- **Verifikasi**: Assert hasil record adalah 0.

### 8. `test_closed_period_rejection` [✅ **PASS**]
- **Deskripsi**: Keamanan periode akuntansi.
- **Logika**: Mencoba membuat jurnal pada tanggal di mana `ClosingPeriod` berstatus 'closed'.
- **Verifikasi**: Assert melempar `Exception` proteksi periode.

### 9. `test_void_journal_success` [✅ **PASS**]
- **Deskripsi**: Mekanisme pembatalan jurnal.
- **Logika**: Menggunakan `JournalService::voidJournal`.
- **Verifikasi**: Assert terbentuk jurnal baru tipe `REVERSAL` dengan angka yang dibalik.

### 10. `test_system_journal_cannot_be_edited_manually` [✅ **PASS**]
- **Deskripsi**: Proteksi Integritas Audit.
- **Logika**: Mencoba mengedit jurnal yang memiliki flag `is_system = true`.
- **Verifikasi**: Assert penolakan akses (hanya boleh via modul asal).

---
*Dihasilkan secara otomatis oleh Antigravity.*
