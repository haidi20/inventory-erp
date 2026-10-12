# Dokumentasi Testing: JournalDisasterRecoverySimulationTest

## Deskripsi
Simulasi pemulihan data dan pembatalan transaksi (Voiding/Reversal) dalam kondisi kritis atau kesalahan input.

## Daftar Method Test & Penjelasan Detail

### 1. `test_void_journal_creates_reversal_and_updates_status` [✅ **PASS**]
- **Deskripsi**: Menghapus dampak finansial jurnal dengan membuat jurnal pembalik otomatis.
- **Logika**: Memanggil fungsi `voidJournal()`.
- **Verifikasi**: 
    - Assert status jurnal asli menjadi `voided`.
    - Assert sistem membuat jurnal baru dengan Debit/Kredit yang dibalik (Reversal).

### 2. `test_cannot_reverse_already_reversed_journal` [✅ **PASS**]
- **Deskripsi**: Mencegah double-reversal yang dapat merusak saldo.
- **Verifikasi**: Assert gagal saat mencoba me-reverse jurnal yang statusnya sudah `reversed`.

### 3. `test_cannot_void_in_closed_period` [✅ **PASS**]
- **Deskripsi**: Larangan pembatalan jurnal jika periode akuntansi sudah dikunci (Audit Lock).
- **Verifikasi**: Assert melempar `Exception` saat periode diubah menjadi `closed`.

---
*Dihasilkan secara otomatis oleh Antigravity.*
