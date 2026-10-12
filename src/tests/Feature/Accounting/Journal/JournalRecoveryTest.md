# Dokumentasi Testing: JournalRecoveryTest

## Deskripsi
Menguji ketahanan sistem (Resilience) terhadap kegagalan mendadak dan kemampuan pemulihan data tanpa duplikasi.

## Daftar Method Test & Penjelasan Detail

### 1. `test_rolls_back_entirely_on_mid_transaction_failure` [✅ **PASS**]
- **Deskripsi**: Menjamin atomisitas transaksi (All or Nothing).
- **Logika**: Mensimulasikan crash database tepat setelah baris pertama jurnal tersimpan namun sebelum transaksi selesai.
- **Verifikasi**: Assert bahwa tidak ada data sampah (partial data) yang tertinggal di tabel `journals` maupun `journal_lines` (Rollback sukses).

### 2. `test_batch_posting_is_idempotent_on_partial_failure` [✅ **PASS**]
- **Deskripsi**: Kemampuan menjalankan ulang proses batch yang terhenti tanpa menyebabkan double-post.
- **Logika**: Menjalankan `processScheduledJournals` dua kali berturut-turut.
- **Verifikasi**: Assert pada pemanggilan kedua, jumlah jurnal yang diproses adalah 0 karena semuanya sudah dalam status `POSTED` (Idempotency).

---
*Dihasilkan secara otomatis oleh Antigravity.*
