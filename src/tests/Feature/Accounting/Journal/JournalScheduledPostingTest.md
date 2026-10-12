# Dokumentasi Testing: JournalScheduledPostingTest

## Deskripsi
Test ini bertujuan untuk memverifikasi mekanisme **Scheduled Posting** (Posting Terjadwal) yang memindahkan baris jurnal dari status 'unposted' ke General Ledger (Buku Besar) secara otomatis melalui background task atau service method.

## Daftar Method Test & Penjelasan Detail

### 1. `test_scheduled_journal_is_not_posted_immediately` [✅ **PASS**]
- **Deskripsi**: Memastikan jurnal yang memiliki tanggal jadwal (`scheduled_at`) di masa depan tidak langsung masuk ke Buku Besar (General Ledger).
- **Logika**: 
    1. Membuat jurnal dengan `scheduled_at` diset untuk besok.
    2. Mengecek saldo di tabel `journal_lines` dengan filter `is_processed = true`.
- **Verifikasi**: Assert saldo di Ledger tetap 0 meskipun record jurnal sudah ada di database.

### 2. `test_process_scheduled_journals` [✅ **PASS**]
- **Deskripsi**: Menguji eksekusi pemrosesan jurnal yang jadwalnya sudah tiba/lewat.
- **Logika**: 
    1. Membuat jurnal dengan `scheduled_at` di masa lalu (contoh: 1 menit yang lalu).
    2. Menjalankan service method `processScheduledJournals()`.
- **Verifikasi**: 
    - Assert jumlah jurnal yang diproses = 1.
    - Assert status jurnal berubah menjadi `POSTED`.
    - Assert flag `is_processed` menjadi `true`.
    - Assert saldo di Ledger sudah terupdate sesuai nilai transaksi.

---
*Dihasilkan secara otomatis oleh Antigravity.*
