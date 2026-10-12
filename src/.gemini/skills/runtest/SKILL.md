---
name: runtest
description: Menjalankan test suite dan memperbaiki kegagalan satu per satu secara sekuensial (per file). Gunakan skill ini ketika ada banyak kegagalan test dan diperlukan perbaikan yang teliti dan bertahap hingga semua test lulus.
---

# RunTest (Sequential Fix & Verify)

Skill ini digunakan untuk membereskan kegagalan test secara sistematis. Alur utamanya adalah: Temukan kegagalan -> Isolasi file -> Perbaiki -> Verifikasi -> Lanjut ke file berikutnya.

## 🛠️ Langkah-Langkah Kerja

### 1. Identifikasi Kegagalan Pertama
Gunakan perintah berikut untuk menemukan file pertama yang gagal tanpa menunggu seluruh suite selesai:
```bash
$env:APP_ENV='testing'; php artisan test --stop-on-failure
```
Atau jika ingin melihat semua daftar kegagalan di awal:
```bash
$env:APP_ENV='testing'; php artisan test --no-coverage
```

### 2. Isolasi File yang Gagal
**WAJIB**: Jangan mencoba memperbaiki banyak file sekaligus. Fokus hanya pada **satu file** yang gagal.
Jalankan test hanya untuk file tersebut untuk mengonfirmasi error:
```bash
$env:APP_ENV='testing'; php artisan test <path/to/FailTest.php>
```

### 3. Analisa dan Perbaikan
- Baca pesan error dengan teliti.
- Gunakan `read_file` untuk melihat kode test dan kode sumber (controller/repository/service/entity) yang terkait.
- Lakukan perbaikan secara terukur pada kode sumber atau test (jika asersinya salah).

### 4. Verifikasi Lokal (Per File)
Jalankan ulang file test yang sedang dikerjakan:
```bash
$env:APP_ENV='testing'; php artisan test <path/to/FailTest.php>
```
- Jika masih **FAIL**: Ulangi langkah 3.
- Jika sudah **PASS**: Lanjut ke langkah 5.

### 5. Lanjut ke Kegagalan Berikutnya
Ulangi langkah 1 untuk menemukan target file berikutnya. Terus lakukan ini sampai perintah `php artisan test` memberikan hasil hijau (100% PASS).

## ⚠️ Aturan Penting
- **DILARANG** menjalankan test secara paralel atau banyak file sekaligus saat proses perbaikan sedang berlangsung.
- **DILARANG** melakukan perbaikan massal tanpa verifikasi per file.
- Setiap file yang sudah diperbaiki dan lulus harus dilaporkan progress-nya kepada user.
- Jika menemui error yang berulang di banyak file (misal: skema database), perbaiki akarnya terlebih dahulu, lalu verifikasi satu per satu file yang terdampak.
