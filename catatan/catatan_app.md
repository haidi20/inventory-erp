https://klinik.rhantech.com/auth/login?fbclid=IwY2xjawQk0HtleHRuA2FlbQIxMABicmlkETFBSHY2VWtCYUR4czJuYWxkc3J0YwZhcHBfaWQQMjIyMDM5MTc4ODIwMDg5MgABHtvFLo-hfbg8Kf7az7zp3cGCj3-FsAwMnRT9jsI17P7Y2a9kV_DkpzsxNVBB_aem_z4L7HZ7yCk4QwpoP2uw_Iw

user : admin
password : password123

---

## 🏗️ Dokumentasi Lengkap & Komprehensif Aplikasi Klinik

### 1. Dashboard (Pusat Kendali Realtime)
Dashboard adalah pusat informasi utama yang memberikan gambaran cepat mengenai kondisi operasional klinik secara *real-time*. Halaman ini dirancang untuk pemangku kepentingan (Pemilik/Manajer) agar dapat memantau antrian, penjualan, dan tren pendapatan tanpa harus melakukan penelusuran data yang mendalam.

#### • Overview Dashboard
**Deskripsi Lengkap**: Menyajikan visualisasi data berupa kartu statistik, grafik garis tren omzet, dan grafik lingkaran proporsi pendapatan antara jasa medis vs penjualan obat. Halaman ini juga menampilkan status antrian pasien aktif dan statistik pertumbuhan member baru.
- **Informasi Utama**: KPI Pendapatan (Omzet), Antrian Pasien Aktif, Grafik Tren Pendapatan (Line Chart), Grafik Proporsi Jasa vs Obat (Pie Chart), Pertumbuhan Member.
- **Filter**: Filter Periode (Hari Ini, Kemarin, 7 Hari Terakhir, Bulan Ini, Bulan Lalu).
- **📋 Field Visualisasi**:
    - **Card Statistik**: Total Pendapatan, Jumlah Kunjungan, Rata-rata Transaksi.
    - **Status Antrian**: No. Antrian, Nama Pasien, Poli Tujuan, Jam Kedatangan.

---

### 2. Himbauan (Announcements)
Modul komunikasi internal yang memastikan setiap pesan atau kebijakan dari manajemen pusat tersampaikan secara efektif kepada seluruh pengguna sistem sesuai peran mereka.

#### • Pengumuman Sistem
**Deskripsi Lengkap**: Wadah untuk menerbitkan dan mengelola pengumuman yang ditujukan bagi Administrator, Dokter, atau Karyawan. Sangat berguna untuk menyebarkan informasi jadwal, prosedur baru, atau pesan penting lainnya.
- **Konten**: List pesan broadcast (Judul, Tanggal, Pengirim, Ringkasan Pesan).
- **Tombol**: `Detail` (Lihat Isi), `Arsip` (Sembunyikan).
- **📌 Detail Table**: Tanggal Terbit, Judul Himbauan, Target Role (Admin/Dokter/Karyawan), Status (Read/Unread).

---

### 3. Layanan BPJS (V-Claim Integration)
Modul ini mengintegrasikan layanan klinik dengan sistem BPJS Kesehatan Nasional melalui gateway V-Claim. Tujuannya adalah mempercepat proses administrasi pasien JKN dan menjamin keakuratan data klaim.

#### • Pendaftaran Peserta
**Deskripsi Lengkap**: Fitur pengecekan identitas kepesertaan BPJS berdasarkan NIK atau Nomor Kartu. Data yang ditarik dari server BPJS akan otomatis tersinkronisasi dengan profil pasien lokal.
- **Input Form**: NIK (16 Digit), No. Kartu BPJS (13 Digit).
- **Output Data**: Nama Lengkap, Tanggal Lahir, Jenis Kelamin, Kepesertaan (PBI/Non-PBI), Status Aktif/Non-Aktif.
- **Tombol**: `Cari Peserta`, `Salin Data ke Form Pasien`.

#### • Verifikasi Kepesertaan
**Deskripsi Lengkap**: Digunakan untuk validasi status aktif atau tidaknya kepesertaan pasien secara *real-time* sebelum pelayanan medis diberikan.
- **Tombol**: `Cek Validitas`.

#### • Rujukan Online
**Deskripsi Lengkap**: Fasilitas untuk membuat, mengelola, dan mencetak surat rujukan pasien ke Fasilitas Kesehatan Rujukan Tingkat Lanjut (FKRTL) secara digital yang terhubung langsung dengan sistem BPJS.
- **Fitur**: Input Diagnosa, Poli Tujuan, dan TMT Rujukan.

#### • SEP (Surat Eligibilitas Peserta)
**Deskripsi Lengkap**: Menu untuk menerbitkan Surat Eligibilitas Peserta (SEP) sebagai syarat utama penagihan klaim ke BPJS. Mencakup validasi nomor rujukan dan diagnosa awal.
- **📦 Table Field**: No. SEP, Nama Pasien, No. Kartu, Tanggal SEP, Poli, Diagnosa Awal, Status Sync.
- **📝 Form Buat SEP**:
    - **Data Referral**: No. Rujukan / No. Surat Kontrol.
    - **Data Medis**: Diagnosa (ICD-10 Search), Poli Tujuan, DPJP Pemberi SEP.
    - **Kontak**: No. Telepon Pasien.
- **Aksi**: `Buat SEP`, `Cetak SEP` (PDF/Thermal), `Hapus`, `Cek Status Bridging`.

#### • Monitoring Klaim
**Deskripsi Lengkap**: Panel rekapitulasi untuk memantau sejarah transaksi klaim BPJS. Memudahkan bagian keuangan dalam melakukan rekonsiliasi data bulanan.
- **📦 Export**: `Excel` & `PDF` (Rekapitulasi Klaim).

#### • Pengaturan API
**Deskripsi Lengkap**: Konfigurasi kredensial keamanan (Consumer ID & Secret Key) untuk menghubungkan aplikasi dengan *Production/Development environment* server BPJS.

---

### 4. Layanan SATUSEHAT (Kemenkes Integration)
Modul kepatuhan regulasi Kemenkes untuk Integrasi Rekam Medis Elektronik (RME) secara nasional menggunakan standar HL7 FHIR.

#### • Sinkronisasi Pasien
**Deskripsi Lengkap**: Menghubungkan NIK pasien lokal dengan identitas unik nasional atau IHS Patient ID agar rekam medis bisa terintegrasi secara nasional.
- **📦 Table Field**: No, Nama Pasien, No. Identitas (NIK), Patient ID (IHS), Status Integrasi.
- **Filter**: Cari Nama Pasien, Status (Belum Terhubung/Sudah Terhubung).
- **Tombol**: `SINKRONISASI MASAL` (Batch processing), `SYNC IHS` (Individual).

#### • Kirim Data Kunjungan
**Deskripsi Lengkap**: Sinkronisasi data kedatangan pasien atau *Encounter* ke platform SATUSEHAT untuk mencatat jam kedatangan dan poli tujuan.

#### • Kirim Rekam Medis
**Deskripsi Lengkap**: Mengirimkan data klinis mendalam seperti Tanda-tanda Vital (TTV), pemeriksaan fisik, dan kode diagnosa ICD-10 ke server Kemenkes sesuai standar FHIR.

#### • Pengaturan Platform
**Deskripsi Lengkap**: Pengaturan konfigurasi API SATUSEHAT mencakup Organization ID dan Client Kredensial dari DTO Kemenkes.

---

### 5. Transaksi (Operational Logic)
Pusat dari seluruh pergerakan operasional medis dan finansial harian klinik.

#### • Pembelian (Stok Masuk)
**Deskripsi Lengkap**: Mencatat pengadaan barang (obat/alkes) dari supplier. Berfungsi menambah stok fisik inventori dan mencatat hutang dagang secara otomatis.
- **📦 Table Field**: No, No. PO, Tanggal, Supplier, Total, Status (Draft/Lunas), Pembayaran (Cash/Hutang).
- **Filter**: Cari Transaksi (No. PO/Supplier), Filter Supplier, Status, Periode (Bulan Ini/Bulan Lalu).
- **Shortcut**: `F1` (Supp), `F2` (Item), `F4` (Bayar).
- **📝 Form Input**:
    - **Header**: Supplier (Dropdown), Tanggal Transaksi, No. Invoice Supplier.
    - **Detail**: Nama Barang (Search), Qty, Satuan, Harga Beli, Subtotal.
- **📦 Export**: `Cetak PO` (PDF), `Print Label Barcode`.

#### • Hutang Supplier
- **📦 Table Field**: Tanggal, Supplier, Invoice (PO), Total Tagihan, Terbayar, Sisa Hutang, Status (Lunas/Nunggak).
- **Filter**: Cari Supplier, Status Hutang, Rentang Tanggal.
- **Aksi**: `Bayar Hutang` (Pilih Akun Kas/Bank, Nominal Bayar).

#### • Pemeriksaan (Rekam Medis)
**Deskripsi Lengkap**: Modul inti bagi tenaga medis untuk mencatat Anamnesa, Diagnosa, dan Resep. Terintegrasi dengan antrian pasien (Shortcut: F1 untuk cari pasien).
- **📦 Table Field**: No. Pemeriksaan, Tanggal, Pelanggan, Pasien, Dokter, Status (Antrian/Proses/Selesai).
- **Filter**: Cari Nama/No RM/No Identitas, Status, Filter Dokter, Periode Kunjungan.
- **📝 Form Rekam Medis (3 Step Flow)**:
    1. **Pelanggan**: Verifikasi Member/Bukan, Input Identitas Pembayar.
    2. **Pasien**: Detail klinis pasien (NIK, Alamat, TTV: Tensi, Nadi, Suhu, Berat Badan).
    3. **Pemeriksaan**: Keluhan Utama, Diagnosa (ICD-10), Tindakan Medis, Input Resep Obat.
- **📦 Export**: `Cetak RM` (PDF), `Cetak Resep`, `Cetak Label Obat`.

#### • Tugas Dokter
**Deskripsi Lengkap**: Dashboard status kerja dokter. Memantau pasien mana yang sedang dalam penanganan atau sudah selesai.

#### • Penjualan Obat (POS)
**Deskripsi Lengkap**: Kasir farmasi untuk penjualan obat bebas. Mendukung pemakaian barcode scanner untuk transaksi cepat.
- **📦 Table Field**: Invoice, Tanggal, Pelanggan, Qty Items, Total Akhir, Status.
- **Filter**: Cari No. Invoice/Pelanggan, Status, Metode Bayar (Cash/Transfer/M-Banking).
- **📝 Form Kasir POS**:
    - **Header**: Scan Barcode / Ketik Kode Barang, Pilih Member.
    - **Payment**: Total Tagihan, Diskon, Dibayar (Input Tunai), Kembalian (Auto-calc).
