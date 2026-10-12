# Dokumentasi Testing: JournalAuditTrailTest

## Deskripsi
Memastikan setiap aktivitas pembuatan atau perubahan jurnal dicatat dalam log audit (Audit Trail) untuk akuntabilitas data.

## Daftar Method Test & Penjelasan Detail

### 1. `test_audit_log_captured_on_create` [✅ **PASS**]
- **Deskripsi**: Menjamin sistem mencatat event "created" beserta payload datanya.
- **Logika**: Membuat jurnal menggunakan `JournalService`.
- **Verifikasi**: 
    - Assert tabel `audit_logs` berisi record dengan `event = created` dan `entity_id` yang sesuai.
    - Assert payload `after` pada log menyimpan informasi krusial seperti `reference_no`.

---
*Dihasilkan secara otomatis oleh Antigravity.*
