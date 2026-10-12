# Dokumentasi Pengujian: Role Repository (Real Integration Test)

File Pengujian: `Role.repo_impl.spec.ts`

Pengujian ini menggunakan konsep **Integration Testing** yang sesungguhnya (bukan mock), di mana setiap request dikirim langsung ke server Laravel aktif (`php artisan serve`) dan berinteraksi langsung dengan database asli melalui API yang diamankan dengan JWT (Spatie JWT).

## Prasyarat Jalankan Test
1. Server Laravel harus berjalan di `http://127.0.0.1:8000`.
2. Database sudah di-seed dengan data awal (terutama Role `owner@klinik.local`).
---

## Daftar Skenario Pengujian

### 1. Fitur Server-Side (Inertia Integration)
**Deskripsi:** Menguji integrasi repository dengan Inertia router untuk fitur tabel dinamis.
- **Pencarian Spesifik Kolom:** Menguji method `search()` dengan parameter query dan kolom (menggunakan `RoleSearchColumn` enum). Memastikan memicu navigasi Inertia dengan filter yang tepat.
- **Pengaturan Per-Page:** Menguji method `changePerPage()` untuk mengubah jumlah data yang ditampilkan. Memastikan reset ke halaman 1 saat jumlah per-page berubah.
- **Paginasi Server-Side:** Menguji method `changePage()` untuk berpindah antar halaman data.

### 2. Siklus Hidup CRUD Lengkap
**Deskripsi:** Menguji integritas data satu record Role dari awal hingga akhir dalam satu alur proses.
- **Tambah (Create):** Mengirim data Role baru ke API dan memastikan status sukses serta mendapatkan objek Role lengkap (termasuk UUID).
- **Read (Verify Create):** Melakukan pencarian via API GET untuk memastikan data benar-benar tersimpan di DB.
- **Edit (Update):** Mengubah nama Role tersebut menggunakan UUID yang didapat dan memastikan perubahan tersimpan.
- **Hapus (Delete):** Menghapus Role tersebut dari database.
- **Verifikasi Akhir:** Memastikan data sudah tidak ditemukan lagi di database.

### 3. Unduh Template Impor (Real Blob)
... (sisanya tetap) ...

**Deskripsi:** Memastikan fungsionalitas pengunduhan file template Excel (`.xlsx`) berjalan dengan benar.
- Melakukan request ke API dengan `responseType: 'blob'`.
- Memastikan server mengirimkan file yang valid.
- Memverifikasi bahwa sistem memicu fungsi `createObjectURL` untuk proses download di browser.

### 3. Ekspor Data Role (Real Blob)
**Deskripsi:** Menguji fitur ekspor seluruh atau sebagian data Role berdasarkan filter tertentu.
- Mengirim filter pencarian ke API.
- Memastikan respons berupa file binary (Blob).
- Memverifikasi integrasi dengan browser API untuk pengunduhan file hasil ekspor.

### 4. Impor Role via File (Real Multipart)
**Deskripsi:** Menguji proses unggah file Excel untuk impor data massal.
- Membuat objek `File` asli secara programatik.
- Mengirim file tersebut menggunakan `FormData` (Multipart/form-data) ke API.
- Memastikan server menerima dan memproses request tersebut tanpa error koneksi.

### 5. Validasi Error Asli dari Server
**Deskripsi:** Memastikan penanganan error validasi (status 422) berfungsi sesuai standar Laravel.
- Mencoba membuat Role dengan email yang sudah terdaftar (duplikat).
- Memastikan error yang dilempar oleh repository mengandung detail pesan kesalahan asli dari database/server Laravel.
- Memastikan pemetaan (mapping) error dari Axios ke format internal repository berjalan benar.