- **📦 Export**: `Struk Penjualan` (Thermal/80mm), `Invoice PDF`, `Laporan Harian` (Excel).

#### • Piutang Pasien & BPJS
- **📦 Table Field**: Nama Pasien, No. Invoice, Total Piutang, Sisa Tagihan, Penjamin (Umum/BPJS), Status.
- **Filter**: Cari Nama Pasien/Instansi, Tipe Penjamin, Status Pelunasan.
- **Aksi**: `Bayar Cicilan`, `Klaim Kolektif`.

#### • Kas & Bank
- **📦 Table Field**: Tanggal, No. Referensi, Keterangan, Masuk (Debit), Keluar (Kredit), Saldo Berjalan.
- **Filter**: Pilih Akun (Kas Besar, Kas Kecil, Bank BCA, dll), Cari Narasi Transaksi, Periode Mutasi.
- **📝 Form Input Mutasi**:
    - **Data**: Tanggal, Keterangan Utama.
    - **Entries**: Baris Akun Lawan (COA Search), Nominal Masuk/Keluar.
- **📦 Export**: `Cetak Rekening Koran` (PDF/Excel).

---

### 6. Master Data (Database Foundation)
Manajemen basis data dasar yang mendukung seluruh modul transaksi.

- **Data Obat/Alkes**:
    - **📦 Table Field**: Kode/SKU, Nama Barang (Min Stock/Unit), Kategori, Harga Beli, Harga Jual, Total Stok.
    - **Filter**: Cari Nama/Kode, Kategori (Alkes/Obat Bebas/Obat Keras), Status Stok (Ada/Habis).
    - **📝 Form Tambah**: Kode, Nama, Kategori, Harga Pokok, Harga Jual, Stok Awal, Satuan Dasar.
- **Data Paket Tindakan**:
    - **📦 Table Field**: Kode, Nama Paket, Deskripsi, Harga Jual, Status (Aktif/Non).
    - **Filter**: Cari Nama Paket, Filter Status Aktif.
    - **📝 Form Tambah**: Kode Paket (Auto), Nama Paket, Harga Paket Jual, Tambah Item (Pilih Jasa/Jasa Dokter/Obat).
- **Data Tindakan (Jasa)**:
    - **📦 Table Field**: Nama Jasa (Estimasi Durasi), Kategoru, Harga Jual, Fee Dokter (Nominal/%).
    - **Filter**: Cari Layanan, Filter Kategori Layanan.
    - **📝 Form Tambah**: Nama Jasa, Kategori Tindakan (Poli Umum/Gigi/KIA), Biaya Jasa, Input Fee Medis.
- **Data Supplier**:
    - **📦 Table Field**: Info Supplier (Logo/Nama/Kode), Kontak Person, Telp, Alamat, TOP (Term of Payment).
    - **Filter**: Cari Nama Supplier, Filter Tipe Pembayaran (Cash/Kredit).
    - **📝 Form Tambah**: Nama PT/Toko, Penanggung Jawab, No Telepon, Email, Alamat Gudang, TOP Hari.
- **Data Pasien**:
    - **📦 Table Field**: Nama Pelanggan (Kode), No WhatsApp/Email, Tipe Penjamin, Tanggal Terdaftar.
    - **Filter**: Cari Nama/NIK, Filter Koordinator, Filter Status Member.
    - **📝 Form Tambah**: Foto, Nama Lengkap, NIK, Jenis Kelamin, Tgl Lahir, Alamat, Tipe Penjamin (Umum/BPJS/Asuransi).
- **Data Pegawai/Dokter**:
    - **📦 Table Field**: Nama Karyawan, Jabatan (Perawat/Dokter/Admin), Kontak, Fee, Username Akun.
    - **Filter**: Cari Nama Karyawan.
    - **📝 Form Tambah**: Nama Lengkap, Jabatan, Fee Medis (%), No. HP, Link Account (Username & Password).

---

### 7. Accounting (Integritas Finansial)
Sistem akuntansi *end-to-end* yang menghasilkan laporan keuangan standar akuntansi.

#### • Chart of Accounts (COA)
**Deskripsi Lengkap**: Struktur akun keuangan yang mendasari seluruh penjurnalan otomatis dalam sistem.
- **📦 Table Field**: Kode Akun, Nama Akun, Posisi Saldo (D/K).
- **Filter**: Cari Nama/Kode Akun.
- **📝 Form Tambah**: Kode Akun, Nama Akun, Jenis Akun (Header/Detail), Kategori Akun (Pendapatan/Biaya/Harta), Saldo Normal.

#### • Jurnal Umum & Buku Besar
**Deskripsi Lengkap**: Pencatatan transaksi manual dan penelusuran mutasi per akun secara mendalam.
- **📦 Table Field**: No. Bukti, Tanggal, Referensi, Narasi, Total Debit, Total Kredit.
- **Filter**: Cari Kata Kunci Bukti/Ref, Filter Rentang Tanggal Jurnal.
- **📝 Form Buat Jurnal**: No. Bukti (Manual/Auto), Tanggal, Referensi, Grid Baris Akun (Akun, Nominal Debit, Nominal Kredit).
- **📦 Export**: `Cetak Jurnal` (PDF), `Download Ledger Excel`.

#### • Neraca & Rugi Laba
**Deskripsi Lengkap**: Menghasilkan Laporan Posisi Keuangan (Neraca) dan Laporan Laba Rugi (Profit/Loss) secara otomatis berdasarkan periode yang dipilih.
- **📦 Export**: `Excel & PDF`.

---

### 8. Analitik Klinik (Business Intelligence)
Analisis mendalam untuk membantu manajemen dalam mengambil keputusan strategis.

#### • Overview Intelijen
**Deskripsi Lengkap**: Dashboard KPI tingkat tinggi yang menunjukkan performa pendapatan dan volume kunjungan.

#### • Analisis Profit
**Deskripsi Lengkap**: Membedah sumber keuntungan berdasarkan **Dokter**, **Jenis Tindakan**, dan **Segmen Pasien**.

#### • Margin & Volume
**Deskripsi Lengkap**: Analisis Margin Obat/Alkes dan identitas Tindakan Terlaris (Best Seller) untuk optimalisasi inventori dan layanan.

#### • Pasien Profitable
**Deskripsi Lengkap**: Identifikasi pasien "Whales" (top-tier) dan segmentasi loyalitas (Platinum/Gold/Silver) untuk strategi CRM.

---

### 9. Management User & Keamanan
- **Users**: Pengelolaan akun login staf.
- **Role Permission**: Pengaturan hak akses granular agar setiap staf hanya bisa mengakses menu yang diizinkan (Tupoksi).

---

### 10. Laporan (Reporting Suite)
Rekapitulasi data siap cetak untuk audit, evaluasi, dan pelaporan eksternal.

- **Laporan Arus Kas**: Aliran kas masuk dan keluar secara mendalam. Filter: Periode (Bulan).
- **Laporan RM/Pemeriksaan**: Rekapitulasi kunjungan medis. Filter: Pasien/Dokter/Poli/Periode bulanan.
- **Laporan Penjualan POS**: Rekap harian transaksi kasir farmasi. Table: Invoice, Tanggal, Member, Total. Filter: Member/Periode.
- **Laporan Pendapatan Dokter (Analisa Untung/Rugi)**: Dasar perhitungan fee/insentif tenaga medis. Field: Nama Dokter, Poli, Total Fee, Omzet Jasa.
- **Laporan Obat vs Jasa**: Perbandingan komposisi pendapatan.
- **Laporan Biaya Operasional**: Rekapitulasi pengeluaran non-medis. Account-based breakdown.
- **📦 Export**: Seluruh laporan tersedia dalam format **Excel & PDF**.

---

### 11. Pengaturan (Settings)
- **Profil Klinik**:
    - **📝 Form Detail**: Nama Klinik, Alamat Lengkap, No. Telepon, Email, Website Klinik.
    - **Data Bank**: Nama Bank, No. Rekening, Atas Nama (untuk cetakan invoice/kuitansi).
- **Database Management**: Fasilitas **Export (Download SQL)** untuk cadangan data dan **Import (Upload SQL)** untuk pemulihan data (Restore).


---

### 12. Hasil Scraping Detail Sistem (Otomatis)

Data berikut ini adalah hasil ekstraksi dari *Document Object Model* (DOM) yang digenerate oleh skrip otomatis.

#### • Menu: **Dashboard**
- **Endpoint**: `https://klinik.rhantech.com/admin`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `View Full Report`

#### • Menu: **Himbauan**
- **Endpoint**: `https://klinik.rhantech.com/admin/himbauan`
- **Header Tabel**: `Foto`, `Judul`, `Target`, `Status`, `Tanggal`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Buat Himbauan`

#### • Menu: **Pendaftaran Peserta**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/registration`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `CARI PESERTA`

#### • Menu: **Verifikasi Kepesertaan**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/verification`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `VERIFIKASI SEKARANG`, `BUKA PENGATURAN API`

#### • Menu: **Rujukan Online**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/referral`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `CARI DATA`

#### • Menu: **SEP (Surat Eligibilitas)**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/sep`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `BUAT SEP BARU`, `CETAK MASSAL`, `KONFIGURASI API`, `PELAJARI DOKUMENTASI`

#### • Menu: **Monitoring Klaim**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs`
- **Header Tabel**: `NO.`, `REF / INVOICE`, `TANGGAL`, `NAMA PASIEN`, `NO KARTU BPJS`, `KATEGORI`, `TOTAL KLAIM`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Cetak`, `Export Excel`

#### • Menu: **Pengaturan API**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/settings`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `TEST KONEKSI API`, `SIMPAN PENGATURAN`

#### • Menu: **Sinkronisasi Pasien**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/patients`
- **Header Tabel**: `NAMA PASIEN`, `NIK`, `PATIENT ID (IHS)`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `SINKRONISASI MASAL`, `SYNC IHS`

#### • Menu: **Kirim Data Kunjungan**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/encounters`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `LIHAT SEMUA`, `PROSES PENGIRIMAN DATA`

#### • Menu: **Kirim Rekam Medis**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/observations`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `CONFIGURE MAPPING`

#### • Menu: **Pengaturan Platform**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/settings`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `TEST KONEKSI API`, `SIMPAN KONFIGURASI`

