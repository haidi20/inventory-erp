# Dokumentasi Testing: JournalSecurityAdvanceTest

## Deskripsi
Implementasi kontrol keamanan akses tingkat lanjut, termasuk pemisahan tugas (Segregation of Duties).

## Daftar Method Test & Penjelasan Detail

### 1. `test_inputer_cannot_approve_own_journal` [✅ **PASS**]
- **Deskripsi**: Mencegah Fraud dengan melarang orang yang sama membuat DAN menyetujui jurnal miliknya sendiri.
- **Verifikasi**: Assert melempar error "Segregation of Duties Violation" saat inputer mencoba memanggil fungsi approve.

### 2. `test_different_user_can_approve_journal` [✅ **PASS**]
- **Deskripsi**: Alur normal dimana Manager menyetujui jurnal staff.
- **Verifikasi**: Assert status workflow berpindah secara benar ketika diproses oleh user yang berbeda.

---
*Dihasilkan secara otomatis oleh Antigravity.*
