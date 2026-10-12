# **Hasil Pengujian Backup & Restore**

Dokumen ini mencatat detail hasil pengujian E2E (End-to-End) dan Unit Test untuk modul Backup & Restore setelah dilakukan standardisasi fitur tabel dan penambahan dukungan restorasi paket lengkap (.zip) yang berisi basis data serta dokumen/aset.

---

## **Sistem Otorisasi (Owner/Admin)**
- **Status**: ✅ **TERVERIFIKASI**
- **Detail**: Halaman Backup dapat diakses oleh user dengan role `owner` dan `admin`.
- **Hasil**: Menu Backup muncul di sidebar dan halaman memuat riwayat data dengan benar menggunakan Token API yang valid.

---

## **Fitur & Interaksi Tabel (E2E Playwright)**
Tes berikut dijalankan pada file `backup.view.spec.ts` menggunakan Playwright:

| ID | Deskripsi Pengujian | Status | Catatan |
|---|---|---|---|
| **B-1** | Menampilkan Riwayat & Pencarian | ✅ BERHASIL | Tabel otomatis memuat riwayat saat halaman dibuka. |
| **B-2** | Pencadangan Manual (Ekspor SQL) | ✅ BERHASIL | Tombol memicu API ekspor dengan pesan sukses (Toast). |
| **B-3** | Restorasi Paket Lengkap (Data + Dokumen) | ✅ BERHASIL | Berhasil mengekstrak Zip, memulihkan database, dan sinkronisasi file aset. |
| **B-4** | Pengurutan Sisi Server (Nama/Ukuran) | ✅ BERHASIL | Klik header memicu request API dengan parameter `order_column`. |
| **B-5** | Pengurutan Sisi Server (Tanggal) | ✅ BERHASIL | Pengurutan berdasarkan waktu pencadangan berjalan akurat. |
| **B-6** | Pilihan Baris per Halaman (10, 25, 50) | ✅ BERHASIL | UI merespons perubahan limit dan memuat ulang data. |
| **B-7** | Navigasi Paginasi | ✅ BERHASIL | Navigasi ke halaman 2+ mengirimkan parameter `page` yang sesuai. |
| **B-8** | Filter Jenis Riwayat (Manual/Sistem)| ✅ BERHASIL | Dropdown memisahkan riwayat manual dan otomatis. |
| **B-9** | Persistensi Modal | ✅ BERHASIL | Modal restore tetap terbuka dengan status memproses hingga selesai. |
| **B-10**| Pengunduhan File Cadangan | ✅ BERHASIL | Aksi unduh pada baris riwayat berhasil mengunduh file cadangan. |

---

### **Simulasi Pengujian Detail (Langkah-demi-Langkah)**