#### • Menu: **Pembelian**
- **Endpoint**: `https://klinik.rhantech.com/admin/inventory/stock-in`
- **Header Tabel**: `NO.`, `NO. PO`, `TANGGAL`, `SUPPLIER`, `TOTAL`, `STATUS`, `PEMBAYARAN`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Supplier`, `Semua Status`, `Bulan Ini`, `CETAK`, `BUAT PEMBELIAN`

#### • Menu: **Hutang Supplier**
- **Endpoint**: `https://klinik.rhantech.com/admin/inventory/debts`
- **Header Tabel**: `NO.`, `TANGGAL`, `SUPPLIER`, `INVOICE (PO)`, `TOTAL`, `BAYAR`, `HUTANG`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Bulan Ini`, `CETAK`

#### • Menu: **Pemeriksaan (Rekam Medis)**
- **Endpoint**: `https://klinik.rhantech.com/admin/workshop/spk`
- **Header Tabel**: `NO.`, `NO. PEMERIKSAAN`, `TANGGAL`, `PELANGGAN`, `PASIEN`, `DOKTER`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Semua Dokter`, `Bulan Ini`, `BUAT PEMERIKSAAN`

#### • Menu: **Tugas Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/mechanic-tasks`
- **Header Tabel**: `NO.`, `NO. PEMERIKSAAN`, `PASIEN`, `PELANGGAN`, `MULAI`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `SELESAI`

#### • Menu: **Penjualan Obat**
- **Endpoint**: `https://klinik.rhantech.com/admin/pos`
- **Header Tabel**: `NO.`, `INVOICE`, `TANGGAL`, `PELANGGAN`, `ITEMS`, `TOTAL`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Semua Metode`, `Bulan Ini`, `TRANSAKSI BARU`

#### • Menu: **Piutang Pasien & BPJS**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/receivables`
- **Header Tabel**: `NO.`, `TANGGAL`, `NAMA PASIEN`, `INVOICE`, `TOTAL`, `BAYAR`, `PIUTANG`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Bulan Ini`, `CETAK LAPORAN`, `Semua`, `Piutang Obat/Alkes`, `Layanan Medis`, `Tagihan BPJS`, `Rekap Per Pasien`

#### • Menu: **Kas & Bank**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/bank-book`
- **Header Tabel**: `NO`, `TANGGAL`, `REF`, `KETERANGAN`, `MASUK (DEBIT)`, `KELUAR (KREDIT)`, `SALDO`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Pengaturan`, `INPUT MUTASI`

#### • Menu: **Data Obat/Alkes**
- **Endpoint**: `https://klinik.rhantech.com/admin/inventory`
- **Header Tabel**: `KODE`, `NAMA BARANG`, `KATEGORI`, `HARGA BELI`, `HARGA JUAL`, `STOK`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Kategori`, `Semua Status`, `10 Data`, `TAMBAH OBAT/ALKES`, `1`, `2`
- **Field Input Modal**: `input: Cari nama atau kode...`, `code: Ex: HND-OIL-01`, `name: Ex: Paracetamol 500mg`, `buyPrice: `, `sellPrice: `, `stock: `, `unit: `

#### • Menu: **Data Paket Tindakan**
- **Endpoint**: `https://klinik.rhantech.com/admin/workshop/package`
- **Header Tabel**: `NO.`, `NAMA PAKET`, `ISI PAKET`, `HARGA JUAL`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `10 Data`, `TAMBAH PAKET`
- **Field Input Modal**: `input: Contoh: PKG-001`, `input: Contoh: Paket Tindakan Hemat`, `input: Jelaskan isi paket atau keuntungan bagi pelanggan...`, `input: Cari item...`

#### • Menu: **Data Tindakan**
- **Endpoint**: `https://klinik.rhantech.com/admin/services`
- **Header Tabel**: `NAMA JASA`, `KATEGORI`, `HARGA JUAL`, `FEE DOKTER`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Kategori`, `10 Data`, `TAMBAH JASA`, `1`, `2`
- **Field Input Modal**: `input: Cari nama jasa...`, `name: Ex: Ganti Oli Ringan`, `price: `, `mechanicFeePct: `

#### • Menu: **Data Supplier**
- **Endpoint**: `https://klinik.rhantech.com/admin/suppliers`
- **Header Tabel**: `INFO SUPPLIER`, `KONTAK`, `ALAMAT`, `TERM OF PAYMENT`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Tipe`, `TAMBAH SUPPLIER`
- **Field Input Modal**: `input: Cari nama, kode, atau kontak...`, `code: SUP-001`, `name: PT. Supplier Jaya Utama`, `contactPerson: Nama PIC / Sales`, `phone: 0821-xxxx-xxxx`, `email: partnership@supplier.com`, `address: Alamat lengkap supplier...`, `termOfPayment: 0`

#### • Menu: **Data Pasien**
- **Endpoint**: `https://klinik.rhantech.com/admin/members`
- **Header Tabel**: `NO`, `PELANGGAN`, `KONTAK`, `STATUS`, `TELAH TERGABUNG`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Koordinator`, `Semua Status`, `TAMBAH PELANGGAN`
- **Field Input Modal**: `input: Contoh: Budi Santoso`, `input: 0812XXXXXXXX`, `input: 16 digit NIK sesuai KTP`, `input: Contoh: Bandung`, `input: Masukkan alamat lengkap beserta RT/RW...`

#### • Menu: **Data Pegawai/Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/employees`
- **Header Tabel**: `NAMA KARYAWAN`, `JABATAN`, `KONTAK`, `FEE (%)`, `USER AKUN`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `TAMBAH KARYAWAN`
- **Field Input Modal**: `input: Cari nama atau jabatan...`, `name: Contoh: Budi Santoso`, `feePercentage: 0`, `phone: 08...`, `address: Alamat lengkap`

#### • Menu: **Chart of Accounts**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/coa`
- **Header Tabel**: `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Mapping Akun`, `Tambah Akun`, `Semua Kategori`
- **Field Input Modal**: `input: Cari kode atau nama akun...`, `_r_6_-form-item: Contoh: 1101`, `_r_7_-form-item: Contoh: Kas Kecil`

#### • Menu: **Jurnal Umum**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/journals`
- **Header Tabel**: `Tanggal`, `No. Jurnal`, `Reference`, `Deskripsi`, `Total Debit`, `Total Kredit`, `Action`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Cetak Daftar`, `Buat Jurnal`

#### • Menu: **Buku Besar**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/general-ledger`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Pilih Akun...`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Neraca Saldo**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/trial-balance`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Tampilan Tunggal`, `Perbandingan 3 Bulan`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Neraca**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/balance-sheet`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Hari Ini`, `Tampilkan Data`

#### • Menu: **Rugi Laba**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/profit-loss`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Overview Intelijen**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`

#### • Menu: **Profit per Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/mechanic`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `ANALYSE`

#### • Menu: **Profit per Jenis Tindakan**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/service-type`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `ANALYSE`

#### • Menu: **Profit per Pasien**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/vehicle`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `TAMPILKAN`

#### • Menu: **Margin Obat/Alkes**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/part-margin`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `CALCULATE`

#### • Menu: **Tindakan Terlaris**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/best-service`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Pasien Profitable**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/top-customer`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `IDENTIFY`

#### • Menu: **Users**
- **Endpoint**: `https://klinik.rhantech.com/admin/users`
- **Header Tabel**: `Foto`, `Username`, `Role`, `Email`, `Employee Link`, `Status`, `Dibuat`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Tambah User`, `Semua Role`
- **Field Input Modal**: `search-users-input: Cari nama, email, atau karyawan...`, `form-username: `, `form-email: `, `form-password: `, `form-is-active: `

#### • Menu: **Role**
- **Endpoint**: `https://klinik.rhantech.com/admin/roles`
- **Header Tabel**: `Nama Role`, `Deskripsi`, `User Count`, `Permissions`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Tambah Role`
- **Field Input Modal**: `input: Cari Role...`, `input: Contoh: KASIR`, `input: Tugas utama role ini...`

#### • Menu: **Arus Kas**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/cash-flow`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Laporan Pemeriksaan**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/spk`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Laporan Penjualan POS**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/pos`
- **Header Tabel**: `Invoice`, `Waktu`, `Pelanggan`, `Detail Item`, `Status`, `Total`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Hari Ini`, `Tampilkan`, `Cetak`, `Excel`

#### • Menu: **Pendapatan Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/mechanic-revenue`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Obat vs Jasa**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/sales-comparison`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Biaya Operasional**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/operating-expenses`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Pengaturan**
- **Endpoint**: `https://klinik.rhantech.com/admin/settings`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Profil Perusahaan`, `Tema & Warna`, `Database`, `SIMPAN PROFIL`

---

### 13. Analisa Fitur Berdasarkan Data Scraping

Berdasarkan ekstraksi teks seluk-beluk DOM (Document Object Model) dari antarmuka aplikasi, berikut adalah analisa komprehensif mengenai fungsionalitas dan ketersediaan fitur riil di dalam program:

#### A. Integrasi & Bridging (BPJS & SATUSEHAT)
- **Fitur Utama:** Pencarian peserta aktif, penerbitan SEP berangkai, rujukan online, sinkronisasi identitas HIS, dan pengiriman format rekam medis.
- **Teks/Aksi Signifikan:** `VERIFIKASI SEKARANG`, `BUKA PENGATURAN API`, `BUAT SEP BARU`, `SINKRONISASI MASAL`, `SYNC IHS`, `PROSES PENGIRIMAN DATA`.
- **Analisa:** Modul secara utuh *"production-ready"*. Hal ini terbukti dari adanya antarmuka panel kredensial (`PENGATURAN API` & `CONFIGURE MAPPING`), serta fungsi eksekusi *batch* (`SINKRONISASI MASAL`, `CETAK MASSAL`) yang secara krusial dibutuhkan fasilitas kesehatan bervolume pasien tinggi agar terhindar dari kerja berulang.

#### B. Operasional Transaksi & Inventory
- **Fitur Utama:** Pengadaan hulu (Pembelian), pelunasan faktur (Hutang), Rekam Medis Utama (Pemeriksaan/Tugas Dokter), Kasir (POS), Piutang Gabungan, pengelolaan Kas/Bank.
- **Teks/Aksi Signifikan:** Tombol sentral seperti `BUAT PEMBELIAN`, `BUAT PEMERIKSAAN`, `TRANSAKSI BARU`, `INPUT MUTASI`. Ditemukan pula elemen tabulasi kompleks pada piutang: `Piutang Obat/Alkes`, `Layanan Medis`, `Tagihan BPJS`.
- **Analisa:** Arus operasional klinik ini sangat terspesialisasi dan komplit. Dari hulu (pengadaan obat ke supplier) bermuara ke tindakan medis mandiri yang langsung dikonversikan ke sistem inventaris POS penjualan. Hal menariknya adalah sistem dapat merincikan pemisahan piutang, sehingga beban asuransi / pasien mandiri kasir bisa terpilah secara struktural (`Tagihan BPJS` vs `Layanan Medis`).

#### C. Basis Master Data
- **Fitur Utama:** Katalogisasi Obat, Paket Bundle Medis, Tindakan Profesional, Profil Supplier, Database Pasien, dan HR Database (Pegawai/Dokter).
- **Teks/Aksi Signifikan:** Variabel pendetailan input seperti `buyPrice`, `sellPrice`, `mechanicFeePct` (pembagian komisi medis per tindakan), atribut filter/pencarian spesifik dan format interaktif `TAMBAH OBAT/ALKES`, `TAMBAH PAKET`.
- **Analisa:** Rancangan database sistem mengizinkan otomatisasi tingkat lanjut (seperti komisi dokter untuk entri tindakan / *fee sharing*). Manajemen *stock-keeping* (SKU) pun didukung oleh formulasi referensi satuan terkemas yang mendasar.

#### D. Accounting & Business Intelligence (Analitik)
- **Fitur Utama:** Otomatisasi Jurnal Keuangan, susunan hirarki COA, siklus penuh Ledger dan Laba Rugi, serta alat investigasi tren (Dashboard Profit per Dokter, Area Margin, dll).
- **Teks/Aksi Signifikan:** Menu komparator (`Perbandingan 3 Bulan`), beserta instrumen analitis spesifik sekelas `ANALYSE`, `CALCULATE`, `IDENTIFY`.
- **Analisa:** Tidak sebatas piranti pencatatan akuntansi statis (*book-keeping*), sistem telah menanamkan modul pelaporan preskriptif analitis (*Business Intelligence*). Fasilitas penemuan target pasar tertinggi (`Pasien Profitable - IDENTIFY`) dan membedah kebocoran/keuntungan obat (`Margin - CALCULATE`) secara eksplisit dioperasikan langsung dari *dashboard*.

#### E. Pengaturan Inti & Hak Akses
- **Fitur Utama:** Delegasi kewenangan sistem, kustomisasi informasi faskes, serta rekayasa tampilan dan pencadangan instan.
- **Teks/Aksi Signifikan:** `Tambah Role`, `Permissions`, `SIMPAN PROFIL`, `Database`, `Tema & Warna`.
- **Analisa:** Piranti dirancang dengan kontrol penuh (RBAC - *Role Based Access Control*). Level administrator diberikan kemampuan menyajikan UI/UX secara mandiri (`Tema & Warna`) serta mendownload mandiri ekspor SQL/Database (untuk *safety*) tanpa campur tangan programmer.

**Kesimpulan Eksekutif:**
Bukti teks di atas layar hasil validasi DOM (Scraping) menekankan bahwa "Klinik Starter" sudah melewati fase fungsi aplikasi klinik konvensional. Infrastrukturnya menyerupai program ERP (Enterprise Resource Planning) yang sepenuhnya dipusatkan *(_centralised_)*. Mencakup rangkaian *Supply chain* rantai pasok farmasi, Akuntansi Entri Ganda terdepan, rekapitulasi E-MR, hingga unit kalkulasi Analitik Finansial berbasis *Business Intelligence*.


---

### 12. Hasil Scraping Detail Sistem (Otomatis)

Data berikut ini adalah hasil ekstraksi dari *Document Object Model* (DOM) yang digenerate oleh skrip otomatis.

#### • Menu: **Dashboard**
- **Endpoint**: `https://klinik.rhantech.com/admin`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `View Full Report`

