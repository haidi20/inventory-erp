# Scenario 23: Validasi Akun Non-Header dan Akun Tidak Aktif

## Deskripsi
Memastikan sistem menolak entri jurnal yang menggunakan akun header (induk) atau akun yang sudah dinonaktifkan.

## Objective
- Proteksi integritas data ledger.
- Mencegah posting ke akun yang hanya berfungsi sebagai pengelompokan (Header).
- Mencegah posting ke akun yang sudah ditutup/tidak aktif.

## File Testing
- `JournalNonHeaderValidationTest.php`

## Skenario Uji
1. **test_cannot_post_to_header_account**: Mencoba membuat jurnal ke akun `is_header = true`. Harus melempar exception "Cannot post to header account".
2. **test_cannot_post_to_inactive_account**: Mencoba membuat jurnal ke akun `is_active = false`. Harus melempar exception "Account is inactive".
