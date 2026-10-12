# Dokumentasi Testing: SegmentReportTest

## Deskripsi
Memastikan laporan keuangan dapat difilter per segmen (Cabang) dan datanya terisolasi dengan benar antara satu cabang dengan cabang lainnya.

## Daftar Method Test & Penjelasan Detail

### 1. `test_segment_report_isolation` [✅ **PASS**]
- **Deskripsi**: Verifikasi isolasi data antar cabang di laporan Laba Rugi.
- **Logika**: Menginput transaksi di Branch A dan Branch B, lalu memverifikasi laporan masing-masing cabang.

### 2. `test_segment_report_unauthorized_access` [✅ **PASS**]
- **Deskripsi**: Verifikasi keamanan akses silang cabang.
- **Verifikasi**: Assert `UnauthorizedException` muncul jika user Branch A mencoba akses laporan Branch B.

---
*Dihasilkan secara otomatis oleh Antigravity.*
