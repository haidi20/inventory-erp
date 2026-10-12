# Analisis Halaman: Master Nilai (Daftar Nilai)

**URL:** https://web.ninesoft.id/site/master/nilai  
**Aplikasi:** Ninesoft Web Accounting  
**Database:** `ninesoft.sql` — database `rbn` (MySQL 5.5)  
**Tanggal Analisis:** 2026-09-09

---

## Deskripsi

Halaman **Master Nilai** (Daftar Nilai) adalah halaman konfigurasi lookup/referensi data master yang digunakan di seluruh modul aplikasi akuntansi. Berfungsi sebagai tabel kode nilai (code table) yang menyimpan daftar pilihan standar untuk field-field di seluruh sistem.

---

## Struktur Halaman

### Layout
- Header dengan navigasi dan user menu
- Panel filter di sisi kanan (collapsible via tombol `myParameter()`)
- Tabel data utama (DataTables server-side) di sisi kiri

### Komponen Utama
| Komponen | Keterangan |
|---|---|
| DataTable | Server-side rendering, sortable, pagination |
| Filter Panel | Collapsible, posisi kanan |
| Row Expand | Klik kolom pertama untuk lihat detail nilai |
| Inline Edit | Sel tabel bisa diedit langsung (EDITABLE rows) |

---

## Tabel Utama

### Kolom
| # | Nama Kolom | Keterangan |
|---|---|---|
| 1 | (expand) | Toggle detail sub-item |
| 2 | STATUS | `EDITABLE` (hijau) atau `READONLY` (merah) |
| 3 | NAMA MASTER | Nama grup/kategori nilai |
| 4 | KETERANGAN | Deskripsi singkat |
| 5 | AKSI | Tombol view/edit detail |

### Detail Sub-tabel (Row Expand)
Setiap master memiliki sub-item dengan kolom:

| Nilai | Keterangan | Default | Urutan | Sejak Tanggal | Sampai Tanggal | Aktif |

---

## Filter

| Field | Tipe | Sumber Data |
|---|---|---|
| Nama Master | Text input | Manual |
| Keterangan | Text input | Manual |
| Created By | Select2 | AJAX: `/site/search/filter/createdby` |
| Update By | Select2 | AJAX: `/site/search/filter/updateby` |
| Created Date | Datepicker | Manual |
| Update Date | Datepicker | Manual |
| Periode | Select2 | AJAX: `/site/search/filter/periode` |

---

## API Endpoints

| Method | URL | Fungsi |
|---|---|---|
| POST | `/site/master/nilai/record/` | Load data DataTable (server-side) |
| GET | `/site/master/nilai/view/{id}` | View/edit detail master nilai |
| POST | `/site/master/user/modal_chpass` | Modal ganti password user |
| GET | `/site/search/filter/createdby` | Data filter created by (Select2 AJAX) |
| GET | `/site/search/filter/updateby` | Data filter updated by (Select2 AJAX) |
| GET | `/site/search/filter/periode` | Data filter periode (Select2 AJAX) |
| GET | `/site/home/dashboard/UpdateDB` | Cek update database |

---

## Data Master dari `ninesoft.sql` (24 Record)

> Data ini diambil langsung dari tabel `erp_lookup_set` dan `erp_lookup_value` di `ninesoft.sql`.

| ID | Nama Master | Status | Jumlah Item | Item |
|---|---|---|---|---|
| 48 | GRADE | EDITABLE | 5 | A, D, E, A+, NOPPN |
| 49 | MADE IN | EDITABLE | 6 | TAIWAN, CHINA, US, JAPAN, GERMAN, INDONESIA |
| 52 | JENIS | READONLY | 3 | BARANG, JASA, UANG MUKA |
| 53 | KELOMPOK | EDITABLE | 4 | BAHAN PENDUKUNG, BAHAN BAKU, ACCESSORIES, BAHAN JADI |
| 54 | TIPE | EDITABLE | 4 | BARANG JADI, JASA KONTRAKTOR, JASA SUBKON, UPAH |
| 55 | MERK | EDITABLE | 2 | SONY, LG |
| 56 | STATUS DOKUMEN | READONLY | 17 | SYSTEM, COMPLETE, NEW, PARTIAL, LUNAS, BELUM LUNAS, DELETE, CLOSED, CAIR, TOLAKAN, POSTED, APPROVED, CLOSED PO, ERROR, CANCEL APPROVED, TERTAGIH, BATAL |
| 57 | REASON | EDITABLE | 8 | HILANG, DICURI, KETEMU, BONUS, SALDO AWAL, DAUR ULANG, KEMBALI, PENGGUNAAN |
| 62 | BAGIAN | EDITABLE | 3 | SALES, GUDANG, ADMINISTRASI |
| 63 | TIPE INVOICE | READONLY | 3 | MATCHING PO, STANDARD, UANG MUKA |
| 65 | TIPE ACCOUNT | READONLY | 16 | AKTIVA LANCAR, AKTIVA TETAP, KAS/BANK, MODAL, KEWAJIBAN LANCAR, dll |
| 66 | METODE DEPRESIASI | READONLY | 3 | NON DEPRECIABLE, STRAIGHT LINE, DOUBLE DECLINING METHOD |
| 67 | TIPE ASSET | EDITABLE | 7 | BANGUNAN, KENDARAAN, PERALATAN PRODUKSI, INVENTARIS KANTOR, dll |
| 68 | VIA KIRIM | EDITABLE | 0 | - |
| 69 | CHOISE | EDITABLE | 0 | - |
| 70 | TIPE_PAJAK | READONLY | 2 | PPN, PPH |
| 71 | TIPE PERUSAHAAN | EDITABLE | 0 | - |
| 72 | JENIS PEKERJAAN | EDITABLE | 2 | PROSES, URGENT |
| 73 | NAMA KENDARAAN | EDITABLE | 1 | PICKUP |
| 75 | NO PAJAK | EDITABLE | 4 | 23612350, 23612419, 23610832, 010.019- |
| 81 | JENIS GUDANG | EDITABLE | 3 | TOKO, GUDANG CABANG, TRANSIT |
| 101 | EKSPEDISI | EDITABLE | 0 | - |
| 103 | PENGIRIMAN | EDITABLE | 2 | DIKIRIM, AMBIL SENDIRI |
| 104 | TIPE CUSTOMER | EDITABLE | 2 | LUAR KOTA, DALAM KOTA |

