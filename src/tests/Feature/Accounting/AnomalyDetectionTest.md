# Dokumentasi Testing: AnomalyDetectionTest

## Deskripsi
Memastikan sistem keamanan dan audit log bekerja dengan benar untuk mendeteksi tindakan tidak wajar atau pelanggaran prosedur akuntansi.

## Daftar Method Test & Penjelasan Detail

### 1. `test_scenario_12_closed_period_restriction` [✅ **PASS**]
- **Deskripsi**: Verifikasi bahwa tidak ada perubahan (seperti generate jurnal penyesuaian) yang diizinkan pada periode yang sudah ditutup.
- **Verifikasi**: Assert exception dilempar saat mencoba memproses periode `closed`.

### 2. `test_audit_log_recording` [✅ **PASS**]
- **Deskripsi**: Memastikan setiap upaya (baik berhasil maupun gagal) tercatat dalam audit log sistem.
- **Verifikasi**: Verifikasi fungsionalitas pencatatan log (mocked/simple check).

---
*Dihasilkan secara otomatis oleh Antigravity.*
