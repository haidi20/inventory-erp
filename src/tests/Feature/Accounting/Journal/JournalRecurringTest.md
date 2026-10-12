# Dokumentasi Testing: JournalRecurringTest

## Deskripsi
Otomatisasi pencatatan jurnal berulang (Recurring) untuk transaksi rutin seperti sewa bulanan atau penyusutan aset.

## Daftar Method Test & Penjelasan Detail

### 1. `test_automatic_recurring_journal_creation` [✅ **PASS**]
- **Deskripsi**: Verifikasi generator otomatis membuat jurnal berdasarkan template.
- **Logika**: 
    1. Membuat template `RecurringJournal` dengan frekuensi bulanan.
    2. Menjalankan `processRecurringJournals()`.
- **Verifikasi**: 
    - Assert jurnal baru terbentuk dengan prefix `[RECURRING]`.
    - Assert `next_run_date` pada template otomatis maju 1 bulan ke depan.

---
*Dihasilkan secara otomatis oleh Antigravity.*