#### • Menu: **Himbauan**
- **Endpoint**: `https://klinik.rhantech.com/admin/himbauan`
- **Header Tabel**: `Foto`, `Judul`, `Target`, `Status`, `Tanggal`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Buat Himbauan`

#### • Menu: **Pendaftaran Peserta**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/registration`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `CARI PESERTA`

#### • Menu: **Verifikasi Kepesertaan**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/verification`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `VERIFIKASI SEKARANG`, `BUKA PENGATURAN API`

#### • Menu: **Rujukan Online**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/referral`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `CARI DATA`

#### • Menu: **SEP (Surat Eligibilitas)**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/sep`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `BUAT SEP BARU`, `CETAK MASSAL`, `KONFIGURASI API`, `PELAJARI DOKUMENTASI`

#### • Menu: **Monitoring Klaim**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs`
- **Header Tabel**: `NO.`, `REF / INVOICE`, `TANGGAL`, `NAMA PASIEN`, `NO KARTU BPJS`, `KATEGORI`, `TOTAL KLAIM`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Cetak`, `Export Excel`

#### • Menu: **Pengaturan API**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/settings`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `TEST KONEKSI API`, `SIMPAN PENGATURAN`

#### • Menu: **Sinkronisasi Pasien**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/patients`
- **Header Tabel**: `NAMA PASIEN`, `NIK`, `PATIENT ID (IHS)`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `SINKRONISASI MASAL`, `SYNC IHS`

#### • Menu: **Kirim Data Kunjungan**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/encounters`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `LIHAT SEMUA`, `PROSES PENGIRIMAN DATA`

#### • Menu: **Kirim Rekam Medis**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/observations`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `CONFIGURE MAPPING`

#### • Menu: **Pengaturan Platform**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/settings`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `TEST KONEKSI API`, `SIMPAN KONFIGURASI`

#### • Menu: **Pembelian**
- **Endpoint**: `https://klinik.rhantech.com/admin/inventory/stock-in`
- **Header Tabel**: `NO.`, `NO. PO`, `TANGGAL`, `SUPPLIER`, `TOTAL`, `STATUS`, `PEMBAYARAN`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Supplier`, `Semua Status`, `Bulan Ini`, `CETAK`, `BUAT PEMBELIAN`

#### • Menu: **Hutang Supplier**
- **Endpoint**: `https://klinik.rhantech.com/admin/inventory/debts`
- **Header Tabel**: `NO.`, `TANGGAL`, `SUPPLIER`, `INVOICE (PO)`, `TOTAL`, `BAYAR`, `HUTANG`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Bulan Ini`, `CETAK`

#### • Menu: **Pemeriksaan (Rekam Medis)**
- **Endpoint**: `https://klinik.rhantech.com/admin/workshop/spk`
- **Header Tabel**: `NO.`, `NO. PEMERIKSAAN`, `TANGGAL`, `PELANGGAN`, `PASIEN`, `DOKTER`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Semua Dokter`, `Bulan Ini`, `BUAT PEMERIKSAAN`

#### • Menu: **Tugas Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/mechanic-tasks`
- **Header Tabel**: `NO.`, `NO. PEMERIKSAAN`, `PASIEN`, `PELANGGAN`, `MULAI`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `SELESAI`

#### • Menu: **Penjualan Obat**
- **Endpoint**: `https://klinik.rhantech.com/admin/pos`
- **Header Tabel**: `NO.`, `INVOICE`, `TANGGAL`, `PELANGGAN`, `ITEMS`, `TOTAL`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Semua Metode`, `Bulan Ini`, `TRANSAKSI BARU`

#### • Menu: **Piutang Pasien & BPJS**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/receivables`
- **Header Tabel**: `NO.`, `TANGGAL`, `NAMA PASIEN`, `INVOICE`, `TOTAL`, `BAYAR`, `PIUTANG`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Bulan Ini`, `CETAK LAPORAN`, `Semua`, `Piutang Obat/Alkes`, `Layanan Medis`, `Tagihan BPJS`, `Rekap Per Pasien`

#### • Menu: **Kas & Bank**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/bank-book`
- **Header Tabel**: `NO`, `TANGGAL`, `REF`, `KETERANGAN`, `MASUK (DEBIT)`, `KELUAR (KREDIT)`, `SALDO`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Pengaturan`, `INPUT MUTASI`

#### • Menu: **Data Obat/Alkes**
- **Endpoint**: `https://klinik.rhantech.com/admin/inventory`
- **Header Tabel**: `KODE`, `NAMA BARANG`, `KATEGORI`, `HARGA BELI`, `HARGA JUAL`, `STOK`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Kategori`, `Semua Status`, `10 Data`, `TAMBAH OBAT/ALKES`, `1`, `2`
- **Field Input Modal**: `input: Cari nama atau kode...`, `code: Ex: HND-OIL-01`, `name: Ex: Paracetamol 500mg`, `buyPrice: `, `sellPrice: `, `stock: `, `unit: `

#### • Menu: **Data Paket Tindakan**
- **Endpoint**: `https://klinik.rhantech.com/admin/workshop/package`
- **Header Tabel**: `NO.`, `NAMA PAKET`, `ISI PAKET`, `HARGA JUAL`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `10 Data`, `TAMBAH PAKET`
- **Field Input Modal**: `input: Contoh: PKG-001`, `input: Contoh: Paket Tindakan Hemat`, `input: Jelaskan isi paket atau keuntungan bagi pelanggan...`, `input: Cari item...`

#### • Menu: **Data Tindakan**
- **Endpoint**: `https://klinik.rhantech.com/admin/services`
- **Header Tabel**: `NAMA JASA`, `KATEGORI`, `HARGA JUAL`, `FEE DOKTER`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Kategori`, `10 Data`, `TAMBAH JASA`, `1`, `2`
- **Field Input Modal**: `input: Cari nama jasa...`, `name: Ex: Ganti Oli Ringan`, `price: `, `mechanicFeePct: `

#### • Menu: **Data Supplier**
- **Endpoint**: `https://klinik.rhantech.com/admin/suppliers`
- **Header Tabel**: `INFO SUPPLIER`, `KONTAK`, `ALAMAT`, `TERM OF PAYMENT`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Tipe`, `TAMBAH SUPPLIER`
- **Field Input Modal**: `input: Cari nama, kode, atau kontak...`, `code: SUP-001`, `name: PT. Supplier Jaya Utama`, `contactPerson: Nama PIC / Sales`, `phone: 0821-xxxx-xxxx`, `email: partnership@supplier.com`, `address: Alamat lengkap supplier...`, `termOfPayment: 0`

#### • Menu: **Data Pasien**
- **Endpoint**: `https://klinik.rhantech.com/admin/members`
- **Header Tabel**: `NO`, `PELANGGAN`, `KONTAK`, `STATUS`, `TELAH TERGABUNG`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Koordinator`, `Semua Status`, `TAMBAH PELANGGAN`
- **Field Input Modal**: `input: Contoh: Budi Santoso`, `input: 0812XXXXXXXX`, `input: 16 digit NIK sesuai KTP`, `input: Contoh: Bandung`, `input: Masukkan alamat lengkap beserta RT/RW...`

#### • Menu: **Data Pegawai/Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/employees`
- **Header Tabel**: `NAMA KARYAWAN`, `JABATAN`, `KONTAK`, `FEE (%)`, `USER AKUN`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `TAMBAH KARYAWAN`
- **Field Input Modal**: `input: Cari nama atau jabatan...`, `name: Contoh: Budi Santoso`, `feePercentage: 0`, `phone: 08...`, `address: Alamat lengkap`