---

## Perbedaan Web vs SQL

| Aspek | Web Scraping | ninesoft.sql |
|---|---|---|
| Jumlah record | 18 | 24 |
| GRADE items | 9 (termasuk BHP, BAHAN LAB, dll) | 5 (A, D, E, A+, NOPPN) |
| KELOMPOK items | banyak | 4 |
| Ada di web tapi tidak di SQL | E-ORDER, DURASI WARNING | - |
| Ada di SQL tapi tidak di web | TIPE, MERK, BAGIAN, TIPE INVOICE, TIPE ACCOUNT, TIPE_PAJAK, JENIS PEKERJAAN, NAMA KENDARAAN, JENIS GUDANG, EKSPEDISI, PENGIRIMAN, TIPE CUSTOMER | - |

> Kesimpulan: Data web adalah data demo/live yang sudah dimodifikasi. Data `ninesoft.sql` adalah snapshot awal yang lebih minimal.

---

## Struktur Database Ninesoft

### Tabel `erp_lookup_set`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `ERP_LOOKUP_SET_ID` | int(11) PK | ID auto increment |
| `PROGRAM_CODE` | varchar(40) UNIQUE | Kode program |
| `ERP_LOOKUP_SET_NAME` | varchar(40) UNIQUE | Nama master |
| `DESCRIPTION` | varchar(80) | Deskripsi |
| `USER_CAN_EDIT_FLAG` | char(1) | Y = EDITABLE, N = READONLY |
| `CREATED_BY` | int(11) | ID user pembuat |
| `CREATED_DATE` | datetime | Tanggal dibuat |
| `LAST_UPDATE_BY` | int(11) | ID user update terakhir |
| `LAST_UPDATE_DATE` | datetime | Tanggal update terakhir |

### Tabel `erp_lookup_value`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `ERP_LOOKUP_VALUE_ID` | int(11) PK | ID auto increment |
| `ERP_LOOKUP_SET_ID` | int(11) FK | Relasi ke erp_lookup_set |
| `PROGRAM_CODE1` | varchar(15) UNIQUE | Kode program item |
| `DISPLAY_NAME` | varchar(80) | Nama nilai (unique per set) |
| `SEQ` | int(3) | Urutan tampil |
| `DESCRIPTION` | varchar(240) | Keterangan |
| `START_DATE` | date | Sejak tanggal berlaku |
| `END_DATE` | date | Sampai tanggal berlaku |
| `PRIMARY_FLAG` | char(1) | Y = default/primary |
| `ACTIVE_FLAG` | char(1) | Y = aktif |
| `PRICE_UP_BELI` | varchar(50) | Rumus harga beli |
| `PRICE_UP_1` s/d `PRICE_UP_10` | varchar(50) | Rumus harga jual 1-10 |
| `PERSEN_BELI` | varchar(50) | Persentase beli |
| `PERSEN1` s/d `PERSEN10` | varchar(50) | Persentase harga 1-10 |
| `DISKON_PILIH` | varchar(50) | Diskon pilihan |
| `DISKON_FLAG` | char(1) | Flag diskon |
| `BULAT` | decimal(17,5) | Pembulatan |

---

## Mapping ke Project Laravel

| Ninesoft | Project | Keterangan |
|---|---|---|
| `erp_lookup_set` | `lookup_sets` | Master grup nilai |
| `erp_lookup_value` | `lookup_values` | Item nilai per grup |
| `USER_CAN_EDIT_FLAG` | `is_editable` | boolean |
| `PRIMARY_FLAG` | `is_default` | boolean |
| `ACTIVE_FLAG` | `is_active` | boolean |
| `DISPLAY_NAME` | `value` | Nama nilai |
| `SEQ` | `sort_order` | Urutan |
| `START_DATE` | `valid_from` | Tanggal mulai |
| `END_DATE` | `valid_to` | Tanggal berakhir |

> Catatan: `erp_lookup_value` menyimpan konfigurasi pricing per grade (PRICE_UP_1 s/d PRICE_UP_10) — fitur ini perlu dipertimbangkan saat implementasi `lookup_values` di project.

---

## Fitur Inline Edit

Baris dengan status **EDITABLE** mendukung inline edit langsung di tabel:
- Klik sel untuk masuk mode edit
- Input muncul di dalam sel
- Tab/Enter untuk pindah ke sel berikutnya
- Auto-save ke server via AJAX saat blur

Baris **READONLY** tidak bisa diedit dari UI.

---

## Teknologi yang Digunakan

| Teknologi | Keterangan |
|---|---|
| DataTables | Server-side, dengan row expand |
| Select2 | Dropdown dengan AJAX search |
| Bootstrap | v3 (glyphicons) |
| jQuery | 1.8.2 |
| Bootstrap Datepicker | Format dd-mm-yyyy |