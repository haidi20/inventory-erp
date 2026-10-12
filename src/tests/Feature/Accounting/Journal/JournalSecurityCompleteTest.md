# Dokumentasi Testing: JournalSecurityCompleteTest

## Deskripsi
Memperkuat keamanan sistem akuntansi melalui validasi ketat pada field sensitif (Fiskal) dan kontrol periode akuntansi yang sudah dikunci (Audit Lock).

## Daftar Method Test & Penjelasan Detail

### 1. `test_fiscal_correction_tagging` [✅ **PASS**]
- **Deskripsi**: Validasi integritas data untuk baris yang ditandai sebagai Koreksi Fiskal.
- **Logika**: Mencoba menyimpan baris `is_fiscal = true` tanpa menentukan `fiscal_type` (positive/negative).
- **Verifikasi**: Assert sistem menolak transaksi dengan pesan "Tipe Fiskal (positive/negative) wajib".

### 2. `test_cannot_post_to_closed_period` [✅ **PASS**]
- **Deskripsi**: Pencegahan manipulasi data pada periode sejarah yang sudah diaudit/ditutup.
- **Logika**: Mengatur status periode menjadi `closed` lalu mencoba melakukan posting jurnal pada tanggal di periode tersebut.
- **Verifikasi**: Assert melempar `Exception` keamanan "Periode ... sudah ditutup".

### 3. `test_audit_trail_payload_mapping` [✅ **PASS**]
- **Deskripsi**: Menjamin transparansi audit dengan mencatat payload lengkap (Before/After).
- **Verifikasi**: Assert record di tabel `audit_logs` menyimpan salinan data jurnal (seperti nomor referensi) saat event `created` dipicu.

---
*Dihasilkan secara otomatis oleh Antigravity.*