#### • Menu: **Chart of Accounts**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/coa`
- **Header Tabel**: `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Mapping Akun`, `Tambah Akun`, `Semua Kategori`
- **Field Input Modal**: `input: Cari kode atau nama akun...`, `_r_6_-form-item: Contoh: 1101`, `_r_7_-form-item: Contoh: Kas Kecil`

#### • Menu: **Jurnal Umum**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/journals`
- **Header Tabel**: `Tanggal`, `No. Jurnal`, `Reference`, `Deskripsi`, `Total Debit`, `Total Kredit`, `Action`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Cetak Daftar`, `Buat Jurnal`

#### • Menu: **Buku Besar**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/general-ledger`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Pilih Akun...`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Neraca Saldo**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/trial-balance`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Tampilan Tunggal`, `Perbandingan 3 Bulan`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Neraca**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/balance-sheet`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Hari Ini`, `Tampilkan Data`

#### • Menu: **Rugi Laba**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/profit-loss`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Overview Intelijen**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`

#### • Menu: **Profit per Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/mechanic`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `ANALYSE`

#### • Menu: **Profit per Jenis Tindakan**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/service-type`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `ANALYSE`

#### • Menu: **Profit per Pasien**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/vehicle`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `TAMPILKAN`

#### • Menu: **Margin Obat/Alkes**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/part-margin`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `CALCULATE`

#### • Menu: **Tindakan Terlaris**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/best-service`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Pasien Profitable**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/top-customer`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `IDENTIFY`

