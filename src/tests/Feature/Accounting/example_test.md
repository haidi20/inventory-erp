# Dokumentasi Pengujian ExampleTest

File ini mendokumentasikan pengujian dasar level fitur (feature-level) untuk memastikan aplikasi dapat berjalan dan merespon permintaan HTTP standar dengan benar. Pengujian ini menggunakan seeder kontrol akses dan otorisasi.

---

## Struktur Kelas

- **`ExampleTest`** — Pengujian dasar integritas aplikasi.
- Menggunakan trait `RefreshDatabase`.
- Melibatkan `RbacSeeder` untuk inisialisasi permission dan role.

---

## Daftar Skenario Pengujian

### 1. `test_the_application_returns_a_successful_response`
- **Tujuan:** Memastikan halaman utama (homepage) aplikasi dapat diakses (status 200) oleh user yang sudah ter-autentikasi dan memiliki hak akses dashboard.
- **Proses:** 
    - Melakukan `seed(RbacSeeder::class)`.
    - Membuat Cabang dan User testing.
    - Menugaskan role `admin` dan permission `dashboard.view`.
    - Mengakses URL `/` (root).
- **Ekspektasi:** HTTP response status harus 200 (Success).

---

## Ringkasan Cakupan

| Kategori | Fitur yang Diuji | Validasi Utama |
|---|---|---|
| Smoke Test | HTTP Response | Status 200 di Halaman Utama |
| RBAC | Auth & Role | Akses User Admin |

**Total: 1 test case, assertions pada HTTP status.**

## Cara Menjalankan
```bash
php artisan test tests/Feature/Accounting/ExampleTest.php
```