| ID | Skenario Simulasi | Detail Langkah Pengujian | Kriteria Keberhasilan | Status |
|:---:|:---|:---|:---|:---:|
| **SIM-1** | **Pemuatan Data Awal** | Akses Menu Backup, tunggu API `history-datatable` selesai. | Tabel terisi riwayat. Tidak ada pesan "Data Kosong" yang salah. | ✅ BERHASIL |
| **SIM-2** | **Pencarian Global** | Masukkan kata kunci "manual" di kotak pencarian utama. | Tabel hanya menampilkan baris dengan nama mengandung "manual". | ✅ BERHASIL |
| **SIM-3** | **Sortir Nama File** | Klik header kolom "Nama File". | Ikon sortir muncul, data diurutkan secara alfabetis (A-Z/Z-A). | ✅ BERHASIL |
| **SIM-4** | **Sortir Ukuran** | Klik header kolom "Ukuran". | Data diurutkan berdasarkan kapasitas file (Besar ke Kecil/sebaliknya). | ✅ BERHASIL |
| **SIM-5** | **Sortir Tanggal** | Klik header kolom "Tanggal Dibuat". | Data diurutkan berdasarkan kronologi waktu pembuatan. | ✅ BERHASIL |
| **SIM-6** | **Ubah Limit Halaman** | Ubah dropdown "Per Halaman" ke angka 25. | Tabel memuat 25 baris data (jika tersedia). | ✅ BERHASIL |
| **SIM-7** | **Navigasi Halaman** | Klik nomor halaman "2" di bagian paginasi. | Data halaman kedua dimuat, teks "Menampilkan X-Y" diperbarui. | ✅ BERHASIL |
| **SIM-8** | **Alur Cadangan Manual** | Klik "Ciptakan Cadangan Sekarang". | Muncul proses loading, lalu muncul Toast sukses, tabel otomatis diperbarui. | ✅ BERHASIL |
| **SIM-9** | **Buka Modal Restore** | Klik tombol "Buka Restore". | Modal terbuka dengan judul "Restore Cadangan". | ✅ BERHASIL |
| **SIM-10**| **Validasi Format Arsip** | Pilih file paket cadangan (.zip) untuk diunggah. | Sistem mengenali paket arsip sistem (Data + Dokumen). | ✅ BERHASIL |
| **SIM-11**| **Unggah ZIP (Data+Aset)**| Pilih file .zip berisi basis data + dokumen/gambar. | Sistem mendeteksi paket arsip dan mengaktifkan tombol restore. | ✅ BERHASIL |
| **SIM-12**| **Restorasi Sesuai Jenis** | Jalankan restore dari file paket arsip. | Data dipulihkan, muncul pesan "Restore Cadangan Berhasil". | ✅ BERHASIL |
| **SIM-13**| **Restorasi Aset Terintegrasi**| Cek folder storage setelah restore ZIP. | File dokumen/gambar dari ZIP berhasil pindah ke folder storage sistem. | ✅ BERHASIL |
| **SIM-14**| **UI Modal Restorasi** | Klik tombol "Buka Restore". | Modal muncul dengan judul "Restore Cadangan" dan input yang bersih. | ✅ BERHASIL |
| **SIM-15**| **Centang Konfirmasi** | Klik submit tanpa mencentang prasyarat. | Tombol "Mulai Restore" tetap nonaktif hingga persetujuan dicentang. | ✅ BERHASIL |
| **SIM-16**| **Aksi Pengunduhan** | Klik ikon unduh pada salah satu baris data riwayat. | Browser memulai pengunduhan file cadangan yang dipilih. | ✅ BERHASIL |
| **SIM-17**| **Ganti Jenis Riwayat** | Ubah filter "Jenis" ke pilihan "Sistem". | Tabel hanya menampilkan riwayat yang dibuat otomatis oleh sistem. | ✅ BERHASIL |

---

## **Kesehatan Kode (Maintainability)**

### **Analisis Statis & Build**
| Cek | Perintah | Status | Catatan |
|---|---|---|---|
| **Typecheck** | `npm run typecheck` | ✅ SUKSES | Tidak ditemukan error TypeScript di modul Backup. |
| **Build** | `npm run build` | ✅ SUKSES | Bundle Vite berhasil dibuat dengan chunking yang optimal. |

### **Uji Unit & Integrasi (Vitest & PHPUnit)**
Logika internal diuji secara terpisah dari UI untuk kecepatan dan akurasi:
- **Repository Pattern**: `backup.repo_impl.spec.ts` ➔ ✅ **BERHASIL**
- **Logika Ekstraksi Zip (Real)**: `BackupZipArchiveFlowTest.php` ➔ ✅ **BERHASIL (100%)**
- **Validation Entities**: Semua entity pendukung ➔ ✅ **BERHASIL**

> [!IMPORTANT]
> Seluruh pengujian E2E menggunakan akun `admin@klinik.local`. Sistem restorasi telah diuji secara **"Real"** (tanpa mocking) menggunakan integrasi PHPUnit, memverifikasi bahwa file ZIP asli berhasil diekstrak dan dipulihkan ke basis data serta sistem penyimpanan storage.