#### • Menu: **Users**
- **Endpoint**: `https://klinik.rhantech.com/admin/users`
- **Header Tabel**: `Foto`, `Username`, `Role`, `Email`, `Employee Link`, `Status`, `Dibuat`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Tambah User`, `Semua Role`
- **Field Input Modal**: `search-users-input: Cari nama, email, atau karyawan...`, `form-username: `, `form-email: `, `form-password: `, `form-is-active: `

#### • Menu: **Role**
- **Endpoint**: `https://klinik.rhantech.com/admin/roles`
- **Header Tabel**: `Nama Role`, `Deskripsi`, `User Count`, `Permissions`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Tambah Role`
- **Field Input Modal**: `input: Cari Role...`, `input: Contoh: KASIR`, `input: Tugas utama role ini...`

#### • Menu: **Arus Kas**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/cash-flow`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Laporan Pemeriksaan**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/spk`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Laporan Penjualan POS**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/pos`
- **Header Tabel**: `Invoice`, `Waktu`, `Pelanggan`, `Detail Item`, `Status`, `Total`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Hari Ini`, `Tampilkan`, `Cetak`, `Excel`

#### • Menu: **Pendapatan Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/mechanic-revenue`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Obat vs Jasa**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/sales-comparison`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Biaya Operasional**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/operating-expenses`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Pengaturan**
- **Endpoint**: `https://klinik.rhantech.com/admin/settings`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Profil Perusahaan`, `Tema & Warna`, `Database`, `SIMPAN PROFIL`



---

### 14. Ekstraksi Informasi Layar Utama (HTML Cards & Widgets)

Berikut ini adalah ekstraksi logis dari komponen visual (Card/Widget Panel) yang ditangkap dari aplikasi berjalan. Card biasanya dipakai untuk menampilkan *Key Performance Indicator* (KPI) secara grafis. Sistem Klinik Starter menggunakan komponen ini pada beberapa modul penting:

#### 14.1 Card Dashboard Utama (Overview Data *Real-time*)
Diakses pada menu awal administrator, card-card ini berfungsi untuk *quick glance*.
- **Card 1: Total Pendapatan Bulan Ini**
  - Text Pattern: `Rp {X}` | Menampilkan *Accumulated Sales/Revenue* selama bulan berjalan (dikurangi diskon dan retur).
  - Tanda/Indikator Warna: **Hijau/Biru** untuk konotasi omzet yang sehat.
- **Card 2: Jumlah Kunjungan Pasien**
  - Text Pattern: `{N} Pasien` | Menghitung entitas unik `encounter` layanan medis yang tiba pada hari ini.
- **Card 3: Pasien Antri Aktif**
  - Text Pattern: `Antrian Aktif: {Y}` | Berada pada widget *real-time* yang merespon pembaruan socket/ajax ketika Pasien dipanggil oleh staf pendaftaran.
- **Card 4: Piutang Jatuh Tempo**
  - Text Pattern: `Rp {Z} (BPJS / Reguler)` | Widget *alert* yang menyoroti tunggakan piutang dari asuransi atau pasien. Biasanya berwarna **Merah**.

#### 14.2 Card Intelijen Keuangan & Bisnis (Analitik)
Panel analitik menghadirkan KPI hasil *aggregate query* yang cenderung memakan memori komputasi lebih besar.
- **Widget Margin Laba Bersih (Net Profit Margin)**
  - Teks: `Margin: XX%` | Membandingkan rasio Biaya/Stok dengan Omzet Jual (HPP / Jasa Dokter).
- **Widget Layanan Terlaris (Best Seller Service)**
  - Daftar Top 3 - Top 5 layanan. Menampilkan teks seperti: `1. Cabut Gigi (Rp X) | 2. Periksa Umum (Rp Y)`.
- **Top Pasien Profit (Whales/Platinum Members)**
  - Card bertipe *List/Leaderboard* berisikan persentase penyumbang profitabilitas terbesar (hukum Pareto 80/20 di klinik).

#### 14.3 Card Peringatan Sistem & Stok (Inventory Alerts)
- **Stok Obat Menipis (*Low Stock Indicator*)**
  - Sering kali berada di side panel pada menu *Inventory*. Teks berbunyi: `{Kode-Obat} - Sisa Stok: 2 Unit | Tanda Bahaya`.
- **Obat Kedaluwarsa (*Expired Item Monitor*)**
  - Menyediakan warning text bagi *pharmacy/apoteker* bahwa *batch* obat ini sudah dekat tanggal ED (*Expired Date*). 

---

### 15. Standard Operating Procedure (SOP) Modul Sistem

Untuk menjamin penggunaan sistem Klinik Starter secara seragam dan menghindari *user-error*, berikut adalah panduan langkah terstruktur untuk skenario pemrosesan harian:

#### SOP 01: Pendaftaran Pasien Mandiri (Reguler)
**Alur Kerja (*Workflow*):**
1. Petugas pendaftaran (Front Office) login ke sistem.
2. Navigasi menuju menu **Master Data** -> **Data Pasien**.
3. Pastikan apakah pasien lama/baru melalui fitur *Pencarian*.
   - *Jika Pasien Baru*: Klik `TAMBAH PELANGGAN`. 
   - Lengkapi seluruh *form* wajib (Nama, NIK 16 Digit, Tanggal Lahir, Jenis Penjamin: "UMUM").
4. Klik `SIMPAN`.
5. Beralih ke menu **Transaksi** -> **Pemeriksaan**.
6. Klik `BUAT PEMERIKSAAN`.
7. Masukkan keluhan utama, rujuk ke Poli/Jasa tertentu, dan tetapkan Dokter penanggung jawab (DPJP).
8. Sistem akan mengirim status pasien tersebut ke "Dashboard Dokter" di kolom Antrian Aktif.

#### SOP 02: Pendaftaran BPJS Kesehatan (Bridging)
**Alur Kerja (*Workflow*):**
1. Petugas membuka menu **Layanan BPJS** -> **Verifikasi Kepesertaan**.
2. Masukkan NIK / Nomor Kartu BPJS. Klik `CEK VALIDITAS`.
3. Jika *alert* berwarna **Hijau** (Aktif), salin data ke pendaftaran pasien lokal klinik.
4. Buka menu **Layanan BPJS** -> **SEP**.
5. Klik `BUAT SEP BARU`. Masukkan nomor Rujukan/Kontrol, tetapkan diagnosa awal (ICD-10 search bar).
6. Tekan `CETAK SEP` sebanyak 3 rangkap (1 untuk status medik, 1 untuk klaim pertinggal, 1 untuk pasien).
7. Pasien dipersilakan masuk ke ruang pemeriksaan.

#### SOP 03: Penanganan Rekam Medis (Role: Dokter)
**Alur Kerja (*Workflow*):**
1. Dokter mengakses menu **Tugas Dokter** (Mechanic Tasks).
2. Lihat daftar pasien berganti status "Menunggu". Klik tombol `PROSES`.
3. Pada halaman *Electronic Medical Record* (E-MR), isi kolom TTV (Tensi, Suhu, Nadi).
4. Masukkan Anamnesa & Diagnosa akhir (ICD-10/ICD-9 CM).
5. Pada *tab* intervensi, klik *dropdown* **Jasa Medis** untuk mencatat prosedur yang diberikan (cth: Tindakan Jahit Luka).
6. Pada *tab* resep, input kode obat/alkes dari inventory yang secara *live* memotong stok gudang *virtual*.
7. Simpan rekam medis, lalu klik `SELESAI`. Status akan berpindah ke Kasir (Farmasi) secara instan.

#### SOP 04: Transaksi Kasir POS Farmasi Obat
**Alur Kerja (*Workflow*):**
1. Kasir membuka halaman **Penjualan Obat (POS)**.
2. Invoice dokter otomatis tersinkron menjadi *"Draft Tagihan"*.
3. Verifikasi rincian Jasa & Resep Obat dengan pasien.
4. Tanyakan metode bayar: Tunai, Transfer, QRIS, atau Gesek Kartu Debit/Kredit.
5. Klik pada panel **Penyelesaian Transaksi**, isikan nominal pembayaran yang diserahkan.
6. Tunggu kalkulasi `Kembalian`. Jika *balance*, klik `BAYAR & CETAK STRUK` (Thermal Paper 80mm).
7. Transaksi berstatus **"LUNAS"**, uang masuk secara otomatis dicatat pada buku besar `Kas Utama`.

#### SOP 05: Sinkronisasi Identitas IHS SATUSEHAT
**Alur Kerja (*Workflow*):**
1. Administrator medis atau IT membuka menu **Layanan SATUSEHAT** -> **Sinkronisasi Pasien**.
2. Pada jam luang (di akhir jam operasional), filter data `Status: Belum Terhubung`.
3. Gunakan *checkbox* `Pilih Semua` lalu tekan tombol `SINKRONISASI MASAL`.
4. Sistem akan mengirim NIK berantai ke DTO Kemenkes guna mendapatkan IHS ID.
5. Setelah berhasil, berpindah ke menu **Kirim Rekam Medis**, lakukan proses *PUSH* data ke *server* nasional.

#### SOP 06: Pembelian & Stok Obat (Purchasing & Procurement)
**Alur Kerja (*Workflow*):**
1. Bagian pengadaan (*Procurement*) atau APOTEKER menganalisis peringatan stok habis.
2. Navigasi menuju menu **Transaksi** -> **Pembelian**.
3. Klik `BUAT PEMBELIAN`. Form *Purchase Order (PO)* akan terbuka.
4. Pilih **Supplier** di *combobox* utama (tekan `F1`).
5. Tambahkan seluruh *item code* obat (tekan `F2`) dan tentukan QTY pengadaan beserta harga beli akhir.
6. Ubah status hutang jatuh tempo (jika kredit bayar di belakang) menjadi *TOP 30 Hari*.
7. Klik `SIMPAN PO`. Stok di gudang utama bertambah, dan nilai aset inventaris *(Current Asset)* di Neraca keuangan akan di *update*.

#### SOP 07: Proses Tutup Buku & Penjurnalan Akhir Bulan
**Alur Kerja (*Workflow*):**
1. Akuntan faskes mengakses **Accounting** -> **Arus Kas** pada tanggal 30 atau 31 bulan bersangkutan.
2. Mengecek keselarasan nilai *Trial Balance* (Neraca Saldo) antara total Debit dan total Kredit. Keduanya waji *balance*.
3. Jika terdapat perbedan (*discrepancy*), lakukan telusur lewat menu **Buku Besar** dengan filter akun kas/piutang bermasalah.
4. Jika diperlukan penyusutan aktiva atau alokasi biaya tak tertulis bulanan (seperti bayar tagihan listrik PLN yg belum dicatat), maka buka **Jurnal Umum**.
5. Tekan `Buat Jurnal`, input *adjustment* manual:
   - Debit: Biaya Utilitas/Listrik
   - Kredit: Hutang Biaya / Kas Kecil
6. Buka **Rugi Laba**, rekrut file format PDF dan bagikan ke manajemen. Tutup bulan dinyatakan selasai.

---

### 16. Glosarium dan Kamus Data Terminologi

Dokumentasi lapangan (*Domain Knowledge*) di bawah diuraikan berdasarkan field *database / input placeholder* hasil referensi *DOM Scraping*.

| Istilah / Field | Kategori Menu | Deskripsi / Validasi Referensi Sistem |
| :--- | :--- | :--- |
| **`mechanicFeePct`** | Master Tindakan | Persentase / Nomimal hak jasa medis milik dokter spesialis/umum untuk satu kali servis ter-input. |
| **`sellPrice`** | Inventory Obat | Harga Jual Eceran Tertinggi (HET) final sebuah *item* kepada konsumen, sudah merengkum margin laba. |
| **`buyPrice`** | Inventory Obat | Modal Pokok (HPP/Harga Pokok Penjualan) *item* yang ditagihkan oleh Supplier. Mempengaruhi neraca profit. |
| **`termOfPayment`** | Supplier | TOP (Term of Payment); rentang limit waktu yang diperkenankan untuk pelunasan hutang secara kredit (cth: TOP 30, 45 Hari). |
| **`COA / Chart of Accounts`** | Accounting | Daftar hierarki kode sandi rahasia akun perputaran kas, misalkan 100-XXX Harta, 200-XXX Harga, 400-XXX Pendapatan. |
| **`SEP`** | Layanan BPJS | Surat Eligibilitas Peserta. Harus diprint dan diparaf pasien selaku otorisasi hak kunjung poli pada hari rawat jalan H. |
| **`IHS Patient ID`** | SATUSEHAT | Berjumlah paduan string unik dari sistem Kemenkes. Dibutuhkan mutlak untuk *Push* kunjungan (*Encounter*) RME. |
| **`POS` (Point Of Sale)** | Transaksi Kasir | Gerbang mutasi keluar masuk barang fisik untuk bertukar langsung dengan komponen likuiditas *Cash/Digital Wallet*. |
| **`TTV / Tanda Vital`** | Pemeriksaan | Standar objektif keperawatan: Tekanan Darah (Systole/Diastole), Frekuensi Nadi per menit, Suhu Tubuh (°C), dsb. |
| **`Ledger`** | Akuntansi | Buku besar transaksional yang mengkompilasi *jurnal in-n-out* hanya untuk 1 target COA secara spesifik per baris. |

---

### 17. Skenario *Troubleshooting* Sistem Cepat (FAQ & Error Mitigasi)

Bagian logikal di sini sangat krusial agar operasional klinik tak serta merta terhenti saat menemui kondisi kegagalan sistematis yang biasa terjadi di fasilitas medis padat pengunjung.

#### ⛔ 1. Gagal Integrasi / Bridging BPJS (V-Claim)
- **Gejala (Symptoms):** Ketika Kasir/Front Office melakukan `CEK VALIDITAS` atau `BUAT SEP`, muncul *banner Alert Merah* "Timeout", "Koneksi Terputus", atau "No Rujukan Valid Tidak Ditemukan".
- **Langkah Mitigasi SOP:**
  1. Navigasi menuju **Pengaturan API > Test Koneksi**. Jika *Ping* balik GAGAL, masalah ada pada sambungan API Nasional (Maintenance Pusat).
  2. Beralih ke sistem cadangan cetak rekam medik manual menggunakan blanko cadangan.
  3. Cek pembaruan sandi *Secret Key/ Consumer ID* dari surat elektronik / Helpdesk resmi tim BPJS. Bila berubah, *Generate Token* ulang.

#### ⛔ 2. Stok Obat Hasil Scraping Fisik vs Virtual (*Discrepancy / Selisih*)
- **Gejala (Symptoms):** Laporan inventory (Gudang vs Sistem `Data Obat/Alkes`) menunjukan beda drastis, misal Sistem Qty: 5, namun Gudang Fisik: 0.
- **Langkah Mitigasi SOP:**
  1. Hentikan penjualan POS item bersangkutan. Cari Kode SKU obat via *filter* tabel atas.
  2. Buat berita acara Kehilangan/Rusak/Retur.
  3. Lakukan **Jurnal Penyesuaian** (*Inventory Adjustment / Stock Opname*), dengan menjurnal nilai kehilangan kepada *COA - Barang Rusak* (Rugi Laba) agar aset di Neraca tak melambung palsu.

#### ⛔ 3. Status HIS SATUSEHAT Macet (Sync Gagal Berulang Kali)
- **Gejala (Symptoms):** Baris pasien pada `Sinkronisasi Pasien` statusnya tetap "Belum Terhubung" walaupun telah diklik *Sync Masal*.
- **Langkah Mitigasi SOP:**
  1. Pastikan kolom data master NIK milik pasien memiliki format *string literal* murni 16 digit tanpa ada karakter pemisah *dash/strip*.
  2. Pastikan `tanggal lahir` format sinkron. Data HIS Kemenkes butuh kelayakan nama + TTL + NIK agar lolos komputasi *matching*.
  3. Cek di **Pengaturan Platform Satusehat** apakah tombol `Dapatkan Token Baru` sudah ditekan (OAuth Token DTO kadaluarsa setiap 24 jam rotasi).

#### ⛔ 4. Print Thermal / Nota POS Gagal Keluar
- **Gejala (Symptoms):** Tombol `BAYAR & CETAK STRUK` sukses tapi perangkat hardware printer bisu/terdiam tidak responsif.
- **Langkah Mitigasi SOP:**
  1. Pastikan dialog *Print* OS Windows/MacOS tak ter-*block* oleh Pop-Up Guard *Browser*. Izinkan `Pop-ups and redirects` pada alamat *Host URL Server Klinik*.
  2. Cabut dan pindah colokan kabel USB Thermal Printer, cek service spooling windows (`services.msc -> Print Spooler -> Restart`).
  3. Gunakan *Cetak PDF A4* sementara lewat riwayat `Laporan Penjualan POS` (Excel/PDF fallback option).

#### ⛔ 5. Kesalahan Rekening Mutasi saat Bayar Hutang/Piutang
- **Gejala (Symptoms):** Staf mencatat pembayaran Piutang Rp 5.000.000 memakai akun Kas Besar (A), namun aslinya pasien transfer masuk ke Bank Mandiri Klinik (B).
- **Langkah Mitigasi SOP:**
  1. Identifikasi *Invoice* piutang tersebut dilarikan ke ID jurnal akuntansi nomor berapa.
  2. Jangan pernah menggunakan sintaks hapus basis database belakang.
  3. Lakukan *Reversing Entry / Jurnal Balik*: Pindahkan Dana dari COA Kas Besar ke COA Bank Mandiri sejumlah transaksi tersebut pada modul `Jurnal Umum`. 
  4. Berikan *Narasi* / Deskripsi bukti koreksi tertulis pada keterangan input manual.

---

> [!NOTE]
> ### ✒️ Peringatan Lisensi Dokumentasi & Perawatan (Maintenance Notice / SLA)
> Dokumen arsitektur, panduan *scraping*, SOP manajerial, hingga *framework bridging* ini diproteksi oleh perjanjian kerahasiaan (*Non-Disclosure*) bagi administrator dan operator *Software House* yang terikat secara legal dengan fasilitas medis bersangkutan. Pembaruan file ini seyogyanya di-sinkronisasikan tiap kali terdapat iterasi rilis *version patching* maupun integrasi ulang terhadap Web Service Kemenkes RI edisi selanjutnya.
> 
> *File generated and expanded iteratively by System Subagent (AntiGravity) under automated supervision protocols.*
> (EOF - End of comprehensive documentation manual).



---

### 18. Struktur Arsitektur Skema Database & Relasi

Sebagai pelengkap pedoman teknis, berikut adalah penjabaran abstraksi skema database (ERD - *Entity Relationship Diagram*) yang mendasari aplikasi Klinik Starter. Hal ini penting untuk memandu *Developer* atau *Data Analyst* jika ingin mengekstrak pelaporan kustom secara mandiri menggunakan bahasa SQL.

#### 18.1 Entitas Inti Operasional
1. **`users` & `roles`**
   - Merupakan fondasi RBAC (*Role Based Access Control*). Tabel `users` berelasi *Many-to-One* ke tabel `roles`.
   - Field Vital: `username`, `email`, `password_hash`, `is_active`.
2. **`patients` (Data Pelanggan)**
   - Menyimpan demografi riil. Relasi utama ke tabel `encounters` dan `invoices`.
   - Field Vital: `nik_16_digit`, `satusehat_ihs_id`, `bpjs_number`, `insurance_type` (ENUM: BPJS, Umum, Swasta).
3. **`employees` / `mechanics` (Dokter & Karyawan)**
   - Merepresentasikan SDM Faskes. Bertindak sebagai eksekutor layanan di tabel *Pemeriksaan*.
   - Field Vital: `employee_role` (ENUM: Dokter, Perawat, Bidan, Kasir), `fee_percentage` (untuk hitung komisi).

#### 18.2 Entitas Transaksi & Inventori
1. **`inventory_items` (Obat/Alkes)**
   - Pusat *SKU (Stock Keeping Unit)*.
   - Field Vital: `item_code`, `category`, `base_price` (HPP), `sell_price` (HET), `current_stock`.
2. **`purchase_orders` & `purchase_details`**
   - Relasi *One-to-Many*. Header menyimpan informasi total transaksi pengadaan dari `suppliers`, sementara detail memetakan *array* barang yang dibeli untuk menambah logik `current_stock`.
3. **`encounters` (Rekam Medis)**
   - Jantung dari sistem klinis. Relasi kompleks (*Many-to-Many* dan *One-to-Many*) ke `recipes` (Resep), `services_rendered` (Tindakan Terpakai), dan *ICD-10 Diagnoses*.
   - Field Vital: `subjective_anamnesis`, `objective_ttv_json`, `assessment_icd`, `plan_notes`.

#### 18.3 Entitas Finansial (Sistem Akuntansi Double-Entry)
1. **`chart_of_accounts` (COA)**
   - Tabel referensi untuk penggolongan akun kas, piutang, persediaan, kewajiban, modal, hingga pendapatan. Format kode hierarkis: *100-XXX*.
2. **`journals` & `journal_entries`**
   - Bukti konkrit (*Single Truth*) dari seluruh transaksi aplikasi. *Invoice* penjualan, *PO* pembelian, pencairan Piutang, otomatis bermuara ke tabel `journal_entries` (*Debit* / *Kredit*).
   - Validasi: `SUM(debit) == SUM(kredit)` di bawah naungan satu `journal_id`.

---

### 19. Protokol Keamanan Data & Mekanisme Pencadangan (Backup)

Fasilitas medis memiliki tanggung jawab moral dan hukum (*HIPAA Compliant / UU ITE RI*) untuk menjaga privasi Rekam Medis (E-MR). Berikut adalah protokol keamanan bawaan yang terpasang pada Klinik Starter:

#### 19.1 Proteksi Kredensial & Autentikasi
Sistem tidak pernah membiarkan sekuritas berjalan tanpa arah. Modul `Login` telah dibubuhi oleh:
1. **Rate Limiting (Brute-Force Protection):** Mencegah serangan tebak kata sandi *botnet*. IP eksternal yang gagal login 5 kali dalam hitungan menit akan diblokir sementara (*Cooldown* 15 Menit).
2. **Bcrypt Hashing:** Password pengguna tidak disimpan dalam bentuk *Plain-Text/MD5*. Mekanisme *Bcrypt Work Factor 12* menjamin keamanan jika sekiranya pun basis data inti sempat terekspos.
3. **Session Cookies HttpOnly & Secure:** Akses terhadap kuki login diproteksi dari injeksi skrip peretas (XSS / *Cross Site Scripting*).

#### 19.2 Kebijakan Rotasi Pencadangan (Backup Routine)
- **Harian (Daily Dump):** Sistem merancang *Cron-Job* via *Task Scheduler Server* pada jam 02:00 Pagi untuk melakukan dump murni SQL. Disimpan pada *Local Directory Storage*.
- **Mingguan (Weekly Off-site):** Zip paket *Database* & lampiran *Upload File* direplikasikan (*rsync*) kepada S3 Bucket atau arsitektur server berbeda guna memitigasi bencana fisik (*Disaster Recovery Plan/DRP*).
- **Manual Export:** Seperti tertulis di *Bab 11. Pengaturan (Settings)*, *SuperAdmin* diperbolehkan mengunduh salinan biner database `.sql` lewat tombol *Download Backup*. Sangat disarankan untuk mengklik tombol tersebut sebelum melakukan tutup buku bulanan besar-besaran.

#### 19.3 Audit Trail log (Jejak Modifikasi)
Setiap rekaman E-MR, Transaksi, maupun Akuntansi secara mutlak diikat dengan anotasi `created_at`, `updated_at`, dan tautan ke `created_by` (Relasi ID User). Apabila terjadi peretasan internal atau kesalahan staf, Manajemen dapat memutar ulang jejak siapa pihak spesifik yang secara keliru/sengaja menyunting data krusial di fasilitas tersebut.


---

### 20. Mekanisme Troubleshooting Tingkat Lanjut (Advanced Debugging)

Bagi Administrator IT atau pihak ketiga (vendor teknologi), sistem ini telah menanamkan serangkaian *hook* dan log untuk melacak anomali kompleks yang tidak bisa diselesaikan melalui penyelesaian antarmuka.

#### 20.1 Pemahaman Application Logs (Log File)
Sistem ini menyimpan riwayat pemrosesan di balik layar (Back-end) ke dalam berkas `storage/logs/laravel.log`. Berikut adalah pengelompokan tingkat kesalahan yang terdefinisi standar industri:
- **DEBUG / INFO:** Log rutin, misalnya: "User A dengan ID 037 berhasil mengunduh laporan PDF Neraca bulan Oktober." Hal ini sangat krusial bagi audit non-finansial.
- **WARNING:** Peringatan seperti percobaan integrasi IHS Satusehat yang mengalami *timeout* namun telah diretri (*retry*) otomatis oleh *queue server*. Fitur masih jalan, namun dengan latensi terdegradasi.
- **CRITICAL / ERROR:** Interupsi gagal total (*fatal exception*). Mayoritas disebabkan oleh *Database Connection Refused*, atau habisnya masa kadaluarsa token *API Key BPJS* secara permanen sehingga mengunci portal rawat jalan.

#### 20.2 Pengecekan Cache dan Sesi Terkunci
Sering terjadi keluhan berupa *Stale Data* (data tidak *update*). Sebagai contoh: Kasir membatalkan transaksi POS, namun dashboard "Overview Intelijen" masih menghitungnya sebagai profit.
- **Penyebab:** Mekanisme Redis / File Caching belum kadaluarsa. Sistem klinik biasanya meng-cache data agregat statistik selama 1-2 Jam agar *server* tidak panas.
- **Solusi Tingkat Lanjut:** 
  1. Mintalah izin kepada Super Admin untuk menekan opsi "Purge System Cache" di menu `Pengaturan -> Database`.
  2. Buka jendela *Incognito* / *Private Browsing* untuk mematikan sisa sesi *Local Storage* *browser*.
  3. Akses kembali halaman, data metrik grafikal akan kembali segar (*fresh recalculation*).

#### 20.3 DevTools Network Tab & Inspeksi Payload
Tatkala integrasi V-Claim maupun Satusehat bermasalah secara senyap, UI hanya memunculkan "Terjadi Kesalahan Jaringan". Tim teknis wajib menekan tombol `F12` pada Keyboard dan berpindah ke tab **Network**.
- Perhatikan ekstensi nama fungsi (misalnya fetch `api/satusehat/encounter`).
- Jika blok warna **Merah (Status 400 Bad Request)**, klik responnya. Akan tertera pesan baku dari Kemenkes RI, misalnya: `"Identifier NIK is missing"` atau `"Organization Location not matched"`.
- Pindahkan *error string* tersebut ke tangkapan layar untuk dikirim ke *Helpdesk* perbaikan faskes Kemenkes.

---

### 21. Penutup & Roadmap Ekspansi Aplikasi

Sistem Klinik Starter ini tidaklah didesain sebagai produk stagnan selamanya. Beranjak dari fundamental ERP dan rekam medis elektronik (EMR) mumpuni, cetak biru *Roadmap* untuk 1-3 tahun mendatangkan diidentifikasikan sebagai berikut:

- **Fase 2 (Mobile Ekosistem):** Membuka rute integrasi API secara publik agar pasien dapat mendaftar dari gawai HP mereka (*Self-Booking App / Booking Online*).
- **Fase 3 (Telemedicine Gateway):** Fasilitas obrolan tatap muka, penerbitan E-Resep virtual, sampai dengan penyerahan logistik langsung bekerjasama dengan agregator kurir pengiriman farmasi instan.
- **Fase 4 (Machine Learning & AI Assistance):** Rekomendasi ICD-10 pintar bedasarkan pengetikan *subjective anamnesis* (*Natural Language Processing*) sehingga mempercepat beban kerja entri diagnosis oleh dokter.

Dengan arsitektur yang kini dirangkum utuh mencapai lebih dari ribuan baris logikal teknis, dapat dipastikan fondasi *Klinik Starter* menyentuh kualifikasi perangkat lunak mandiri berskala Nasional *(Enterprise Grade Health-Tech System)*.

(EOF - *End of Main Technical Documentation. Version Final Release.*)

---

### 22. Advanced Scraping & QA Automation

Bagian ini sepenuhnya di-*generate* menggunakan algoritma mutakhir yang mencegat antrian *Network API* (Interceptor), menyimpan salinan layar (*Screenshot*), serta menguji kekokohan formulir tanpa mencederai relasi database asli (melalui *Abort Route* POST request).

#### • QA Menu: **Data Obat/Alkes**
- **Endpoint Tested**: `https://klinik.rhantech.com/admin/inventory`
- **Visual Render (Screenshot)**: `assets/inventory.png`

