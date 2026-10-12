# Dokumentasi Testing: JournalAttachmentTest

## Deskripsi
Memverifikasi fungsionalitas pengarsipan dokumen pendukung (Digital Archive) yang terhubung langsung dengan record jurnal untuk kebutuhan audit.

## Daftar Method Test & Penjelasan Detail

### 1. `test_journal_attachment_creation` [✅ **PASS**]
- **Deskripsi**: Memastikan file attachment (seperti Scan Invoice) tersimpan dan terelasi dengan jurnal yang tepat.
- **Logika**: 
    1. Membuat jurnal transaksi.
    2. Membuat record `JournalAttachment` yang merujuk ke ID jurnal tersebut.
- **Verifikasi**: 
    - Assert data tersimpan di tabel `journal_attachments`.
    - Assert relasi Eloquent `$journal->attachments()` mengembalikan jumlah yang benar (1).

---
*Dihasilkan secara otomatis oleh Antigravity.*
