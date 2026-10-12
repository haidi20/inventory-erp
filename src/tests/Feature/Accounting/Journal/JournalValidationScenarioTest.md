# Dokumentasi Testing: JournalValidationScenarioTest

## Deskripsi
Kumpulan skenario validasi bisnis untuk mencegah kesalahan input data master pada transaksi jurnal (seperti akun Header atau akun Inaktif).

## Daftar Method Test & Penjelasan Detail

### 1. `test_cannot_post_to_header_account` [✅ **PASS**]
- **Deskripsi**: Akun Header (seperti "ASET") tidak boleh menampung transaksi langsung.
- **Logika**: Mencoba membuat jurnal menggunakan `account_id` yang memiliki `is_header = true`.
- **Verifikasi**: Assert melempar `Exception` dengan pesan "Cannot post to header account".

### 2. `test_cannot_post_to_inactive_account` [✅ **PASS**]
- **Deskripsi**: Mencegah penggunaan akun yang sudah dinonaktifkan.
- **Verifikasi**: Assert melempar `Exception` "Account is inactive".

### 3. `test_can_post_to_active_non_header_account` [✅ **PASS**]
- **Deskripsi**: Sanity check untuk memastikan akun normal yang aktif tetap dapat digunakan.
- **Verifikasi**: Assert jurnal berhasil terbentuk (2 baris lines).

---
*Dihasilkan secara otomatis oleh Antigravity.*