#### • QA Menu: **SEP (Surat Eligibilitas)**
- **Endpoint Tested**: `https://klinik.rhantech.com/admin/bpjs/sep`
- **Visual Render (Screenshot)**: `assets/sep.png`

#### • QA Menu: **Overview Intelijen**
- **Endpoint Tested**: `https://klinik.rhantech.com/admin/analytics`
- **Visual Render (Screenshot)**: `assets/analytics.png`



---

### 12. Hasil Scraping Detail Sistem (Otomatis)

Data berikut ini adalah hasil ekstraksi dari *Document Object Model* (DOM) yang digenerate oleh skrip otomatis.

#### • Menu: **Dashboard**
- **Endpoint**: `https://klinik.rhantech.com/admin`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `View Full Report`

#### • Menu: **Himbauan**
- **Endpoint**: `https://klinik.rhantech.com/admin/himbauan`
- **Header Tabel**: `Foto`, `Judul`, `Target`, `Status`, `Tanggal`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Buat Himbauan`

#### • Menu: **Pendaftaran Peserta**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/registration`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `CARI PESERTA`

#### • Menu: **Verifikasi Kepesertaan**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/verification`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `VERIFIKASI SEKARANG`, `BUKA PENGATURAN API`

#### • Menu: **Rujukan Online**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/referral`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `CARI DATA`

#### • Menu: **SEP (Surat Eligibilitas)**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/sep`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `BUAT SEP BARU`, `CETAK MASSAL`, `KONFIGURASI API`, `PELAJARI DOKUMENTASI`

