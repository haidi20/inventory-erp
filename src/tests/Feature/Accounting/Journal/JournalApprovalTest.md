# Dokumentasi Testing: JournalApprovalTest

## Deskripsi
Memverifikasi fungsionalitas dasar status Draft dan transisi ke status Posted melalui mekanisme Approval.

## Daftar Method Test & Penjelasan Detail

### 1. `test_create_journal_as_draft_skips_ledger` [✅ **PASS**]
- **Deskripsi**: Memasukkan jurnal sebagai Draft tidak boleh mempengaruhi Buku Besar (Ledger).
- **Logika**: 
    1. Membuat jurnal dengan `status = draft`.
    2. Menjalankan Stored Procedure `SyncGeneralLedgerByPeriod`.
- **Verifikasi**: Assert tidak ada baris record yang terbentuk di tabel `general_ledgers` untuk ID jurnal tersebut.

### 2. `test_approve_draft_journal` [✅ **PASS**]
- **Deskripsi**: Mengubah status Draft menjadi Posted dan memicu pencatatan ke Ledger.
- **Logika**: Mengambil jurnal Draft -> Memanggil `approveJournal()`.
- **Verifikasi**: 
    - Assert status berubah menjadi `posted`.
    - Assert `workflow_status` menjadi `approved`.
    - Assert record Buku Besar (General Ledger) sudah terbentuk setelah sinkronisasi.

---
*Dihasilkan secara otomatis oleh Antigravity.*