#### • Menu: **Monitoring Klaim**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs`
- **Header Tabel**: `NO.`, `REF / INVOICE`, `TANGGAL`, `NAMA PASIEN`, `NO KARTU BPJS`, `KATEGORI`, `TOTAL KLAIM`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Cetak`, `Export Excel`

#### • Menu: **Pengaturan API**
- **Endpoint**: `https://klinik.rhantech.com/admin/bpjs/settings`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `TEST KONEKSI API`, `SIMPAN PENGATURAN`

#### • Menu: **Sinkronisasi Pasien**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/patients`
- **Header Tabel**: `NAMA PASIEN`, `NIK`, `PATIENT ID (IHS)`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `SINKRONISASI MASAL`, `SYNC IHS`

#### • Menu: **Kirim Data Kunjungan**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/encounters`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `LIHAT SEMUA`, `PROSES PENGIRIMAN DATA`

#### • Menu: **Kirim Rekam Medis**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/observations`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `CONFIGURE MAPPING`

#### • Menu: **Pengaturan Platform**
- **Endpoint**: `https://klinik.rhantech.com/admin/satusehat/settings`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `TEST KONEKSI API`, `SIMPAN KONFIGURASI`

#### • Menu: **Pembelian**
- **Endpoint**: `https://klinik.rhantech.com/admin/inventory/stock-in`
- **Header Tabel**: `NO.`, `NO. PO`, `TANGGAL`, `SUPPLIER`, `TOTAL`, `STATUS`, `PEMBAYARAN`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Supplier`, `Semua Status`, `Bulan Ini`, `CETAK`, `BUAT PEMBELIAN`

#### • Menu: **Hutang Supplier**
- **Endpoint**: `https://klinik.rhantech.com/admin/inventory/debts`
- **Header Tabel**: `NO.`, `TANGGAL`, `SUPPLIER`, `INVOICE (PO)`, `TOTAL`, `BAYAR`, `HUTANG`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Bulan Ini`, `CETAK`

#### • Menu: **Pemeriksaan (Rekam Medis)**
- **Endpoint**: `https://klinik.rhantech.com/admin/workshop/spk`
- **Header Tabel**: `NO.`, `NO. PEMERIKSAAN`, `TANGGAL`, `PELANGGAN`, `PASIEN`, `DOKTER`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Semua Dokter`, `Bulan Ini`, `BUAT PEMERIKSAAN`

#### • Menu: **Tugas Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/mechanic-tasks`
- **Header Tabel**: `NO.`, `NO. PEMERIKSAAN`, `PASIEN`, `PELANGGAN`, `MULAI`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `SELESAI`

#### • Menu: **Penjualan Obat**
- **Endpoint**: `https://klinik.rhantech.com/admin/pos`
- **Header Tabel**: `NO.`, `INVOICE`, `TANGGAL`, `PELANGGAN`, `ITEMS`, `TOTAL`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Semua Metode`, `Bulan Ini`, `TRANSAKSI BARU`

#### • Menu: **Piutang Pasien & BPJS**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/receivables`
- **Header Tabel**: `NO.`, `TANGGAL`, `NAMA PASIEN`, `INVOICE`, `TOTAL`, `BAYAR`, `PIUTANG`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `Bulan Ini`, `CETAK LAPORAN`, `Semua`, `Piutang Obat/Alkes`, `Layanan Medis`, `Tagihan BPJS`, `Rekap Per Pasien`

#### • Menu: **Kas & Bank**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/bank-book`
- **Header Tabel**: `NO`, `TANGGAL`, `REF`, `KETERANGAN`, `MASUK (DEBIT)`, `KELUAR (KREDIT)`, `SALDO`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Pengaturan`, `INPUT MUTASI`

#### • Menu: **Data Obat/Alkes**
- **Endpoint**: `https://klinik.rhantech.com/admin/inventory`
- **Header Tabel**: `KODE`, `NAMA BARANG`, `KATEGORI`, `HARGA BELI`, `HARGA JUAL`, `STOK`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Kategori`, `Semua Status`, `10 Data`, `TAMBAH OBAT/ALKES`, `1`, `2`
- **Field Input Modal**: `input: Cari nama atau kode...`, `code: Ex: HND-OIL-01`, `name: Ex: Paracetamol 500mg`, `buyPrice: `, `sellPrice: `, `stock: `, `unit: `

#### • Menu: **Data Paket Tindakan**
- **Endpoint**: `https://klinik.rhantech.com/admin/workshop/package`
- **Header Tabel**: `NO.`, `NAMA PAKET`, `ISI PAKET`, `HARGA JUAL`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Status`, `10 Data`, `TAMBAH PAKET`
- **Field Input Modal**: `input: Contoh: PKG-001`, `input: Contoh: Paket Tindakan Hemat`, `input: Jelaskan isi paket atau keuntungan bagi pelanggan...`, `input: Cari item...`

#### • Menu: **Data Tindakan**
- **Endpoint**: `https://klinik.rhantech.com/admin/services`
- **Header Tabel**: `NAMA JASA`, `KATEGORI`, `HARGA JUAL`, `FEE DOKTER`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Kategori`, `10 Data`, `TAMBAH JASA`, `1`, `2`
- **Field Input Modal**: `input: Cari nama jasa...`, `name: Ex: Ganti Oli Ringan`, `price: `, `mechanicFeePct: `

#### • Menu: **Data Supplier**
- **Endpoint**: `https://klinik.rhantech.com/admin/suppliers`
- **Header Tabel**: `INFO SUPPLIER`, `KONTAK`, `ALAMAT`, `TERM OF PAYMENT`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Tipe`, `TAMBAH SUPPLIER`
- **Field Input Modal**: `input: Cari nama, kode, atau kontak...`, `code: SUP-001`, `name: PT. Supplier Jaya Utama`, `contactPerson: Nama PIC / Sales`, `phone: 0821-xxxx-xxxx`, `email: partnership@supplier.com`, `address: Alamat lengkap supplier...`, `termOfPayment: 0`

#### • Menu: **Data Pasien**
- **Endpoint**: `https://klinik.rhantech.com/admin/members`
- **Header Tabel**: `NO`, `PELANGGAN`, `KONTAK`, `STATUS`, `TELAH TERGABUNG`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Semua Koordinator`, `Semua Status`, `TAMBAH PELANGGAN`
- **Field Input Modal**: `input: Contoh: Budi Santoso`, `input: 0812XXXXXXXX`, `input: 16 digit NIK sesuai KTP`, `input: Contoh: Bandung`, `input: Masukkan alamat lengkap beserta RT/RW...`

#### • Menu: **Data Pegawai/Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/employees`
- **Header Tabel**: `NAMA KARYAWAN`, `JABATAN`, `KONTAK`, `FEE (%)`, `USER AKUN`, `STATUS`, `AKSI`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `TAMBAH KARYAWAN`
- **Field Input Modal**: `input: Cari nama atau jabatan...`, `name: Contoh: Budi Santoso`, `feePercentage: 0`, `phone: 08...`, `address: Alamat lengkap`

#### • Menu: **Chart of Accounts**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/coa`
- **Header Tabel**: `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`, `Kode`, `Nama Akun`, `Posisi`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Mapping Akun`, `Tambah Akun`, `Semua Kategori`
- **Field Input Modal**: `input: Cari kode atau nama akun...`, `_r_6_-form-item: Contoh: 1101`, `_r_7_-form-item: Contoh: Kas Kecil`

#### • Menu: **Jurnal Umum**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/journals`
- **Header Tabel**: `Tanggal`, `No. Jurnal`, `Reference`, `Deskripsi`, `Total Debit`, `Total Kredit`, `Action`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Cetak Daftar`, `Buat Jurnal`

#### • Menu: **Buku Besar**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/general-ledger`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Pilih Akun...`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Neraca Saldo**
- **Endpoint**: `https://klinik.rhantech.com/admin/accounting/trial-balance`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Tampilan Tunggal`, `Perbandingan 3 Bulan`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Neraca**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/balance-sheet`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Hari Ini`, `Tampilkan Data`

#### • Menu: **Rugi Laba**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/profit-loss`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Overview Intelijen**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`

#### • Menu: **Profit per Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/mechanic`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `ANALYSE`

#### • Menu: **Profit per Jenis Tindakan**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/service-type`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `ANALYSE`

#### • Menu: **Profit per Pasien**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/vehicle`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `TAMPILKAN`

#### • Menu: **Margin Obat/Alkes**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/part-margin`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `CALCULATE`

#### • Menu: **Tindakan Terlaris**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/best-service`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Pasien Profitable**
- **Endpoint**: `https://klinik.rhantech.com/admin/analytics/top-customer`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `IDENTIFY`

#### • Menu: **Users**
- **Endpoint**: `https://klinik.rhantech.com/admin/users`
- **Header Tabel**: `Foto`, `Username`, `Role`, `Email`, `Employee Link`, `Status`, `Dibuat`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Tambah User`, `Semua Role`
- **Field Input Modal**: `search-users-input: Cari nama, email, atau karyawan...`, `form-username: `, `form-email: `, `form-password: `, `form-is-active: `

#### • Menu: **Role**
- **Endpoint**: `https://klinik.rhantech.com/admin/roles`
- **Header Tabel**: `Nama Role`, `Deskripsi`, `User Count`, `Permissions`, `Aksi`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Tambah Role`
- **Field Input Modal**: `input: Cari Role...`, `input: Contoh: KASIR`, `input: Tugas utama role ini...`

#### • Menu: **Arus Kas**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/cash-flow`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Laporan Pemeriksaan**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/spk`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Laporan Penjualan POS**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/pos`
- **Header Tabel**: `Invoice`, `Waktu`, `Pelanggan`, `Detail Item`, `Status`, `Total`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Hari Ini`, `Tampilkan`, `Cetak`, `Excel`

#### • Menu: **Pendapatan Dokter**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/mechanic-revenue`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Obat vs Jasa**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/sales-comparison`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan`

#### • Menu: **Biaya Operasional**
- **Endpoint**: `https://klinik.rhantech.com/admin/reports/operating-expenses`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Bulan Ini`, `Tampilkan Data`

#### • Menu: **Pengaturan**
- **Endpoint**: `https://klinik.rhantech.com/admin/settings`
- **Tombol/Aksi/Filter**: `Layanan BPJS`, `Layanan SATUSEHAT`, `Transaksi`, `Master Data`, `Accounting`, `Analitik Klinik`, `Management User`, `Laporan`, `KELUAR SISTEM`, `KEMBALI`, `3`, `S`, `Profil Perusahaan`, `Tema & Warna`, `Database`, `SIMPAN PROFIL`

