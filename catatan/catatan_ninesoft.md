# Analisa Fitur NineSoft Accounting Online

Dokumen ini berisi penjelasan lengkap mengenai fitur-fitur yang tersedia di NineSoft Accounting Online berdasarkan observasi sistem pada URL http://web.ninesoft.id/.

Accounting Online
http://web.ninesoft.id/
User:web@demo.com
Password : admin

---

## 0. Dashboard & Notifikasi Utama
Sistem menyediakan dashboard interaktif untuk pemantauan real-time kondisi bisnis.
- **Widget Monitoring**: Menampilkan grafik dan angka untuk Stok (Nilai Persediaan), Pembelian, Omzet (Sales), Hutang (AP), Piutang (AR), dan Saldo Kas/Bank.
- **Interaktivitas Dashboard**: Seluruh widget utama bersifat *click-through*. Mengklik angka pada widget **STOK**, **OMZET**, atau **PIUTANG** akan mengarahkan pengguna langsung ke laporan detail yang sudah terfilter secara otomatis.
- **Alert & Reminder (To-Do List)**:
    - **Barang Harus Dipesan**: Notifikasi otomatis jika stok di bawah minimum (Reorder Point).
    - **PO Outstanding**: Daftar pesanan ke supplier yang belum diterima/difakturkan.
    - **SO Outstanding**: Daftar pesanan dari customer yang belum dikirim (Surat Jalan).
    - **Piutang Jatuh Tempo**: Daftar tagihan ke pelanggan yang sudah melewati batas kredit.

---

---

## 1. Analisis Mendalam: Menu Master (Header 1)
Menu Master adalah fondasi sistem NineSoft yang mengelola seluruh data entitas bisnis secara hierarkis dan terpusat.

### **A. Barang dan Jasa (Master Item)**
Merupakan modul paling detail yang mencakup 4 tab fungsional untuk setiap item:
- **Identitas Utama (Header & Data Stok)**:
    - **Field Utama**: `Kode Item`, `Nama Item` (Dua baris), `Barcode`, `Satuan Utama`, `Kelompok`, `Gudang Utama`.
    - **Kontrol Persediaan**: `Min Stok`, `Max Stok`, `Min Order`, `Max Order`.
    - **Atribut Khusus**: Checkbox `Rakitan` (untuk barang produksi), `Status (Aktif)`, dan `Tipe Item` (Persediaan/Jasa/Biaya).
- **Tab Harga Jual (Pricing)**:
    - **Multi-Level Pricing**: Mendukung 4 level harga jual (`Harga 1` s/d `Harga 4`) yang dapat dikunci ke tipe pelanggan tertentu.
    - **Referens & Diskon**: Menampilkan `Harga Beli` terakhir sebagai dasar penentuan harga. Mendukung diskon default per item (Rp atau %).
- **Tab Konv Satuan (Unit Conversion)**:
    - Digunakan untuk mengatur rasio unit (misal: 1 `BOX` = 12 `PCS` atau 1 `LUSIN` = 12 `PCS`).
    - Memungkinkan stok tetap akurat saat dibeli dalam satuan besar dan dijual dalam satuan kecil.
- **Tab Mapping Akun (G/L Link)**:
    - Menghubungkan setiap pergerakan item dengan Akuntansi secara otomatis.
    - Slot Akun: `Akun Penjualan`, `Akun Persediaan`, `Akun Diskon Jual`, `Akun HPP`, `Akun Retur Jual`.

### **B. Customer & Supplier**
Manajemen database mitra bisnis dengan fitur kontrol finansial:
- **Plafon & Kontrol Kredit**:
    - **Customer**: Field `Batas Piutang` (Rp) dan `Batas JTempo` (Hari). Jika terlampaui, sistem memberikan peringatan/blokir.
    - **Supplier**: Field `Batas Hutang` (Rp) untuk memantau kewajiban perusahaan.
- **Tab Detail (Multi-Address Management)**:
    - Fitur unggulan di mana satu Customer/Supplier bisa memiliki daftar alamat yang berbeda (Tabel).
    - Memisahkan antara `Alamat Pusat`, `Alamat Tagihan`, dan `Alamat Pengiriman`.
- **Integrasi Pajak**: Field `NPWP`, `Nama PKP`, dan `Alamat Pajak` terintegrasi langsung untuk pembuatan Faktur Pajak.
- **Rayon & Sales**: Fitur pengelompokan wilayah (`Rayon`) dan penugasan `Salesman` pendamping untuk setiap pelanggan.

### **C. Formula (BOM - Bill of Materials)**
Digunakan untuk mendefinisikan struktur produk rakitan (Finished Goods):
- **Header**: Memilih item hasil produksi yang sudah dicentang sebagai `Rakitan` di Master Barang.
- **Detail (Bahan Baku)**: Daftar item bahan baku beserta jumlah yang dibutuhkan untuk membuat satu unit barang jadi.
- **Logika Produksi**: Mendukung pengaturan masa berlaku formula (`Mulai` & `Selesai`).

### **D. Account (Master Akun & COA)**
- **Chart of Accounts (COA)**: Pengelolaan bagan akun hierarkis (Harta, Hutang, Modal, Pendapatan, Biaya).
- **Setting COA (Automatic Mapping)**: Konfigurasi "default akun" untuk seluruh sistem (misal: menentukan akun penampung untuk Uang Muka, Selisih Kurs, s/d Laba Ditahan).
- **Aktiva Tetap (Fixed Assets)**:
    - **Manajemen Aset**: Modul khusus untuk mencatat aset perusahaan (Gedung, Kendaraan, Peralatan).
    - **Metode Penyusutan**: Mendukung metode **Straight Line** (Garis Lurus).
    - **Field Input**: `Nama Asset`, `Nilai Perolehan`, `Masa Tanggal (Start/End)`, `Umur Asset` (Tahun), dan `Rate Depresiasi`.
    - **Mapping Akun**: Menghubungkan aset dengan `Akun Aset`, `Akun Akumulasi Penyusutan`, dan `Akun Biaya Penyusutan`.

---

#### **Detail Laporan Master:**
- **Laporan Master Account (COA)**:
    - **Filter**: Tipe Akun, Level Akun.
    - **Field**: `Kode Akun`, `Nama Akun`, `Tipe`, `Klasifikasi`, `Saldo Normal`.
- **Daftar Barang & Jasa**:
    - **Filter**: Kategori Barang, Status Aktif.
    - **Field**: `Kode Item`, `Nama Item`, `Satuan`, `Gudang Utama`, `Harga Beli Terakhir`, `Harga Jual`.

#### **Form Input (Tambah Barang Baru):**
- **Header**: `Kode Item`, `Nama Item`, `Barcode`, `Satuan` (Unit Utama), `Kelompok` (Kategori), `Lokasi/Gudang`, `Min/Max Stok`, `Jenis` (Barang/Jasa), `HPP Awal`, `Gambar`.
- **Detail (Tab)**:
    - **Account**: Pemetaan akun GL per item.
    - **Harga**: Pengaturan harga jual bertingkat (Harga 1-5).
    - **Saldo Awal**: Input stok awal per gudang.

#### **Form Input (Tambah Customer Baru):**
- **Field**: `Kode & Nama Customer`, `Termin Pembayaran`, `Batas Piutang`, `Salesman`, `No NPWP`, `Alamat Utama`, `Kontak Person`, `COA Piutang` (untuk integrasi GL).

#### **Contoh Data Stok & Pelanggan:**
- **Item**: `ITEM Premium` (Satuan: PCS, Harga: 1.000.000,00), `Buku Sidu` (Satuan: PCS, Harga: 19.000,00).
- **Customer**: `CUSTOMER 009`, `ferdy setiyawan`.

### **B. Customer & Supplier**
- **Jangka Waktu Kredit**: Pengaturan termin pembayaran (Top - Terms of Payment) untuk pelanggan dan pemasok.
- **Pajak**: Pengaturan kode pajak (PPN/PPH) yang berlaku pada transaksi.
- **Customer**: Database pelanggan yang mencakup data kontak, alamat, limit piutang, dan termin.
- **Supplier**: Database pemasok yang mencakup data kontak, alamat, dan termin hutang.

### **C. Fitur Master Lainnya**
- **Alamat**: Manajemen alamat pengiriman dan penagihan yang lebih mendetail.
- **Sales**: Manajemen data tenaga penjual (Salesman) untuk tracking komisi dan performa.
- **Daftar Harga**: Pengaturan price list (harga jual) yang sangat fleksibel:
    - **Multi-Level**: Mendukung hingga **6 level harga** (`Harga 1` s/d `Harga 6`) untuk klasifikasi pelanggan yang berbeda (Retail, Grosir, Distributor).
    - **Impor & Edit**: Mendukung pengubahan harga massal via **Import CSV/Excel** dan pengelompokan berdasarkan `Kelompok Barang`.
    - **Field**: Mencakup `Harga Beli` (untuk proteksi margin), `Harga Jual`, dan pengaturan **Diskon** (Persentase/Nominal) per item.
- **Account (Akuntansi)**:
    - **Klasifikasi Akun**: Pengaturan tipe akun (Harta, Hutang, Modal, Pendapatan, Biaya).
    - **Chart of Account (COA)**: Daftar akun buku besar yang digunakan untuk pencatatan keuangan.
    - **Saldo Awal Akun**: Input saldo awal neraca saat migrasi ke sistem.
    - **Asset (Aset Tetap)**: Fitur khusus untuk mengelola aset perusahaan. Mencakup Nama Aset, Nilai Perolehan, Umur Ekonomis, dan **Metode Penyusutan (Straight Line)**. Sistem akan menghitung biaya penyusutan setiap bulannya secara otomatis.
    - **Setting COA (Mapping Akun)**: Fitur krusial untuk **Otomatisasi Jurnal**. Di sini pengguna memetakan akun-akun GL ke aktivitas transaksi (seperti akun untuk Penjualan, HPP, Persediaan, PPN, Piutang, dll). Hal ini memungkinkan setiap transaksi di modul Penjualan/Pembelian langsung menjurnal ke buku besar tanpa input manual.
- **User Group**: Pengaturan hak akses pengguna berdasarkan grup (Role-based Access Control).
- **Setting Menu**: Kustomisasi tampilan menu dan hak akses per modul bagi user tertentu.
- **Cetakan**: Pengaturan template cetakan faktur, surat jalan, dan dokumen lainnya.
- **Info Whatsapp**: Integrasi sistem dengan Whatsapp untuk pengiriman notifikasi atau dokumen ke pelanggan/supplier.
- **Impor Data Master**: Fasilitas untuk mempercepat migrasi data dengan mengimpor daftar **Akun (COA)**, **Barang**, **Customer**, dan **Supplier** langsung dari file Excel.

---

### 2. Analisis Mendalam: Modul Inventory (Header 2)
Fokus pada pengelolaan siklus hidup stok barang, mulai dari pemantauan saldo, mutasi antar gudang, hingga audit fisik (opname).

### **A. Pusat Informasi Stok (Info Barang Stok)**
Modul monitoring real-time yang menjadi jantung operasional gudang.
- **Dashboard & Filter**:
    - **Filter Utama**: Berdasarkan `Lokasi`, `Gudang` (Spesifik atau Semua), dan `Rentang Tanggal`.
    - **Opsi Tampilan**: Checkbox `Tampilkan Nol` untuk memfilter item yang tidak memiliki saldo.
- **Tab Stok (Real-time Balance)**:
    - **Komponen Saldo**: `Stok Fisik` (ada di gudang), `Order` (sedang dipesan), dan `Sisa` (tersedia untuk dijual).
    - **Valuasi**: Menampilkan `HPP` (Harga Pokok Penjualan) dan `Total Nilai` (Qty x HPP).
    - **Analisa Jual (Integrated Field)**: Kolom pada grid Daftar Stok yang membandingkan `Stok Saat Ini` + `Order` vs `Qty Terjual` dalam periode tertentu untuk mendeteksi potensi stock-out.
- **Tab Summary Stok (Mutasi Periode)**:
    - Rekapitulasi pergerakan barang dalam periode yang dipilih.
    - Format: `Stok Awal` + `Masuk` - `Keluar` = `Stok Akhir`.
- **Tab Kartu Stok (Audit Trail)**:
    - Riwayat mendetail per item secara kronologis.
    - Field: `Tanggal`, `Nomor Transaksi`, `No Referensi`, `Pihak/Relasi`, `Masuk`, `Keluar`, `Saldo`, dan `Gudang`.
    - **Fitur Khusus: Kartu Stok Semua Gudang**: Kemampuan untuk melihat mutasi kartu stok lintas seluruh gudang dalam satu tampilan tunggal, sangat membantu untuk rekonsiliasi total stok fisik perusahaan.
    - **Filter Lanjutan**: Mendukung pemilihan rentang tanggal spesifik, kategori barang, serta pemilihan satu atau beberapa gudang sekaligus (misal: PUSAT & CABANG A).
    - **Output & Distribusi**: Selain cetak dan Excel, laporan kartu stok dapat dikirimkan langsung melalui **Email** dari dalam sistem.
    - Dilengkapi ringkasan total mutasi di bagian bawah tabel.

### **B. Mutasi Stok (Pindah Gudang)**
Digunakan untuk mencatat perpindahan fisik barang antar lokasi internal perusahaan.
- **Form Header**:
    - `No Mutasi` (Auto-generated), `Tanggal`, `Gudang Asal`, `Gudang Tujuan`, `No Referensi`, `Keterangan`.
- **Form Detail (Grid)**:
    - Pemilihan `Item` (via Browse/Barcode), `Qty`, `Satuan`, dan `Keterangan Baris`.
- **Efek Sistem**: Saldo Gudang Asal berkurang dan Saldo Gudang Tujuan bertambah secara instan saat data disimpan. Tidak ada efek ke jurnal akuntansi jika harga barang tetap (hanya pindah lokasi).

### **C. Koreksi & Penyesuaian (Stok Adjustment)**
Mengelola perubahan stok akibat faktor non-transaksi (seperti barang rusak atau bonus supplier).
- **Logika Akuntansi**:
    - Membutuhkan pemilihan **Akun GL (COA)** sebagai akun pengimbang (misal: Biaya Barang Rusak).
- **Fitur 'Adjust Nilai' (Checkbox)**:
    - **Jika Centang OFF**: Fokus pada koreksi **Kuantitas** (mempengaruhi saldo unit).
    - **Jika Centang ON**: Memungkinkan koreksi **Nilai Persediaan (HPP)** tanpa mengubah jumlah fisik barang.
- **Formulir**: Mencakup `Gudang`, `No Bukti`, `Tanggal`, dan tabel detail item beserta kolom `Alasan/Uraian`.

### **D. Audit Stok (Stok Opname)**
Fitur krusial untuk sinkronisasi data sistem dengan kenyataan fisik di lapangan.
- **Mekanisme Perhitungan**:
    - **Stok Sistem**: Angka saldo menurut aplikasi (Read-only).
    - **Stok Fisik**: Angka yang diinput oleh user setelah hitungan fisik.
    - **Selisih**: Otomatis dihitung (`Fisik` - `Sistem`). Selisih ini yang akan diposting sebagai adjustment.
- **Fitur Efisiensi**:
    - **Bulk Import**: Tombol `Excel` untuk mengunggah hasil hitungan fisik dalam jumlah besar.
    - **Barcode Search**: Input barcode di form untuk pencatatan cepat.
    - **Stok Baru**: Menambahkan item yang ditemukan di gudang tetapi sebelumnya belum terdaftar di sistem.
- **Workflow**: Biasanya dilakukan pada saat "Cut-off" atau akhir periode sebelum proses closing.

### **E. Perakitan (Assembly) & BOM Integration**
Menangani proses produksi sederhana atau rebranding barang.
- **Pemicu**: Eksekusi menu perakitan akan memanggil data `Formula (BOM)` dari Master.
- **Input**: User memilih `Item Hasil` dan jumlah yang akan dibuat.
- **Auto-Explosion**: Sistem otomatis menarik daftar `Item Bahan` beserta kuantitas standar yang dibutuhkan.
- **Logika HPP**: Nilai HPP Barang Jadi secara otomatis diakumulasikan dari total HPP seluruh Bahan Baku yang digunakan.

### **F. Karakteristik Antarmuka (UI Patterns)**
- **Consistent Action Bar**: Seluruh formulir memiliki toolbar icon di pojok kanan atas untuk: `Excel (Export/Import)`, `Baru`, `Hapus`, `Simpan`, `Batal`, dan `Cetak`.
- **Smart Grid**: Tabel detail mendukung pencarian item instan dan manajemen baris yang responsif.
- **Audit Info**: Tombol `Info` di setiap transaksi menunjukkan log pembuat dan waktu perubahan data.

---

## 3. Analisis Mendalam: Modul Pembelian (Purchasing)
Mengelola siklus pengadaan barang mulai dari pemesanan (PO), penerimaan barang, hingga pelunasan kewajiban (Hutang).

### **A. Info Pembelian (Dashboard & Monitoring)**
Pusat kendali untuk memantau status pengadaan dan kesehatan finansial terkait supplier.
- **Visualisasi Utama**:
    - **Grafik Umur Hutang (Aging)**: Analisis jatuh tempo hutang untuk manajemen arus kas.
    - **Grafik Pembelian**: Tren nilai pembelian harian untuk memantau intensitas pengadaan.
- **Tracking Area (Tabulated View)**:
    - **Tab PO**: Monitoring pesanan yang masih outstanding (belum diterima).
    - **Tab Pembelian & Retur**: Daftar lengkap mutasi invoice dan pengembalian barang.
    - **Tab Pelunasan**: Rekapitulasi histori pembayaran yang telah dilakukan.
    - **Tab Kartu Hutang**: Buku pembantu hutang per supplier untuk rekonsiliasi saldo.
    - **Fitur Khusus: Summary Outstanding Uang Muka**: Laporan untuk melacak saldo *Down Payment* (DP) yang telah dibayarkan ke supplier namun belum dikompensasi oleh faktur pembelian.

### **B. Order Pembelian (PO - Purchase Order)**
Dokumen komitmen pemesanan yang belum mempengaruhi stok maupun laporan keuangan.
- **Field Header**:
    - `No PO` (Auto), `Tanggal`, `Supplier`, `Gudang`, `Termin Pembayaran`, `Tanggal Dibutuhkan`, `No Referensi`, dan `Keterangan`.
- **Field Detail (Grid)**:
    - `Item`, `Qty`, `Satuan`, `Harga Input`, `Diskon (Rp/%)`, `Subtotal`, dan `Keterangan Baris`.
- **Fitur Khusus**: Tombol `Browse Item` untuk pemilihan barang masal dan `Info PO-INVOICE` untuk cek status pemenuhan order.

### **C. Faktur Pembelian (Purchase Invoice)**
Modul krusial untuk pengakuan persediaan masuk (stok) dan pengakuan kewajiban (hutang).
- **Logika Integrasi (Matching)**:
    - Menyediakan tombol **`Pilih No PO`** untuk menarik data pesanan. Hal ini memastikan konsistensi harga dan jumlah antara pesanan dan penerimaan.
- **Sistem 4 Tab Utama**:
    - **Tab 1: Detail**: Input item komoditas (Barang/Jasa).
    - **Tab 2: Uang Muka**: Mengompensasi cicilan/DP yang telah dibayarkan sebelumnya untuk memotong total tagihan faktur ini.
    - **Tab 3: Pelunasan**: Fitur pembayaran langsung di tempat. Field: `Voucher`, `Akun Kas/Bank`, `No Cek/Giro`, `Potongan`, dan `Jumlah Bayar`.
    - **Tab 4: Info PO-INVOICE**: Menampilkan perbandingan per baris item antara kuantitas di PO vs kuantitas yang difakturkan.

### **D. Retur Pembelian (Faktur Retur Beli)**
Mencatat pengembalian barang ke pemasok.
- **Mekanisme Referensi**: Sistem memungkinkan pemilihan `No Faktur` asal agar nilai retur secara otomatis memotong saldo hutang pada faktur spesifik tersebut (bukan sekadar memotong saldo global supplier).
- **Efek Transaksi**: Mengurangi stok barang di gudang dan mengurangi saldo akun Hutang Dagang di Buku Besar (GL).

### **E. Pelunasan Hutang (Settlement)**
Proses penyelesaian tagihan melalui modul Kas & Bank (via menu Kas/Bank > Bukti Kas Keluar).
- **Alur Kerja**:
    1. Pilih `Supplier` dan `Akun Kas/Bank` sumber dana.
    2. Gunakan fitur **`Browse Nota`** untuk menarik seluruh faktur yang belum lunas (Outstanding).
    3. Input jumlah `Bayar` dan `Potongan` (jika ada diskon pembayaran dipercepat).
- **Manajemen Giro**: Field `No Check/Giro` dan `Tgl Jatuh Tempo` untuk melacak status pembayaran yang belum cair.
- **Tab Lain-lain**: Digunakan untuk mencatat biaya admin bank atau pembulatan nilai saat pelunasan.

### **F. UI Patterns & Busines Logic**
- **Credit Limit Warning**: Notifikasi atau blokir otomatis jika total hutang ke supplier melampaui `Batas Hutang` yang diset di Master.
- **Automatic Journaling**: Setiap simpan data Faktur/Retur akan langsung membentuk jurnal akuntansi secara real-time berdasarkan mapping akun GL per item atau kelompok barang.

---

---

## 4. Analisis Mendalam: Modul Penjualan (Sales)
Mengelola seluruh siklus pendapatan pelanggan mulai dari pesanan (SO), pengiriman barang (Surat Jalan), hingga penagihan (Invoice) dan pelunasan piutang.

### **A. Info Penjualan (Dashboard & Tracking)**
Pusat pemantauan kinerja penjualan dan kesehatan piutang dagang.
- **Analisis Visual**:
    - **Grafik Penjualan**: Tren harian omzet untuk melihat fluktuasi pasar.
    - **Fast Moving Item**: Analisis produk terlaris berdasarkan kuantitas dan kategori untuk optimasi stok.
    - **Umur Piutang (Aging AR)**: Ringkasan saldo piutang berdasarkan usia tagihan untuk meminimalisir risiko piutang tak tertagih.
- **Monitoring Operasional**: Tab khusus untuk melacak status Sales Order (SO) dan Surat Jalan (SJ) yang belum difakturkan.

### **B. Sales Order (SO - Pesanan Penjualan)**
Dokumen awal untuk mencatat pesanan pelanggan sebagai dasar perencanaan stok dan pengiriman.
- **Field Header**:
    - `No SO` (Auto-generated), `Tanggal`, `Customer`, `Lokasi`, `Termin`, `Salesman`, `No Referensi (Manual)`, dan `Keterangan`.
- **Field Detail (Grid)**:
    - `Barcode/Item`, `Nama Item`, `Qty`, `Satuan`, `Harga Jual`, `Diskon (%)`, dan `Subtotal`.
- **Logika**: SO belum memotong stok fisik maupun stok di Buku Besar, namun menjadi "dokumen induk" bagi proses selanjutnya.

### **C. Surat Jalan (SJ - Delivery Order)**
Dokumen instruksi pengeluaran barang yang secara otomatis mengurangi saldo fisik di gudang.
- **Header Khusus**: Menambahkan informasi `Gudang` sumber barang dan identifikasi `Customer`.
- **Integrasi Pintar**: Tombol **`Pilih No SO`** memungkinkan bagian gudang menarik item secara akurat dari pesanan yang telah disetujui.
- **Efek Sistem**: Stok fisik berkurang saat SJ disimpan. Status SJ akan dipantau hingga menjadi Faktur Penjualan.

### **D. Faktur Penjualan (Sales Invoice)**
Modul pengakuan pendapatan dan timbulnya piutang dagang.
- **Fleksibilitas Jalur (Workflow)**:
    - **Jalur SO-Invoice**: Menarik data langsung dari SO (via `Pilih No SO`).
    - **Jalur SJ-Invoice**: Menarik data dari pengiriman yang sudah dilakukan (via `Browse No SJ`).
- **Struktur 4 Tab Utama**:
    - **Tab 1: Detail**: Grid input item atau impor dari dokumen referensi.
    - **Tab 2: Uang Muka**: Memotong total tagihan dengan deposit/uang muka yang telah dibayar pelanggan.
    - **Tab 3: Pelunasan**: Fitur kasir instan untuk input pembayaran tunai/kartu saat faktur diterbitkan.
    - **Tab 4: Info SO-SJ-INVOICE**: Laporan sinkronisasi per item untuk memastikan konsistensi kuantitas antara Pesanan, Pengiriman, dan Faktur.

### **E. Penjualan Barcode (POS - Point of Sale)**
Antarmuka khusus ritel untuk transaksi cepat di meja kasir.
- **Karakteristik POS**:
    - Desain minimalis dengan input terpusat pada field **Barcode**.
    - Setiap scan barcode otomatis menambah baris dan kuantitas item.
    - Mendukung cetak struk ke printer thermal secara langsung.

### **F. Pelunasan Piutang (Collection)**
Proses penerimaan uang dari pelanggan melalui menu Kas/Bank > Bukti Kas Masuk (BKM).
- **Alur Kerja**:
    1. Pilih `Customer` dan `Akun Kas/Bank` penampung dana.
    2. Klik **`Browse Nota`** untuk menampilkan daftar faktur yang masih memiliki saldo piutang.
    3. Masukkan jumlah `Bayar` (Lunas atau Parsial) dan `Potongan` (jika ada diskon pembayaran atau pinalti).
- **Pembayaran Non-Tunai**: Field untuk mencatat `No Check/Giro`, `Bank`, dan `Tanggal Jatuh Tempo` untuk rekonsiliasi dana masuk.

### **G. UI Patterns & Business Logic**
- **Credit Control**: Sistem memperingatkan atau memblokir transaksi jika piutang pelanggan melampaui `Batas Piutang` atau `Batas Jatuh Tempo`.
- **Pricing Strategy**: Harga jual otomatis terisi berdasarkan kaitan antara `Item` dan `Level Harga` pada Master Pelanggan (Harga 1-5).
- **Sales Performance**: Setiap transaksi terikat dengan data `Salesman` untuk kebutuhan perhitungan komisi dan analisis performa tim sales.

---

---

### 5. Analisis Mendalam: Modul Kas/Bank (Finance)
Pusat pengelolaan arus kas (Cash Management) yang mencakup seluruh aktivitas penerimaan, pengeluaran, serta rekonsiliasi instrumen perbankan (Cek/Giro).

### **A. Info Kas Bank (Monitoring Likuiditas)**
Dashboard real-time untuk memantau posisi dana dan tren arus kas perusahaan.
- **Visualisasi Arus Kas**:
    - **Grafik Penerimaan & Pengeluaran**: Perbandingan performa cash-in dan cash-out antar periode (bulan ini vs bulan lalu).
- **Saldo Kas/Bank**: Ringkasan saldo akhir untuk setiap akun keuangan (Kas Tunai, Rekening Bank, Petty Cash) di satu layar utama.

### **B. Bukti Kas Masuk (BKM - Cash Receipt)**
Modul untuk mencatat seluruh uang yang masuk ke dalam sistem perusahaan.
- **Komponen Header**:
    - `No Voucher`, `Tanggal`, `Akun Kas/Bank` (Tujuan dana), `Dari (Customer/Pihak Lain)`, `Total`, `No Cek/Giro`, and `Tanggal Jatuh Tempo`.
- **Integrasi Transaksi (Detail Tab)**:
    - **Tab Detail (Piutang)**: Menggunakan fitur **`Browse Nota`** untuk menarik daftar faktur penjualan yang akan dilunasi. Mendukung input `Potongan` (Diskon) dan nominal `Terima`.
    - **Tab Lain-lain**: Untuk pencatatan pendapatan non-operasional (seperti Bunga Bank atau Penjualan Aset) dengan pemetaan langsung ke akun GL.
    - **Tab Hutang**: Memungkinkan pencatatan pengembalian dana dari supplier (Retur Beli Tunai).

### **C. Bukti Kas Keluar (BKK - Cash Disbursement)**
Modul untuk mencatat seluruh pengeluaran dana perusahaan.
- **Alur Operasional**:
    - **Pelunasan Hutang**: Melunasi faktur pembelian supplier dengan menarik data via `Browse Nota`.
    - **Biaya Operasional**: Mencatat beban usaha (Gaji, Listrik, Sewa, dll) melalui tab `Lain-lain`.
- **Fitur Pendukung**: Tersedia tombol cepat untuk menginput `Biaya Transfer` bank atau menangani `Tolakan` giro secara sistematis.

### **D. Tracking Cek & Giro (BG Masuk/Keluar)**
Manajemen siklus hidup instrumen pembayaran nontunai yang memiliki masa jatuh tempo.
- **Siklus Status**:
    - **NEW**: Status awal saat giro dicatat namun belum masuk tanggal efektif pencairan.
    - **CAIR (Cleared)**: Setelah proses kliring bank, status diubah menjadi CAIR untuk memperbarui saldo akun bank di Buku Besar.
    - **TOLAK (Rejected)**: Untuk mencatat giro yang ditolak oleh bank (dana tidak cukup/salah data).
- **Audit Tool**: Filter radio button (NEW, CAIR, TOLAK) memudahkan akuntan untuk memantau beban atau potensi dana yang masih menggantung.

### **E. Laporan Buku Kas-Bank (Account Ledger)**
Laporan kronologis mutasi untuk setiap akun kas atau bank.
- **Struktur Laporan**: Mencakup `Tanggal`, `No Voucher`, `Keterangan`, `Penerimaan (Debit)`, `Pengeluaran (Credit)`, dan `Saldo Kumulatif`.
- **Opsi Output**: Tersedia dalam layout Portrait/Landscape untuk kebutuhan audit dan rekonsiliasi dengan rekening koran (Bank Statement).

### **F. UI Patterns & Logic**
- **Voucher Prefixing**: Penomoran voucher sering kali memiliki prefix unik berdasarkan jenis akun (misal: 'K' untuk Kas, 'B' untuk Bank).
- **Split-Row Entry**: Satu voucher BKM/BKK dapat digunakan untuk melunasi banyak nota sekaligus atau membagi satu pengeluaran ke beberapa akun biaya yang berbeda.

---

## 6. Analisis Mendalam: Modul GL (General Ledger)
Modul inti akuntansi untuk pelaporan keuangan, pencatatan jurnal penyesuaian, dan finalisasi periode pembukuan (Closing).

### **A. Transaksi Jurnal (General Journal)**
Digunakan untuk mencatat transaksi yang tidak tercover oleh modul operasional khusus atau untuk jurnal penyesuaian (Adjustment).
- **Header Formulir**:
    - `No Jurnal` (Otomatis), `Tanggal`, `No Referensi`, dan `Keterangan` (untuk narasi global transaksi).
- **Detail Line (Double-Entry Logic)**:
    - **Akun**: Pemilihan akun via `Deskripsi Account` atau `Kode Account`.
    - **Uraian**: Keterangan spesifik untuk setiap baris posting.
    - **Debet & Kredit**: Input nilai moneter.
- **Kontrol Integritas**: Sistem menyediakan ringkasan `Total Debet`, `Total Kredit`, dan **`Selisih`** secara real-time. Data hanya dapat disimpan jika kondisi jurnal seimbang (Selisih = 0).

### **B. Closing Periode Akuntansi (Penutupan Buku)**
Fitur untuk mengunci transaksi dan memfinalisasi laporan keuangan bulanan/tahunan.
- **Workflow Closing**:
    1. Sistem menampilkan daftar 12 bulan dalam satu tahun fiskal beserta statusnya (`New/Open` atau `Closed`).
    2. Tombol **`Closing`** akan mengeksekusi penguncian data dan pemindahan saldo laba/rugi ke akun modal (Retained Earnings).
- **Fitur Undo**: Memungkinkan pembukaan kembali periode yang terkunci jika diperlukan koreksi masa lalu (memerlukan otoritas supervisor).

### **C. Rekalkulasi Sistem (Fasilitas > Reproses)**
Serangkaian fungsi pemeliharaan data untuk menjamin sinkronisasi antara operasional dan akuntansi.
- **Reproses Stok**: Sinkronisasi ulang saldo fisik di gudang dengan catatan mutasi kartu stok.
- **Reproses HPP**: Menghitung kembali nilai perolehan persediaan dan Harga Pokok Penjualan (HPP) untuk akurasi laporan laba rugi.
- **Reproses Accounting**: Melakukan pemindaian ulang seluruh transaksi operasional untuk memastikan semuanya telah terposting dengan benar ke Buku Besar (GL).
- **Penting**: Reproses ini wajib dijalankan setiap akhir bulan sebelum melakukan proses **Closing**.

### **D. Laporan Neraca Saldo (Trial Balance)**
Laporan komprehensif yang menampilkan seluruh akun beserta pergerakannya.
- **Komponen Laporan**:
    - **Saldo Awal**: Posisi keuangan pada awal periode.
    - **Mutasi (Debet/Kredit)**: Total aktivitas selama periode yang dipilih.
    - **Saldo Akhir**: Ringkasan posisi keuangan terakhir yang siap disajikan dalam Neraca atau Laba Rugi.
- **Fleksibilitas**: Laporan dapat diekspor ke Excel dengan format yang tetap terjaga hierarki akunnya (Level 1-5).

### **E. UI Patterns & Logic**
- **Real-time Journaling**: Setiap simpan data di modul Penjualan, Pembelian, atau Kas/Bank akan langsung memicu pembuatan jurnal otomatis di background tanpa perlu proses "posting" manual.
- **Drill-down Capability**: Pada laporan digital, user dapat melakukan klik pada nomor dokumen untuk melihat detail formulir transaksi asal (traceability).

## 7. Analisis & Laporan Manajerial (Analytic Center)
Menu ini merupakan pusat informasi strategis bagi pemilik bisnis dan akuntan untuk mengevaluasi performa perusahaan secara mendalam.

### **A. Laporan Keuangan (Financial Statements)**
Informasi yang disajikan mencakup:
- **Laba Rugi (Profit & Loss)**:
    - **Varian**: Laba Rugi per Item, per Customer, per GL (Buku Besar), per Sales, serta Perbandingan Tahunan (Annual Comparison).
- **Neraca (Balance Sheet)**:
    - **Varian**: Neraca Standar, Common Sized (Persentase), Perbandingan 2 Bulan, dan Perbandingan Tahunan.
- **Arus Kas (Cash Flow)**: Melacak sumber dan penggunaan dana secara real-time.
- **Neraca Saldo (Trial Balance)**: Ringkasan saldo seluruh akun untuk audit.

### **B. Laporan Manajerial (Business Performance)**
Fokus pada indikator kunci keberhasilan bisnis:
- **10 Top Customers**: Analisis pelanggan dengan kontribusi pendapatan terbesar.
- **Fast & Slow Moving Items**: Daftar hingga 500 item terbaik/terburuk untuk rotasi stok yang optimal.
- **Analisis Laba Kotor**: Membedah margin per penjualan atau per pelanggan.
- **Laba Rugi Item / Barang (%)**: Visualisasi margin kotor per barang dalam bentuk persentase, memudahkan identifikasi barang paling menguntungkan (*High Margin*).
- **Laba Rugi Per Sales**: Laporan performa profitabilitas berdasarkan tim sales, melampaui sekadar omzet kotor.
- **Laba Rugi Nota By Surat Jalan**: Analisis laba berdasarkan pengiriman barang, sangat kritikal bagi bisnis dengan jeda waktu antara kirim barang dan penagihan faktur.
- **Laba/Rugi Penjualan Per Item**: Laporan mendalam yang menghitung margin kotor per barang dengan filter berdasarkan Kelompok Barang dan Gudang tertentu.
- **Analisis Konsistensi**: Laporan yang membandingkan Qty Pesanan vs Qty Kirim vs Qty Faktur untuk mendeteksi kehilangan atau selisih pengiriman.

### **C. Karakteristik Output**
- **Tanpa Grafik Statis**: Menu Analisa mengandalkan tabel data mendetail, sementara visualisasi tren (grafik) dipusatkan di halaman **Dashboard Utama**.
- **Filter Fleksibel**: Mendukung pencarian per Periode (Bulan/Tahun), Cabang/Lokasi, dan opsi pengurutan (Sorting).
---

## 8. Fasilitas & Konfigurasi Sistem
Bagian ini menjelaskan bagaimana sistem NineSoft dikonfigurasi untuk menyesuaikan kebutuhan bisnis.

### **A. Persiapan Singkat (Profil Perusahaan)**
Digunakan untuk mengatur identitas usaha yang akan muncul pada laporan dan faktur.
- **Akses**: Fasilitas > Persiapan Singkat.
- **Field**: `Nama Perusahaan`, `Email`, `Alamat` (Baris 1 & 2), `NPWP`, `No Seri Faktur Pajak`, `No Pengukuhan PKP`, `Tgl Pengukuhan PKP`, `Logo`.

### **B. Setup Sistem (Konfigurasi Global)**
Mengatur perilaku sistem secara menyeluruh.
- **Akses**: Fasilitas > Setup.
- **Tab General**:
    - `Tanggal Mulai Program`: Batas awal sistem mulai mencatat transaksi.
    - `Format No Transaksi`: Pilihan format penomoran (Bulanan atau Tahunan).
    - `Jumlah Desimal`: Pengaturan presisi angka (0 s/d 4 desimal).
    - `Sistem Jual/Beli`: Pilihan alur (Gunakan Surat Jalan atau Langsung Faktur).
    - `Maksimum Cetak`: Limitasi pengulangan cetak dokumen.
- **Tab Transaksi**:
    - `Stok Boleh Minus`: Toggle krusial untuk mengizinkan input penjualan meski saldo sistem belum diupdate (cocok untuk ritel cepat).
    - `Matching PO/SO`: Validasi Qty Faktur terhadap Qty Pesanan untuk mencegah over-delivery.
    - `Limit Hutang Piutang`: Kontrol kredit proaktif dengan sistem blokir/peringatan.
    - `HPP Display`: Opsi untuk menyembunyikan atau menampilkan nilai modal di layar POS/Sales untuk privasi.
    - `Tampilkan Note For Today`: Pengaturan pengingat tugas harian.
- **Tab Web (Integration & Output)**:
    - `Mode Print`: Pilihan 'Dengan Preview' atau 'Direct Print'.
    - `Jenis Form`: Pilihan layout (Besar/Kecil/Thermal).
    - **Bluetooth Printer Support**: Dukungan khusus untuk pencetakan struk dari browser mobile/web.
    - `Sistem Pembelian`: Pilihan antara 'Tanpa Barang Datang' (Langsung Invoice) atau 'Dengan Barang Datang' (Prosedur PO-SJ-Inv).

### **C. Manajemen Pengguna (Security)**
Mengatur siapa saja yang bisa mengakses sistem dan sejauh mana hak aksesnya.
- **User Group**:
    - **Field**: Nama Group, Deskripsi.
    - **Otoritas**: Pengaturan hak akses granular (**VIEW, ADD, EDIT, DELETE**) untuk setiap modul di sistem.
- **Data User**:
    - **Field**: `Username`, `Email`, `Password`, `Full Name`, `Jabatan`, `Masa Aktif Akun`.
    - **Izin Khusus**: Checkbox `Admin` (Semua akses), `Excel` (Izin export data), dan `Portal`.

### **D. Pengaturan Menu & Dokumen**
- **Setting Menu**: Mengatur prefix nomor otomatis (misal: 'FJ' untuk Faktur Jual), izin `Edit Tanggal` transaksi, dan otomatisasi baris (`Auto Insert`).
- **Custom Cetakan**: Editor layout visual untuk kustomisasi dokumen perusahaan (Faktur, SJ, PO, dll).
    - **Mekanisme Editor**: Berbasis **Grid Designer** di mana setiap elemen dapat diatur posisi (*Layout*) dan isinya (*Content*).
    - **Placeholders**: Menggunakan label dinamis dalam kurung siku, contoh: `[NAME]`, `[ADDRESS1]`, `[DOCUMENT_NO]`, `[DOCUMENT_DATE]`.
- **Cetak Barcode**: Pengaturan label barcode barang untuk kebutuhan inventaris fisik.

### **E. Pemeliharaan & Audit (Maintenance)**
- **History Transaksi (Audit Log)**: Mencatat setiap aktivitas user di sistem. Data mencakup `Tanggal`, `Menu`, `Tipe Aksi` (ADD/EDIT/DELETE), `No Dokumen`, dan `Username` pelaku.
- **Rekalkulasi Sistem**: Fitur untuk memperbaiki integritas data jika terjadi ketidaksinkronan:
    - `Hitung Ulang Stok`: Sinkronisasi fisik vs kartu stok.
    - `Hitung Ulang HPP`: Menghitung kembali harga pokok penjualan berdasarkan histori transaksi.
    - `Hitung Saldo Accounting`: Sinkronisasi mutasi jurnal dengan saldo buku besar.
- **Database Tools**: Fasilitas untuk `Restore Database` dan `Database Patching` untuk pembaruan sistem.

---

## 9. Alur Proses Bisnis NineSoft (End-to-End)
Berikut adalah urutan standar penggunaan aplikasi NineSoft mulai dari persiapan data hingga penutupan buku bulanan.

### **Tahap 1: Persiapan & Input Data Master**
1.  **Setup Awal**: Mengatur profil perusahaan dan sistem (Fasilitas > Setup).
2.  **Chart of Accounts (COA)**: Menyusun daftar akun dan melakukan mapping akun otomatis (Setting COA).
3.  **Data Induk**: Menginput (atau impor via Excel) data Barang, Supplier, dan Customer.
4.  **Saldo Awal**: Menginput Saldo Awal Akun (Neraca) dan Saldo Awal Stok per gudang untuk memulai periode pertama.

### **Tahap 2: Siklus Operasional Harian**
1.  **Siklus Pembelian**:
    *   Buat **Order Pembelian (PO)** ke supplier.
    *   Input **Faktur Pembelian** (mengurangi hutang & menambah stok).
    *   Lakukan **Pelunasan Hutang** via Kas/Bank jika pembayaran sudah dilakukan.
2.  **Siklus Penjualan**:
    *   Terima pesanan via **Sales Order (SO)**.
    *   Kirim barang via **Surat Jalan (SJ)** (mengurangi stok fisik).
    *   Terbitkan **Faktur Penjualan** (mengakui piutang & pendapatan).
    *   Input **Pelunasan Piutang** saat uang diterima dari pelanggan.
3.  **Kas/Bank & Memorial**:
    *   Input biaya operasional via **Bukti Kas Keluar**.
    *   Input penyesuaian non-kas via **Transaksi Jurnal** (GL).

### **Tahap 3: Prosedur Akhir Bulan (Period-End)**
Sebelum melakukan tutup buku, user **WAJIB** menjalankan fitur rekalkulasi di menu **Fasilitas** untuk memastikan integritas data:
1.  **ReProses Stok**: Menghitung ulang saldo stok fisik di seluruh gudang.
2.  **ReProses HPP**: Menghitung kembali nilai Harga Pokok Penjualan berdasarkan metode valuation (FIFO/Average) agar laba kotor akurat.
3.  **ReProses Accounting**: Sinkronisasi seluruh transaksi ke dalam Buku Besar (GL).

### **Tahap 4: Penutupan Buku (Closing)**
1.  **Closing Periode**: Masuk ke menu `GL > Closing Periode Akuntansi`.
2.  **Eksekusi**: Pilih bulan dan tahun yang akan ditutup, lalu klik **Closing**.
3.  **Efek Closing**:
    *   Sistem akan mengunci transaksi pada bulan tersebut (tidak bisa edit/hapus).
    *   Saldo Laba/Rugi akan dipindahkan ke akun Laba Ditahan (Retained Earnings).
    *   Saldo akhir neraca bulan ini akan menjadi saldo awal bulan berikutnya.

---

## 10. Pola UI, Aksi, dan Fitur Ekspor
NineSoft memiliki pola antarmuka yang konsisten di seluruh modul untuk memudahkan navigasi dan pengolahan data.

### **A. Fitur pada Daftar (List View)**
Setiap tabel daftar (misal: Daftar Barang, Daftar Faktur) memiliki aksi standar:
- **Tombol AKSI (Kaca Pembesar)**: Membuka formulir detail untuk melakukan pengeditan atau sekadar melihat data.
- **Icon Plus Hijau (+)**: Fitur "Expander" untuk melihat detail baris tanpa pindah halaman (misal: melihat rincian stok per gudang atau rincian jurnal).
- **Import Excel**: Tombol di pojok kanan atas untuk unggah data massal (bulk upload).
- **Accordion History**: Menampilkan log perubahan data langsung di bawah daftar filter.

### **B. Fitur pada Formulir Transaksi (Input Form)**
Saat mengisi data atau transaksi, tersedia tombol-tombol aksi berikut:
- **Toolbar Utama**:
    - **Simpan & Undo**: Untuk manajemen status data.
    - **Hapus**: Untuk membatalkan/menghapus transaksi (tergantung hak akses).
    - **Cetak (Dropdown)**: Pilihan format cetak yang beragam (A4, Half Letter, Surat Jalan, dll).
    - **Excel**: Ekspor data transaksi yang sedang dibuka langsung ke file Excel.
    - **Info (Audit Log)**: Membuka jendela pop-up yang menunjukkan siapa pembuat data dan kapan terakhir kali diubah.
- **Aksi pada Tabel Detail (Grid)**:
    - **Browse Item**: Membuka katalog pencarian barang.
    - **Pilih Ref (SO/SJ/PO)**: Menarik data dari dokumen referensi untuk mempercepat input.
    - **Delete All**: Menghapus seluruh baris detail sekaligus.
- **Quick Add (+)**: Icon plus biru di samping dropdown (misal: Satuan, Kelompok) untuk menambah data master baru tanpa harus keluar dari formulir transaksi.

### **C. Fitur pada Laporan (Reporting)**
Setiap laporan memiliki opsi output yang fleksibel:
- **Preview**: Menampilkan laporan di layar browser sesuai filter yang dipilih.
- **Export Excel**: Mengunduh laporan dalam format `.xlsx` dengan tata letak yang rapi.
- **Cetak**: Mengirim laporan langsung ke mesin pencetak.
- **Kirim Email**: Fitur untuk mengirim laporan sebagai lampiran email langsung dari sistem.
- **Layout Control**: Opsi pemilihan tampilan *Portrait* atau *Landscape* sebelum laporan dibuat.

### **D. Fitur Impor Data Massal**
NineSoft menyediakan fitur impor untuk mempercepat pengisian data dalam jumlah besar. Fitur ini tidak terpusat di satu menu, melainkan tersebar di setiap daftar modul terkait:
- **Lokasi Tombol**: Berada di pojok kanan atas setiap List View (Daftar), biasanya berwarna biru/abu-abu dengan label **"Import"**.
- **Data yang Mendukung Impor**:
    - **Master Barang & Jasa**: Menggunakan format `.csv` (Template: `IMPORT_WEB_ITEM.csv`).
    - **Master Customer & Supplier**: Menggunakan format `.xlsx` (Template: `customer.xlsx`).
    - **Faktur Pembelian & Penjualan**: Mendukung impor transaksi langsung dari Excel (Template: `beli.xlsx` / `jual_faktur_sj.xlsx`).
    - **Transaksi Jurnal (GL)**: Mendukung impor jurnal umum via `.csv` (Template: `IMPORT_WEB_GL.csv`).
- **Mekanisme Templat**: Setiap jendela pop-up impor menyediakan tombol **"Download Template"** (biasanya berwarna kuning) untuk memastikan data yang diunggah sesuai dengan struktur kolom sistem.

---

## 11. Fitur Lanjutan & Batasan Sistem
Berdasarkan observasi mendalam, berikut adalah fitur akuntansi lanjutan serta beberapa batasan sistem yang perlu diperhatikan:

### **A. Pengelolaan Pajak (Taxes)**
- **Metode Perhitungan**: Mendukung sistem **PPN (Exclusive)** dan **INCL (Inclusive)** yang dapat dipilih per transaksi.
- **Multiple Rates**: User dapat mendefinisikan berbagai kode pajak (misal: PPN 11%, PPH 23) dengan akun GL yang berbeda.
- **Identitas Pajak**: Pengaturan NPWP, No Seri Faktur Pajak, dan Tanggal PKP tersedia secara global di Setup Perusahaan.

### **B. Karakteristik Posting & Keamanan**
- **Real-time Posting**: Sistem NineSoft tidak memiliki tombol "Post". Setiap transaksi yang disimpan (Save) akan langsung memperbarui saldo Buku Besar (GL) dan laporan keuangan secara instan.
- **Audit Trail**: Melalui tombol **Info** di setiap formulir, sistem menyimpan jejak audit lengkap tentang siapa yang membuat dan kapan data terakhir diubah.
- **Kontrol Periode**: Keamanan data historis dijamin melalui fitur **Closing Periode** yang mengunci bulan-bulan sebelumnya dari perubahan.

### **C. Batasan Sistem (Varian Demo)**
- **Multi-Currency**: Sistem saat ini hanya mendukung satu mata uang induk (**IDR**). Tidak ditemukan fitur input mata uang asing atau rekalkulasi selisih kurs.
- **Project/Department**: Tidak tersedia fitur tracking biaya atau pendapatan per Proyek atau per Departemen secara eksplisit. Tracking terbatas pada pemisahan per **Gudang** atau **Lokasi**.

---

## 12. Katalog Ekspor & Daftar Field
Berikut adalah rincian kolom (field) yang muncul saat melakukan ekspor data ke Excel dari berbagai modul di NineSoft.

### **A. Ekspor Data Master**
| Jenis Ekspor | Daftar Kolom (Field) |
| :--- | :--- |
| **Data Barang & Jasa** | `KODE`, `NAMA`, `KEMASAN`, `RAKITAN`, `BARCODE`, `SATUAN`, `SATUAN2`, `SATUAN3`, `KONV2`, `KONV3`, `KELOMPOK`, `JENIS_BARANG`, `TIPE`, `MERK`, `SUPPLIER_KODE`, `KETERANGAN`, `AKTIF` |
| **Data Customer** | `KODE CUSTOMER`, `NAMA CUSTOMER`, `JANGKA WAKTU` (Termin), `BATAS PIUTANG` (Limit), `RAYON`, `AKTIF` |
| **Data Supplier** | `KODE SUPPLIER`, `NAMA SUPPLIER`, `JANGKA WAKTU`, `RAYON`, `AKTIF` |

### **B. Ekspor Daftar Transaksi**
| Jenis Ekspor | Daftar Kolom (Field) |
| :--- | :--- |
| **Daftar Penjualan** | `STATUS`, `TANGGAL`, `NOMOR` (Faktur), `NO REFERENSI`, `CUSTOMER`, `TOTAL`, `USER` |
| **Daftar Pembelian** | `STATUS`, `TANGGAL`, `NOMOR`, `NO REFERENSI`, `SUPPLIER`, `TOTAL`, `USER` |
| **Daftar Jurnal (GL)** | `TANGGAL`, `TRANSAKSI`, `NO DOKUMEN`, `ITEM`, `KETERANGAN`, `NO REFERENSI`, `ACCOUNT`, `DEBET`, `KREDIT` |

### **C. Ekspor Laporan Keuangan**
| Nama Laporan | Daftar Kolom (Field) |
| :--- | :--- |
| **Neraca Saldo** | `NO`, `KODE AKUN`, `NAMA ACCOUNT`, `SALDO AWAL`, `MUTASI DEBET`, `MUTASI KREDIT`, `SALDO AKHIR` |
| **Laba Rugi Standard** | `KODE AKUN`, `NAMA AKUN`, `NILAI` (Nominal per akun pendapatan/biaya) |
| **Neraca Standard** | `KODE AKUN`, `NAMA AKUN`, `NILAI` (Nominal per akun harta/hutang/modal) |

---

---

## 13. WhatsApp & Notifikasi (Info Whatsapp)
Sistem NineSoft terintegrasi dengan layanan pengiriman pesan instan untuk mempercepat komunikasi dengan mitra bisnis.

- **Log Pesan (Outbound Log)**: Mencatat seluruh riwayat pengiriman notifikasi/dokumen.
- **Field Atribut**:
    - `User Email`: Identitas pengirim (staf) dari dalam sistem.
    - `Phone`: Nomor WhatsApp tujuan (pelanggan/supplier).
    - `Message`: Isi pesan yang dikirimkan.
---

## 8. Fasilitas & Konfigurasi Sistem
Bagian ini menjelaskan bagaimana sistem NineSoft dikonfigurasi untuk menyesuaikan kebutuhan bisnis.

### **A. Persiapan Singkat (Profil Perusahaan)**
Digunakan untuk mengatur identitas usaha yang akan muncul pada laporan dan faktur.
- **Akses**: Fasilitas > Persiapan Singkat.
- **Field**: `Nama Perusahaan`, `Email`, `Alamat` (Baris 1 & 2), `NPWP`, `No Seri Faktur Pajak`, `No Pengukuhan PKP`, `Tgl Pengukuhan PKP`, `Logo`.

### **B. Setup Sistem (Konfigurasi Global)**
Mengatur perilaku sistem secara menyeluruh.
- **Akses**: Fasilitas > Setup.
- **Tab General**:
    - `Tanggal Mulai Program`: Batas awal sistem mulai mencatat transaksi.
    - `Format No Transaksi`: Pilihan format penomoran (Bulanan atau Tahunan).
    - `Jumlah Desimal`: Pengaturan presisi angka (0 s/d 4 desimal).
    - `Sistem Jual/Beli`: Pilihan alur (Gunakan Surat Jalan atau Langsung Faktur).
    - `Maksimum Cetak`: Limitasi pengulangan cetak dokumen.
- **Tab Transaksi**:
    - `Stok Boleh Minus`: Toggle krusial untuk mengizinkan input penjualan meski saldo sistem belum diupdate (cocok untuk ritel cepat).
    - `Matching PO/SO`: Validasi Qty Faktur terhadap Qty Pesanan untuk mencegah over-delivery.
    - `Limit Hutang Piutang`: Kontrol kredit proaktif dengan sistem blokir/peringatan.
    - `HPP Display`: Opsi untuk menyembunyikan atau menampilkan nilai modal di layar POS/Sales untuk privasi.
    - `Tampilkan Note For Today`: Pengaturan pengingat tugas harian.
- **Tab Web (Integration & Output)**:
    - `Mode Print`: Pilihan 'Dengan Preview' atau 'Direct Print'.
    - `Jenis Form`: Pilihan layout (Besar/Kecil/Thermal).
    - **Bluetooth Printer Support**: Dukungan khusus untuk pencetakan struk dari browser mobile/web.
    - `Sistem Pembelian`: Pilihan antara 'Tanpa Barang Datang' (Langsung Invoice) atau 'Dengan Barang Datang' (Prosedur PO-SJ-Inv).

### **C. Manajemen Pengguna (Security)**
Mengatur siapa saja yang bisa mengakses sistem dan sejauh mana hak aksesnya.
- **User Group**:
    - **Field**: Nama Group, Deskripsi.
    - **Otoritas**: Pengaturan hak akses granular (**VIEW, ADD, EDIT, DELETE**) untuk setiap modul di sistem.
- **Data User**:
    - **Field**: `Username`, `Email`, `Password`, `Full Name`, `Jabatan`, `Masa Aktif Akun`.
    - **Izin Khusus**: Checkbox `Admin` (Semua akses), `Excel` (Izin export data), dan `Portal`.

### **D. Pengaturan Menu & Dokumen**
- **Setting Menu**: Mengatur prefix nomor otomatis (misal: 'FJ' untuk Faktur Jual), izin `Edit Tanggal` transaksi, dan otomatisasi baris (`Auto Insert`).
- **Custom Cetakan**: Editor layout visual untuk kustomisasi dokumen perusahaan (Faktur, SJ, PO, dll).
    - **Mekanisme Editor**: Berbasis **Grid Designer** di mana setiap elemen dapat diatur posisi (*Layout*) dan isinya (*Content*).
    - **Placeholders**: Menggunakan label dinamis dalam kurung siku, contoh: `[NAME]`, `[ADDRESS1]`, `[DOCUMENT_NO]`, `[DOCUMENT_DATE]`.
- **Cetak Barcode**: Pengaturan label barcode barang untuk kebutuhan inventaris fisik.

### **E. Pemeliharaan & Audit (Maintenance)**
- **History Transaksi (Audit Log)**: Mencatat setiap aktivitas user di sistem. Data mencakup `Tanggal`, `Menu`, `Tipe Aksi` (ADD/EDIT/DELETE), `No Dokumen`, dan `Username` pelaku.
- **Rekalkulasi Sistem**: Fitur untuk memperbaiki integritas data jika terjadi ketidaksinkronan:
    - `Hitung Ulang Stok`: Sinkronisasi fisik vs kartu stok.
    - `Hitung Ulang HPP`: Menghitung kembali harga pokok penjualan berdasarkan histori transaksi.
    - `Hitung Saldo Accounting`: Sinkronisasi mutasi jurnal dengan saldo buku besar.
- **Database Tools**: Fasilitas untuk `Restore Database` dan `Database Patching` untuk pembaruan sistem.

---

## 9. Alur Proses Bisnis NineSoft (End-to-End)
Berikut adalah urutan standar penggunaan aplikasi NineSoft mulai dari persiapan data hingga penutupan buku bulanan.

### **Tahap 1: Persiapan & Input Data Master**
1.  **Setup Awal**: Mengatur profil perusahaan dan sistem (Fasilitas > Setup).
2.  **Chart of Accounts (COA)**: Menyusun daftar akun dan melakukan mapping akun otomatis (Setting COA).
3.  **Data Induk**: Menginput (atau impor via Excel) data Barang, Supplier, dan Customer.
4.  **Saldo Awal**: Menginput Saldo Awal Akun (Neraca) dan Saldo Awal Stok per gudang untuk memulai periode pertama.

### **Tahap 2: Siklus Operasional Harian**
1.  **Siklus Pembelian**:
    *   Buat **Order Pembelian (PO)** ke supplier.
    *   Input **Faktur Pembelian** (mengurangi hutang & menambah stok).
    *   Lakukan **Pelunasan Hutang** via Kas/Bank jika pembayaran sudah dilakukan.
2.  **Siklus Penjualan**:
    *   Terima pesanan via **Sales Order (SO)**.
    *   Kirim barang via **Surat Jalan (SJ)** (mengurangi stok fisik).
    *   Terbitkan **Faktur Penjualan** (mengakui piutang & pendapatan).
    *   Input **Pelunasan Piutang** saat uang diterima dari pelanggan.
3.  **Kas/Bank & Memorial**:
    *   Input biaya operasional via **Bukti Kas Keluar**.
    *   Input penyesuaian non-kas via **Transaksi Jurnal** (GL).

### **Tahap 3: Prosedur Akhir Bulan (Period-End)**
Sebelum melakukan tutup buku, user **WAJIB** menjalankan fitur rekalkulasi di menu **Fasilitas** untuk memastikan integritas data:
1.  **ReProses Stok**: Menghitung ulang saldo stok fisik di seluruh gudang.
2.  **ReProses HPP**: Menghitung kembali nilai Harga Pokok Penjualan berdasarkan metode valuation (FIFO/Average) agar laba kotor akurat.
3.  **ReProses Accounting**: Sinkronisasi seluruh transaksi ke dalam Buku Besar (GL).

### **Tahap 4: Penutupan Buku (Closing)**
1.  **Closing Periode**: Masuk ke menu `GL > Closing Periode Akuntansi`.
2.  **Eksekusi**: Pilih bulan dan tahun yang akan ditutup, lalu klik **Closing**.
3.  **Efek Closing**:
    *   Sistem akan mengunci transaksi pada bulan tersebut (tidak bisa edit/hapus).
    *   Saldo Laba/Rugi akan dipindahkan ke akun Laba Ditahan (Retained Earnings).
    *   Saldo akhir neraca bulan ini akan menjadi saldo awal bulan berikutnya.

---

## 10. Pola UI, Aksi, dan Fitur Ekspor
NineSoft memiliki pola antarmuka yang konsisten di seluruh modul untuk memudahkan navigasi dan pengolahan data.

### **A. Fitur pada Daftar (List View)**
Setiap tabel daftar (misal: Daftar Barang, Daftar Faktur) memiliki aksi standar:
- **Tombol AKSI (Kaca Pembesar)**: Membuka formulir detail untuk melakukan pengeditan atau sekadar melihat data.
- **Icon Plus Hijau (+)**: Fitur "Expander" untuk melihat detail baris tanpa pindah halaman (misal: melihat rincian stok per gudang atau rincian jurnal).
- **Import Excel**: Tombol di pojok kanan atas untuk unggah data massal (bulk upload).
- **Accordion History**: Menampilkan log perubahan data langsung di bawah daftar filter.

### **B. Fitur pada Formulir Transaksi (Input Form)**
Saat mengisi data atau transaksi, tersedia tombol-tombol aksi berikut:
- **Toolbar Utama**:
    - **Simpan & Undo**: Untuk manajemen status data.
    - **Hapus**: Untuk membatalkan/menghapus transaksi (tergantung hak akses).
    - **Cetak (Dropdown)**: Pilihan format cetak yang beragam (A4, Half Letter, Surat Jalan, dll).
    - **Excel**: Ekspor data transaksi yang sedang dibuka langsung ke file Excel.
    - **Info (Audit Log)**: Membuka jendela pop-up yang menunjukkan siapa pembuat data dan kapan terakhir kali diubah.
- **Aksi pada Tabel Detail (Grid)**:
    - **Browse Item**: Membuka katalog pencarian barang.
    - **Pilih Ref (SO/SJ/PO)**: Menarik data dari dokumen referensi untuk mempercepat input.
    - **Delete All**: Menghapus seluruh baris detail sekaligus.
- **Quick Add (+)**: Icon plus biru di samping dropdown (misal: Satuan, Kelompok) untuk menambah data master baru tanpa harus keluar dari formulir transaksi.

### **C. Fitur pada Laporan (Reporting)**
Setiap laporan memiliki opsi output yang fleksibel:
- **Preview**: Menampilkan laporan di layar browser sesuai filter yang dipilih.
- **Export Excel**: Mengunduh laporan dalam format `.xlsx` dengan tata letak yang rapi.
- **Cetak**: Mengirim laporan langsung ke mesin pencetak.
- **Kirim Email**: Fitur untuk mengirim laporan sebagai lampiran email langsung dari sistem.
- **Layout Control**: Opsi pemilihan tampilan *Portrait* atau *Landscape* sebelum laporan dibuat.

### **D. Fitur Impor Data Massal**
NineSoft menyediakan fitur impor untuk mempercepat pengisian data dalam jumlah besar. Fitur ini tidak terpusat di satu menu, melainkan tersebar di setiap daftar modul terkait:
- **Lokasi Tombol**: Berada di pojok kanan atas setiap List View (Daftar), biasanya berwarna biru/abu-abu dengan label **"Import"**.
- **Data yang Mendukung Impor**:
    - **Master Barang & Jasa**: Menggunakan format `.csv` (Template: `IMPORT_WEB_ITEM.csv`).
    - **Master Customer & Supplier**: Menggunakan format `.xlsx` (Template: `customer.xlsx`).
    - **Faktur Pembelian & Penjualan**: Mendukung impor transaksi langsung dari Excel (Template: `beli.xlsx` / `jual_faktur_sj.xlsx`).
    - **Transaksi Jurnal (GL)**: Mendukung impor jurnal umum via `.csv` (Template: `IMPORT_WEB_GL.csv`).
- **Mekanisme Templat**: Setiap jendela pop-up impor menyediakan tombol **"Download Template"** (biasanya berwarna kuning) untuk memastikan data yang diunggah sesuai dengan struktur kolom sistem.

---

## 11. Fitur Lanjutan & Batasan Sistem
Berdasarkan observasi mendalam, berikut adalah fitur akuntansi lanjutan serta beberapa batasan sistem yang perlu diperhatikan:

### **A. Pengelolaan Pajak (Taxes)**
- **Metode Perhitungan**: Mendukung sistem **PPN (Exclusive)** dan **INCL (Inclusive)** yang dapat dipilih per transaksi.
- **Multiple Rates**: User dapat mendefinisikan berbagai kode pajak (misal: PPN 11%, PPH 23) dengan akun GL yang berbeda.
- **Identitas Pajak**: Pengaturan NPWP, No Seri Faktur Pajak, dan Tanggal PKP tersedia secara global di Setup Perusahaan.
- **Ekspor PPN Masukan**: Fitur khusus dalam laporan pembelian untuk mengekspor data pajak masukan ke format yang kompatibel dengan aplikasi **e-Faktur DJP**, memangkas waktu pelaporan pajak bulanan.


### **B. Karakteristik Posting & Keamanan**
- **Real-time Posting**: Sistem NineSoft tidak memiliki tombol "Post". Setiap transaksi yang disimpan (Save) akan langsung memperbarui saldo Buku Besar (GL) dan laporan keuangan secara instan.
- **Audit Trail**: Melalui tombol **Info** di setiap formulir, sistem menyimpan jejak audit lengkap tentang siapa yang membuat dan kapan data terakhir diubah.
- **Kontrol Periode**: Keamanan data historis dijamin melalui fitur **Closing Periode** yang mengunci bulan-bulan sebelumnya dari perubahan.

### **C. Batasan Sistem (Varian Demo)**
- **Multi-Currency**: Sistem saat ini hanya mendukung satu mata uang induk (**IDR**). Tidak ditemukan fitur input mata uang asing, kurs harian, atau rekalkulasi selisih kurs.
- **Payroll & HR**: Tidak ditemukan modul untuk manajemen penggajian, absensi, atau database karyawan secara mendalam.
- **Project & Department**: Tidak tersedia fitur tracking biaya/pendapatan berdasarkan Proyek atau Departemen untuk konsolidasi laporan laba rugi internal.
- **Produksi Kompleks**: Sistem terbatas pada perakitan (*Assembly*) sederhana, tanpa dukungan untuk *Work Order* (WO), *Material Requirement Planning* (MRP), atau pelacakan biaya *overhead* produksi secara mendetail.
- **Rekonsiliasi Bank**: Tidak ditemukan fitur pencocokan otomatis antara catatan kas sistem dengan rekening koran (*Bank Statement*).

---

## 12. Katalog Ekspor & Daftar Field
Berikut adalah rincian kolom (field) yang muncul saat melakukan ekspor data ke Excel dari berbagai modul di NineSoft.

### **A. Ekspor Data Master**
| Jenis Ekspor | Daftar Kolom (Field) |
| :--- | :--- |
| **Data Barang & Jasa** | `KODE`, `NAMA`, `KEMASAN`, `RAKITAN`, `BARCODE`, `SATUAN`, `SATUAN2`, `SATUAN3`, `KONV2`, `KONV3`, `KELOMPOK`, `JENIS_BARANG`, `TIPE`, `MERK`, `SUPPLIER_KODE`, `KETERANGAN`, `AKTIF` |
| **Data Customer** | `KODE CUSTOMER`, `NAMA CUSTOMER`, `JANGKA WAKTU` (Termin), `BATAS PIUTANG` (Limit), `RAYON`, `AKTIF` |
| **Data Supplier** | `KODE SUPPLIER`, `NAMA SUPPLIER`, `JANGKA WAKTU`, `RAYON`, `AKTIF` |

### **B. Ekspor Daftar Transaksi**
| Jenis Ekspor | Daftar Kolom (Field) |
| :--- | :--- |
| **Daftar Penjualan** | `STATUS`, `TANGGAL`, `NOMOR` (Faktur), `NO REFERENSI`, `CUSTOMER`, `TOTAL`, `USER` |
| **Daftar Pembelian** | `STATUS`, `TANGGAL`, `NOMOR`, `NO REFERENSI`, `SUPPLIER`, `TOTAL`, `USER` |
| **Daftar Jurnal (GL)** | `TANGGAL`, `TRANSAKSI`, `NO DOKUMEN`, `ITEM`, `KETERANGAN`, `NO REFERENSI`, `ACCOUNT`, `DEBET`, `KREDIT` |

### **C. Ekspor Laporan Keuangan**
| Nama Laporan | Daftar Kolom (Field) |
| :--- | :--- |
| **Neraca Saldo** | `NO`, `KODE AKUN`, `NAMA ACCOUNT`, `SALDO AWAL`, `MUTASI DEBET`, `MUTASI KREDIT`, `SALDO AKHIR` |
| **Laba Rugi Standard** | `KODE AKUN`, `NAMA AKUN`, `NILAI` (Nominal per akun pendapatan/biaya) |
| **Neraca Standard** | `KODE AKUN`, `NAMA AKUN`, `NILAI` (Nominal per akun harta/hutang/modal) |

---

---

## 13. WhatsApp & Notifikasi (Info Whatsapp)
Sistem NineSoft terintegrasi dengan layanan pengiriman pesan instan untuk mempercepat komunikasi dengan mitra bisnis.

- **Log Pesan (Outbound Log)**: Mencatat seluruh riwayat pengiriman notifikasi/dokumen.
- **Field Atribut**:
    - `User Email`: Identitas pengirim (staf) dari dalam sistem.
    - `Phone`: Nomor WhatsApp tujuan (pelanggan/supplier).
    - `Message`: Isi pesan yang dikirimkan.
    - `Status`: Keterangan pengiriman (`Sent`/`Pending`).
    - `Time`: Waktu presisi pengiriman untuk pelacakan.
- **Jenis Notifikasi Otomatis**:
    *   **Transaksi**: Notifikasi PO/SO baru atau konfirmasi pengiriman barang (SJ).
    *   **Keuangan**: Pengingat saldo piutang (Billing Reminders) yang dipicu berdasarkan H-N atau H+N jatuh tempo.
    *   **CRM**: Pesan apresiasi otomatis setelah pelunasan piutang (Payment Received).

---

## 14. Analisis Lanjutan: Keamanan & Granularitas Akses
Sistem NineSoft menerapkan kontrol keamanan yang sangat spesifik untuk mencegah akses data yang tidak sah.

- **Granularitas Role-Based Access Control (RBAC)**:
    - Hak akses diatur **per menu** hingga level sub-menu terkecil.
    - Setiap menu memiliki 4 opsi tindakan: **VIEW** (Melihat), **ADD** (Menambah), **EDIT** (Mengubah), dan **DEL** (Menghapus).
    - Administrator dapat membatasi user kasir hanya bisa `ADD` POS tanpa bisa `VIEW` laporan keuangan atau `EDIT` transaksi yang sudah disimpan.
- **Isolasi Data (Multi-Branch Logic)**:
    - Meskipun database terpusat, pemisahan operasional dilakukan melalui entitas `Gudang` dan `Lokasi`.
    - User dapat dibatasi aksesnya hanya pada Gudang tertentu untuk menjaga privasi stok antar cabang.

---

## 15. Strategi Implementasi StoreSoft (Mapping Arsitektur)
Berdasarkan hasil analisa NineSoft, berikut adalah panduan pemetaan fitur ke dalam folder arsitektur `feature/accounting/` di StoreSoft:

### **A. Area `setup/` (Pondasi)**
- **Feature**: Chart of Accounts (COA), Mapping Akun Otomatis, Master Pajak, Saldo Awal Neraca.
- **NineSoft Match**: Menu Master > Account & Fasilitas > Persiapan Singkat.
- **Target**: Memastikan seluruh modul (Sales/Purchase) memiliki referensi akun yang valid sebelum transaksi dimulai.

### **B. Area `journal/` (Engine Transaksi)**
- **Feature**: Input Jurnal Manual, Approval Workflow, Audit Trail, Recurring Journal.
- **NineSoft Match**: Menu GL > Transaksi Jurnal & Fasilitas > History.
- **Target**: Menyediakan interface entry data yang *balanced* (Debit = Kredit) dengan validasi periode terkunci.

### **C. Area `ledger/` (Buku Pembantu)**
- **Feature**: General Ledger (Buku Besar), Sub-Ledger, Trial Balance (Neraca Saldo).
- **NineSoft Match**: Menu GL > Laporan Accounting.
- **Target**: Engine untuk menghitung *Running Balance* secara real-time dari seluruh mutasi jurnal.

### **D. Area `reports/` (Output Manajerial)**
- **Feature**: Neraca (Balance Sheet), Laba Rugi (P&L), Arus Kas (Cash Flow), Comparative & Ratio Analysis.
- **NineSoft Match**: Menu Analisa & GL > Laporan Keuangan.
- **Target**: Transformasi data ledger menjadi format laporan formal yang siap cetak (PDF) atau ekspor (Excel).

### **E. Area `closing/` (Finalitas)**
- **Feature**: Rekonsiliasi Bank, Jurnal Penyesuaian, ReProses Stok/HPP, Closing Bulanan/Tahunan.
- **NineSoft Match**: Menu GL > Closing & Fasilitas > Reproses.
- **Target**: Prosedur validasi akhir untuk mengunci data periode berjalan dan memindahkan saldo laba ke periode berikutnya.

---

---

## 16. Analisis Teknis: Endpoint & Payload Mapping
Bagian ini ditujukan bagi pengembang (Developer) untuk memahami struktur pertukaran data antara *frontend* dan *backend* NineSoft sebagai acuan desain API StoreSoft.

### **A. Master Data (Chart of Accounts)**
- **Listing Akun (DataTable)**:
    - **Endpoint**: `https://web.ninesoft.id/site/master/account/ajax_list/1`
    - **Method**: `POST` dengan parameter pencarian.
    - **Format Response**: JSON DataTable.
- **Hierarki Akun (Tree View)**:
    - **Endpoint**: `https://web.ninesoft.id/site/master/account/ajax_tree`
    - **Format Response**: HTML (`<ul><li>`) untuk kemudahan rendering server-side.
- **Autocomplete Pencarian**:
    - **Endpoint**: `/site/auto/search/coa_code`
    - **Guna**: Pengambilan data akun secara dinamis saat mengetik di form transaksi.

### **B. Transaksi Jurnal (General Ledger)**
- **Penyimpanan Transaksi**:
    - **Endpoint**: `https://web.ninesoft.id/site/gl/jurnal/save`
    - **Method**: `POST` (*form-urlencoded*).
    - **Payload Utama**:
        - `jurnal_id`: ID transaksi (kosong untuk data baru).
        - `document_no`: Nomor dokumen otomatis atau manual.
        - `document_date`: Tanggal transaksi (`DD-MM-YYYY HH:mm`).
        - `dataDetail`: **String JSON** yang berisi array dari detail (Double entry).
        - **Format Detail**: `["id_detail", "id_akun", "nama_akun", "kode_akun", "uraian", "nominal_debet", "nominal_kredit", null, null]`
        - *Contoh Payload dataDetail*: `[["", "2", "Kas Kecil", "101.01.001", "Setoran Modal", "1.000.000", "", null, null], [...]]`

### **C. Pelaporan Keuangan (Reports)**
- **Struktur Neraca/Laba Rugi**:
    - **Endpoint**: `https://web.ninesoft.id/site/report/analysis/LprnNrcStndrd/`
    - **Metode**: Server-Side Rendering (SSR).
    - **Parameter**: `period` (YYYYMM), `show_zero` (bool).
    - **Output**: Tabel HTML murni.
    - **Rekomendasi StoreSoft**: Menggunakan API berbasis JSON agar data lebih fleksibel diolah di *frontend* (misal: grafik analisis atau pencarian dinamis).

---


### **D. Modul Penjualan (Sales Invoice)**
- **Endpoint**: `https://web.ninesoft.id/site/sales/faktur/save`
- **Method**: `POST` (*form-urlencoded*)
- **Payload Utama**:
    - `person_id`: ID internal customer (contoh: `1` untuk CASH).
    - `document_date`: Tanggal transaksi (`DD-MM-YYYY HH:mm`).
    - `dataDetail`: **String JSON** (Array of Arrays).
    - **Format Detail**: `[ [null, "ITEM_ID", null, "NAMA_ITEM", "KODE_ITEM", "QTY", "SATUAN", "HARGA", "DISC_PERCENT", "SUBTOTAL", null, null] ]`

### **E. Modul Pembelian (Purchase Invoice)**
- **Endpoint**: `https://web.ninesoft.id/site/purchasing/faktur/save`
- **Method**: `POST` (*form-urlencoded*)
- **Payload Utama**:
    - `person_id`: ID internal supplier.
    - `invoice_id`: ID transaksi (kosong untuk baru).
    - `dataDetail`: **String JSON** (Array of Arrays).
    - **Format Detail**: Serupa dengan Sales Invoice, namun untuk sisi perolehan stok.

### **F. Modul Kas & Bank (BKM/BKK)**
- **Endpoint BKM**: `https://web.ninesoft.id/site/finance/Bkm/save`
- **Endpoint BKK**: `https://web.ninesoft.id/site/finance/Bkk/save`
- **Method**: `POST` (*form-urlencoded*)
- **Payload Utama**:
    - `bank_id`: ID akun Kas/Bank (sumber/tujuan dana).
    - `dataDetail2`: **String JSON** (Array of Arrays) untuk entri manual/Lain-lain.
    - **Format Detail2**: `[ [null, "COA_ID", "NAMA_ACC", "KODE_ACC", "MEMO", "AMOUNT", null] ]`
    - `dataDetail1/3`: Untuk alokasi pelunasan nota (Hutang/Piutang).
- **Endpoint Dashboard**: `https://web.ninesoft.id/site/home/dashboard` (JSON format untuk data widget).
- **Endpoint Analisa Laba**: `https://web.ninesoft.id/site/report/analysis/LbRgNt` (SSR HTML).

### **G. Infrastruktur & Keamanan (API Protocol)**
- **Header WAJIB**:
    - `X-Requested-With: XMLHttpRequest` (Wajib untuk request via AJAX).
    - `Content-Type: application/x-www-form-urlencoded; charset=UTF-8`.
- **Autentikasi**:
    - Menggunakan Cookie **`ci_session_7`** sebagai token sesi.
- **Pola Sinkronisasi**:
    - Semua row detail dikemas dalam satu field stringified JSON (`dataDetail`). Hal ini meminimalkan jumlah field dalam POST request namun memerlukan parsing manual di sisi backend.

### **H. Master Formula (BOM)**
- **Endpoint**: `https://web.ninesoft.id/site/master/formula/save`
- **Method**: `POST` (*form-urlencoded*)
- **Payload Utama**:
    - `item_id_head`: ID internal barang hasil (Parent).
    - `entered_qty_head`: Jumlah output (default 1).
    - `dataDetail`: **String JSON** (Array of Arrays) untuk bahan baku.
    - **Format Detail**: `[ [null, "ITEM_ID", "NAMA", "KODE", "QTY", "STOK", "SATUAN", "MEMO"] ]`

### **I. Master Fixed Assets**
- **Endpoint**: `https://web.ninesoft.id/site/master/asset/ajax_add/`
- **Method**: `POST` (*form-urlencoded*)
- **Payload Utama**:
    - `nilai_asset`: Harga perolehan aset.
    - `umur_asset`: Masa manfaat dalam tahun.
    - `coa_asset_id`: Akun aset (Neraca).
    - `coa_debet_id`: Akun biaya penyusutan (Laba/Rugi).
    - `coa_kredit_id`: Akun akumulasi penyusutan (Neraca - Kontra Aktiva).

---

## 17. Mapping Laporan Keuangan Digital
Bagian ini mendokumentasikan bagaimana sistem menyajikan data laporan untuk konsumsi pengguna.

### **A. Mekanisme Data Fetching**
- **Metode**: Server-Side Rendering (SSR) via AJAX.
- **Data format**: Server mengembalikan **Raw HTML Table**, bukan JSON mentah. Browser kemudian merender tabel tersebut ke dalam kontainer yang disediakan.
- **Endpoint Laporan**:
    - **Neraca Standard**: `/site/report/analysis/LprnNrcStndrd/`
    - **Laba Rugi Standard**: `/site/report/analysis/LbRgGl/`

### **B. Struktur Parameter (Filter)**
Parameter filter dikirim dalam format array indeks:
- `p[12][1]`: Periode laporan (Format: `YYYYMM`, contoh: `202603`).
- `p[13][1]`: Lokasi/Gudang (ID internal).
- `submitForm`: Set ke `preview` untuk mendapatkan tampilan HTML.
- `printer`: Set ke `portrait` atau `landscape`.

### **C. Tantangan Integrasi StoreSoft**
Karena data yang dikembalikan berupa HTML, StoreSoft disarankan untuk **tidak melakukan parsing HTML** dari NineSoft, melainkan mendesain API laporan internal yang langsung mengembalikan objek JSON terstruktur (Tree Structure) untuk memudahkan rendering di Vite/Vue.

---

## 18. Manajemen Pajak (Tax Management)
NineSoft mengintegrasikan kepatuhan pajak langsung ke dalam alur transaksi.

### **A. Konfigurasi Master Pajak**
- **Akses**: `Master` > `Customer & Supplier` > `Pajak`.
- **Tipe Pajak**: Mendukung PPN (11%/12%), PPN INCL (Termasuk Pajak), dan Non-Pajak.
- **Mapping Akun**: Setiap kode pajak dipetakan ke **Akun PPN Masukan** (untuk Pembelian) dan **Akun PPN Keluaran** (untuk Penjualan).

### **B. Implementasi Transaksi**
- **Payload API**: Pada saat simpan faktur, dikirimkan field:
    - `ppn_code`: Kode identifier pajak (misal: `PPN`).
    - `ppn_percen`: Nilai persentase (11.00 atau 12.00).
    - `ppn_amount`: Nominal pajak yang dihitung otomatis oleh sistem.
    - `no_pajak`: Field untuk menginput Nomor Seri Faktur Pajak (NSFP) dari DJP.

## 19. Audit Trail & Integritas Data
Fitur keamanan untuk melacak aktivitas pengguna dan perubahan data keuangan.

### **A. Log Transaksi (History)**
- **Akses**: `Fasilitas` > `History`.
- **Metadata**: Mencatat `UserID`, `Timestamp`, `Aksi` (Tambah/Edit/Hapus), dan `Modul`.
- **Info Detail**: Setiap formulir transaksi memiliki tombol **Info** yang menampilkan:
    - **Create By / Date**: Pembuat data awal.
    - **Last Update By / Date**: Pengubah data terakhir.

### **B. Keamanan Penghapusan**
- Sistem mencatat log penghapusan permanent, namun menyarankan pencatatan bukti koreksi (Jurnal Balik) untuk integritas audit yang lebih baik.

## 20. Prosedur Akhir Periode (Closing & Reproses)
Menjamin validitas saldo stok dan laporan keuangan antar periode.

### **A. Closing Periode Akuntansi**
- **Fungsi**: Mengunci (Lock) seluruh transaksi pada periode tersebut agar tidak bisa diubah (mencegah manipulasi data lampau).
- **Endpoint**: `POST /site/gl/closing/ajax_list`.
- **Status Bulanan**: Ditandai dengan `OPEN` (Bisa input) atau `CLOSED` (Terkunci).

### **B. Reproses HPP (Inventory Valuation)**
- **Kebutuhan**: Jika ada input transaksi mundur (backdate), nilai HPP rata-rata/FIFO mungkin tidak akurat.
- **Mekanisme**: Sistem menghitung ulang seluruh saldo stok secara kronologis dari awal periode hingga akhir.
- **Endpoint**: `/site/facilities/hpp/reproses_hpp_queue`.

## 21. Manajemen Piutang & AR Aging (Analisis Umur Piutang)
Membantu pemantauan arus kas dari sisi pelunasan pelanggan.

### **A. Laporan Umur Piutang (AR Aging)**
- **Akses**: `Penjualan` > `Laporan Penjualan` > `Umur Piutang`.
- **Pembagian Periode (Buckets)**:
    1. **Belum Jatuh Tempo**: Invoice yang belum melewati tanggal jatuh tempo.
    2. **1 - 30 Hari**: Terlambat hingga 1 bulan.
    3. **31 - 60 Hari**: Terlambat 2 bulan.
    4. **61 - 90 Hari**: Terlambat 3 bulan.
    5. **> 90 Hari**: Piutang macet/peringatan keras.

### **B. Dasar Perhitungan**
- **Logika**: Selisih antara `Document Date` (atau Due Date) dengan `Cut-off Date` laporan.

## 22. Multi-Cabang & Branch Isolation
Strategi NineSoft untuk pengelolaan unit bisnis yang tersebar.

### **A. Konsep Lokasi (Site)**
- **Identitas**: Cabang didefinisikan sebagai entitas "Alamat" atau "Cabang" di Master.
- **Parameter Teknis**: Setiap transaksi dikirim dengan parameter `person_site_id` atau `site_name` untuk menentukan lokasi asalnya.

### **B. Isolasi Stok**
- Saldo stok dipisahkan secara hierarkis: **Toko** > **Gudang**. Pengguna dapat melihat stok per gudang atau stok gabungan per cabang pada laporan Info Barang.

---

## 23. Logika & Validasi Transaksi Jurnal (GL)
Bagian ini merinci perilaku sistem saat menangani entri buku besar manual.

### **A. Validasi Keseimbangan (Balancing Check)**
- **Mekanisme**: Client-side validation menggunakan SweetAlert2.
- **Pesan Error**: "Perhatian: Jumlah Debet dan Kredit Harus Sama!".
- **Perilaku**: Tombol **Simpan** akan memicu pengecekan total Debet vs Kredit dari array `dataDetail`. Jika selisih ≠ 0, request POST tidak akan dikirim ke server.

### **B. Dimensi Transaksi (Flat Accounting)**
- **Analisis**: NineSoft menggunakan struktur akuntansi *flat*. Tidak ditemukan field untuk **Project**, **Department**, atau **Cost Center** pada baris jurnal.
- **Struktur Baris**: Fokus murni pada `ID Akun`, `Nama Akun`, `Uraian`, `Debet`, dan `Kredit`.

### **C. Penomoran Otomatis (Prefixing)**
- **Prefix Manual**: Seluruh jurnal yang diinput melalui menu GL akan memiliki prefix `GL-` (contoh: `GL-26-03-00001`).

## 24. Mekanisme Posting & Sinkronisasi Modul
Bagian ini menjelaskan bagaimana NineSoft menjaga konsistensi antara buku besar dan modul operasional.

### **A. Isolasi Jurnal Otomatis (Read-Only Logic)**
- **Sumber Data**: Jurnal yang dihasilkan otomatis oleh modul **Penjualan (FJ)**, **Pembelian (FB)**, atau **Kas (BKM/BKK)**.
- **Pembatasan**: Jurnal-jurnal tersebut **TIDAK muncul** di dalam list edit `GL > Transaksi Jurnal`.
- **Integritas**: Perubahan nilai pada jurnal otomatis hanya bisa dilakukan dengan merevisi dokumen sumbernya (misal: mengedit Faktur Penjualan). Hal ini mencegah diskoneksi antara nilai invoice dan nilai jurnal di buku besar.

### **B. Pemetaan Akun Otomatis (Setting COA)**
Sistem menggunakan tabel pemetaan (`Master > Setting COA`) untuk menentukan akun tujuan saat transaksi operasional disimpan:
- **Penjualan**: `Piutang Dagang (D)` vs `Penjualan (K)`.
- **Persediaan**: `HPP (D)` vs `Persediaan (K)`.
- **Kas/Bank**: Bergantung pada akun kas yang dipilih pada voucher.

**Kesimpulan Pengembang:**
NineSoft menggunakan kombinasi teknologi tradisional (DataTable & SSR HTML) dengan pengiriman detil transaksi melalui *stringified JSON* di dalam formulir POST. Untuk **StoreSoft**, disarankan penggunaan **REST API murni (JSON)** untuk seluruh modul guna mendukung arsitektur *Decoupled* (Vite + Go Fiber) yang lebih modern.

---

## 25. Observasi UI & UX (User Experience Patterns)
Berdasarkan eksplorasi langsung pada demo sistem, ditemukan beberapa pola interaksi kunci yang sangat membantu operasional:

### **A. Browse Popup (Pencarian & Pemilihan)**
- **Multi-Criteria Search**: Setiap jendela popup (Barang, Customer, Invoice) memiliki kolom filter di bagian atas setiap header tabel. User dapat memfilter data berdasarkan berbagai kolom secara bersamaan (misal: Kode + Kelompok).
- **Batch Selection**: Pada popup 'Browse Item', tersedia checkbox di setiap baris dan fitur 'Check All'. Hal ini memungkinkan user memilih banyak barang sekaligus untuk dimasukkan ke dalam satu transaksi.
- **In-Form Creation**: Hampir seluruh popup memiliki tombol '+' (Tambah Baru). Contoh: Saat membuat faktur, jika customer belum ada, user bisa klik '+' di popup browse customer untuk menambah data master tanpa harus menutup form faktur.

### **B. Dashboard Interaktif (Real-time Analytics)**
- **Modern Indicator Cards**: Dashboard utama menggunakan kartu metrik berwarna (Biru untuk Omzet, Merah untuk Hutang, Hijau untuk Piutang) yang sangat memudahkan pemantauan sekilas.
- **Deep Hover Details**: Beberapa grafik mendukung hover untuk melihat angka detail pada poin data tertentu.

### **C. Transaksi Cepat (POS Interface)**
- **Fokus Input**: Interface POS (Point of Sale) didesain agar kursor selalu kembali ke field 'Barcode' setelah setiap transaksi, meminimalisir penggunaan mouse bagi kasir.

## 26. Visualisasi & Analitik (Dashboard Detail)
Modul 'Analisa' dan halaman 'Info' di setiap modul menyediakan visualisasi berikut:

- **Sales Performance**: Grafik batang yang membandingkan 'Bulan Ini vs Bulan Lalu' secara harian.
- **Fast Moving Items**: Grafik Donut/Pie yang menunjukkan kontribusi kelompok barang (Item Group) terhadap total kuantitas penjualan.
- **Debt & AR Ageing**: Visualisasi pie chart untuk komposisi umur piutang (Belum Jatuh Tempo, < 30 Hari, s/d > 90 Hari).

## 27. Fitur Fasilitas Tambahan
- **List User Online**: Fitur administrasi untuk memantau siapa saja yang sedang login ke sistem.
- **Web-based Print Designer**: Desain template cetakan disimpan dalam format teks yang bisa diedit langsung di browser, mendukung kustomisasi logo dan variabel dinamis.
- **Bluetooth Printing**: Konfigurasi khusus di menu Setup untuk mendukung printer mobile (thermal), memudahkan operasional lapangan atau toko kecil.

---

## 28. Kesimpulan Analisa Bisnis (Market Target)
Berdasarkan kelengkapan fitur dan keterbatasan yang ditemukan, sistem NineSoft Accounting Online menunjukkan segmentasi pasar yang sangat spesifik:

- **Target Utama**: Perusahaan **Trading & Distribusi** (Grosir/Eceran) yang membutuhkan kecepatan input (POS), kontrol stok multi-gudang, dan integrasi pajak yang simpel.
- **Kekuatan**: Kemudahan penggunaan, pelaporan manajerial yang berorientasi profit (Laba per Item/Sales), dan ekspor data e-Faktur.
- **Kelemahan**: Kurang cocok untuk perusahaan **Jasa Service** yang mengandalkan manajemen proyek, perusahaan **Manufaktur** berskala besar, atau korporasi yang membutuhkan konsolidasi **Multi-Currency** dan manajemen **HR/Payroll** terintegrasi.

---

## 29. Ringkasan Observasi Tambahan (Developer Notes)

Berikut adalah catatan teknis tambahan untuk mempermudah migrasi ke arsitektur StoreSoft:

1.  **Autocomplete Engine**: Sistem NineSoft sangat bergantung pada pencarian asinkron (`/site/auto/search/...`) untuk akun, customer, dan barang. StoreSoft disarankan menggunakan API serupa dengan indexing yang kuat di sisi Go Fiber.
2.  **Automated Notifications (WhatsApp)**: Notifikasi bukan sekadar info pengiriman, tapi mencakup:
    *   Pengingat Tagihan (N hari sebelum/sesudah jatuh tempo).
    *   Ucapan Terima Kasih (setelah pelunasan).
    *   Status Pesanan (saat SO disetujui).
3.  **Decimal Precision**: Sistem mendukung kustomisasi desimal (0-4 digit) di level setup global. Penting untuk memastikan tipe data di database StoreSoft (numeric/decimal) mendukung fleksibilitas ini.
4.  **Operational Alerts**: Dashboard memiliki widget "Pending" (PO Outstanding, SO Outstanding) yang berfungsi sebagai To-Do List bagi admin gudang dan purchasing.

Dokumen ini diharapkan menjadi acuan bagi tim pengembang **StoreSoft** untuk memutuskan apakah akan meniru (replikasi) kesederhanaan NineSoft atau menambahkan fitur-fitur yang menjadi kelemahannya sebagai nilai jual kompetitif.

---

## 14. Bukti Scraping Otomatis Ninesoft - Khusus Modul Master

Bagian ini dihasilkan secara mandiri oleh skrip `catatan_ninesoft_scraper.py` (Bot AntiGravity) menginkorporasikan ekstraksi relasi Tabel, Fields Panel, dan Pola Validasi di area **Master Data Ninesoft**.

#### 🗂️ Menu: **Master**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/info`
- **Konstruksi Tabel Master**: `KODE ITEM`, `NAMA ITEM`, `SATUAN`, `HARGA BELI TERAKHIR`, `HARGA JUAL TERAKHIR`, `HARGA BELI`, `HARGA JUAL`, `AKTIF`, `AKSI`
- **Aksi / Fitur Operasional**: `New`, `Filter`, `Reset`, `Import`, `Filter Data`
- **Struktur Tab Halaman Add/Detail**:
  - **Tab Detail** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table1_length`
  - **Tab Account** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `acc_jual`, `acc_jual_code`, `acc_inventory`, `acc_inventory_code`, `acc_disc`, `acc_disc_code`, `acc_hpp`, `acc_hpp_code`, `acc_retur_jual`, `acc_retur_jual_code`, `acc_retur_beli`, `acc_retur_beli_code`, `acc_hutang_suspend`, `acc_hutang_suspend_code`
  - **Tab Saldo Awal Program** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table3_length`
  - **Tab Harga** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table4_length`
  - **Tab Diskon** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table5_length`

#### 🗂️ Menu: **Daftar Nilai**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/nilai`
- **Konstruksi Tabel Master**: `STATUS`, `NAMA MASTER`, `KETERANGAN`, `AKSI`
- **Aksi / Fitur Operasional**: `Filter`, `Reset`, `Filter Data`
- **Kamus Field Form (Add Data)**: `dtNamaMaster`, `dtNote`, `table_length`

#### 🗂️ Menu: **Barang dan Jasa**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/item`
- **Konstruksi Tabel Master**: `KODE ITEM`, `NAMA ITEM`, `SATUAN`, `HARGA BELI TERAKHIR`, `HARGA JUAL TERAKHIR`, `HARGA BELI`, `HARGA JUAL`, `AKTIF`, `AKSI`
- **Aksi / Fitur Operasional**: `New`, `Filter`, `Reset`, `Import`, `Filter Data`
- **Struktur Tab Halaman Add/Detail**:
  - **Tab Detail** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table1_length`
  - **Tab Account** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `acc_jual`, `acc_jual_code`, `acc_inventory`, `acc_inventory_code`, `acc_disc`, `acc_disc_code`, `acc_hpp`, `acc_hpp_code`, `acc_retur_jual`, `acc_retur_jual_code`, `acc_retur_beli`, `acc_retur_beli_code`, `acc_hutang_suspend`, `acc_hutang_suspend_code`
  - **Tab Saldo Awal Program** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table3_length`
  - **Tab Harga** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table4_length`
  - **Tab Diskon** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table5_length`

#### 🗂️ Menu: **Formula (BOM)**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/formula`
- **Konstruksi Tabel Master**: `NO FORMULA`, `NAMA ITEM`, `KODE ITEM`, `SATUAN`, `BERLAKU MULAI`, `BERLAKU SAMPAI`, `AKTIF`, `AKSI`
- **Aksi / Fitur Operasional**: `New`, `Filter`, `Reset`, `Import`, `Download Template`, `Save`, `Cancel`, `Filter Data`
- **Kamus Field Form (Add Data)**: `document_no`, `item_description_head`, `item_code_head`, `uom_code_head`, `start_date`, `end_date`, `document_reff_no`, `active_flag`, `keterangan`, `table_length`

#### 🗂️ Menu: **Satuan**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/satuan`
- **Konstruksi Tabel Master**: `SATUAN`, `KETERANGAN`, `AKTIF`, `DEFAULT`, `AKSI`
- **Aksi / Fitur Operasional**: `Add Record`, `Cancel`
- **Kamus Field Form (Add Data)**: `table_length`, `uom_code`, `description`, `active_flag`, `primary_flag`

#### 🗂️ Menu: **Saldo Awal**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/saldo`
- **Konstruksi Tabel Master**: `NAMA ITEM`, `KODE ITEM`, `STOK AWAL`, `SATUAN`, `HPP AWAL`, `SUBTOTAL`, `AKSI`, `Total(All Gudang):`, `22,389,261,116.53`, `Total(Per Gudang):`, `18,192,148,808.25`
- **Aksi / Fitur Operasional**: `Save`, `Cancel`

#### 🗂️ Menu: **Konversi Item**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/konversi`
- **Konstruksi Tabel Master**: `SATUAN`, `NILAI KONVERSI`, `KETERANGAN`, `AKTIF`, `AKSI`, `LAIN-lAIN`, `UTAMA`
- **Aksi / Fitur Operasional**: `Add Record`, `Cancel`
- **Kamus Field Form (Add Data)**: `table_length`, `from_uom`, `to_uom`, `to_qty`, `active_flag`

#### 🗂️ Menu: **Gudang**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/gudang`
- **Konstruksi Tabel Master**: `GUDANG`, `KETERANGAN`, `DEFAULT`, `PENJUALAN`, `AKTIF`, `ALAMAT`, `AKSI`, `KODE LOKASI`, `DETAIL`
- **Aksi / Fitur Operasional**: `Add Record`, `Cancel`
- **Kamus Field Form (Add Data)**: `table_length`, `warehouse_name`, `description`, `primary_flag`, `sales_flag`, `active_flag`, `address_code`, `address1`

#### 🗂️ Menu: **Jangka Waktu Kredit**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/termin`
- **Konstruksi Tabel Master**: `NAMA/KODE`, `KETERANGAN`, `JANGKA WAKTU(HARI)`, `DEFAULT`, `AKTIF`, `AKSI`
- **Aksi / Fitur Operasional**: `Add Record`, `Cancel`
- **Kamus Field Form (Add Data)**: `table_length`, `payment_term_name`, `description`, `number_days`, `primary_flag`, `active_flag`

#### 🗂️ Menu: **Pajak**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/pajak`
- **Konstruksi Tabel Master**: `KODE PAJAK`, `PERSEN`, `DEFAULT`, `AKSI`
- **Aksi / Fitur Operasional**: `Add Record`, `Cancel`
- **Kamus Field Form (Add Data)**: `table_length`, `ppn_code`, `percentage`, `primary_flag`

#### 🗂️ Menu: **Customer**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/customer`
- **Konstruksi Tabel Master**: `KODE CUSTOMER`, `NAMA CUSTOMER`, `JANGKA WAKTU`, `BATAS PIUTANG`, `RAYON`, `AKSI`
- **Aksi / Fitur Operasional**: `New`, `Filter`, `Reset`, `Import`, `Download Template`, `Save`, `Cancel`, `Filter Data`
- **Struktur Tab Halaman Add/Detail**:
  - **Tab Detail** ➔ Fields: `person_code`, `person_name`, `termin`, `term_number`, `jtempo`, `limit_piutang`, `rayon`, `no_npwp`, `discount_percen_head`, `discount_price_head`, `email`, `note`, `karyawan_id`, `coa_piut_code`, `coa_piut_name`, `tipe_harga_jual`, `check_supp`, `active_flag`, `username`, `password`, `table_length`
  - **Tab Saldo Awal Program** ➔ Fields: `person_code`, `person_name`, `termin`, `term_number`, `jtempo`, `limit_piutang`, `rayon`, `no_npwp`, `discount_percen_head`, `discount_price_head`, `email`, `note`, `karyawan_id`, `coa_piut_code`, `coa_piut_name`, `tipe_harga_jual`, `check_supp`, `active_flag`, `username`, `password`, `table2_length`
  - **Tab Address** ➔ Fields: `person_code`, `person_name`, `termin`, `term_number`, `jtempo`, `limit_piutang`, `rayon`, `no_npwp`, `discount_percen_head`, `discount_price_head`, `email`, `note`, `karyawan_id`, `coa_piut_code`, `coa_piut_name`, `tipe_harga_jual`, `check_supp`, `active_flag`, `username`, `password`, `table2_length`
  - **Tab Contact** ➔ Fields: `person_code`, `person_name`, `termin`, `term_number`, `jtempo`, `limit_piutang`, `rayon`, `no_npwp`, `discount_percen_head`, `discount_price_head`, `email`, `note`, `karyawan_id`, `coa_piut_code`, `coa_piut_name`, `tipe_harga_jual`, `check_supp`, `active_flag`, `username`, `password`, `table2_length`

#### 🗂️ Menu: **Supplier**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/supplier`
- **Konstruksi Tabel Master**: `KODE SUPPLIER`, `NAMA SUPPLIER`, `JANGKA WAKTU`, `BATAS HUTANG`, `AKSI`
- **Aksi / Fitur Operasional**: `New`, `Filter`, `Reset`, `Import`, `Download Template`, `Save`, `Cancel`, `Filter Data`
- **Struktur Tab Halaman Add/Detail**:
  - **Tab Detail** ➔ Fields: `person_code`, `person_name`, `termin`, `term_number`, `limit_hutang`, `no_npwp`, `discount_percen_head`, `discount_price_head`, `email`, `note`, `coa_hut_code`, `coa_hut_name`, `check_cus`, `active_flag`, `table_length`
  - **Tab Saldo Awal Program** ➔ Fields: `person_code`, `person_name`, `termin`, `term_number`, `limit_hutang`, `no_npwp`, `discount_percen_head`, `discount_price_head`, `email`, `note`, `coa_hut_code`, `coa_hut_name`, `check_cus`, `active_flag`, `table2_length`
  - **Tab Address** ➔ Fields: `person_code`, `person_name`, `termin`, `term_number`, `limit_hutang`, `no_npwp`, `discount_percen_head`, `discount_price_head`, `email`, `note`, `coa_hut_code`, `coa_hut_name`, `check_cus`, `active_flag`, `table2_length`
  - **Tab Contact** ➔ Fields: `person_code`, `person_name`, `termin`, `term_number`, `limit_hutang`, `no_npwp`, `discount_percen_head`, `discount_price_head`, `email`, `note`, `coa_hut_code`, `coa_hut_name`, `check_cus`, `active_flag`, `table2_length`

#### 🗂️ Menu: **Alamat**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/alamat`
- **Konstruksi Tabel Master**: `KODE`, `ALAMAT`, `TELP`, `FAX`, `BISA KIRIM`, `AKTIF`, `AKSI`
- **Aksi / Fitur Operasional**: `Add Record`, `Cancel`
- **Kamus Field Form (Add Data)**: `table_length`, `address_code`, `address1`, `address2`, `city`, `province`, `country`, `phone`, `fax`, `ship_flag`, `active_flag`

#### 🗂️ Menu: **Sales**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/karyawan`
- **Konstruksi Tabel Master**: `NAMA`, `NAMA LENGKAP`, `KETERANGAN`, `MASA KERJA`, `MULAI`, `AKHIR`
- **Aksi / Fitur Operasional**: `Cancel`, `Add Record`
- **Kamus Field Form (Add Data)**: `first_name`, `last_name`, `start_date`, `end_date`, `description`, `table_length`

#### 🗂️ Menu: **Daftar Harga**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/harga`
- **Konstruksi Tabel Master**: `KODE ITEM`, `NAMA ITEM`, `SATUAN`, `BELI`, `HARGA 1`, `DISKON`, `KELOMPOK`, `AKSI`, `Rp`, `%`
- **Aksi / Fitur Operasional**: `Import`, `Download Template`, `Save`, `Cancel`, `Add Record`
- **Struktur Tab Halaman Add/Detail**:
  - **Tab Item** ➔ Fields: `group`, `table_length`, `item_code`, `display_name`, `uom`
  - **Tab Harga** ➔ Fields: `group`, `table_length`, `beli`, `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `9`, `10`
  - **Tab Diskon** ➔ Fields: `group`, `table_length`, `discount_price`, `discount_percen`

#### 🗂️ Menu: **Asset**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/asset`
- **Konstruksi Tabel Master**: `NAMA ASSET`, `KODE ASSET`, `JML`, `NILAI`, `UMUR`, `METODE DEPRESIASI`, `AKTIF`, `AKSI`
- **Aksi / Fitur Operasional**: `Add Record`, `Save`
- **Struktur Tab Halaman Add/Detail**:
  - **Tab Asset** ➔ Fields: `table_length`, `asset_code`, `asset_name`, `entered_qty`, `nilai_asset`, `susut_year`, `umur_asset`, `rate`, `dd-mm-yyyy`, `note`, `active_flag`
  - **Tab Account** ➔ Fields: `table_length`, `coa_asset`, `coa_debet`, `coa_kredit`, `asset_type`, `metode_depresiasi`, `intantangible_asset`

#### 🗂️ Menu: **Account**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/account`
- **Konstruksi Tabel Master**: `KODE ACC`, `NAMA ACCOUNT`, `TIPE ACCOUNT`, `AKTIF`, `AKSI`
- **Aksi / Fitur Operasional**: `Add Record`, `Create Coa`

#### 🗂️ Menu: **Saldo Awal COA**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/saldocoa`
- **Konstruksi Tabel Master**: `ACCOUNT`, `SALDO AWAL`, `PERIODE`, `AKSI`, `KODE`, `NAMA`
- **Aksi / Fitur Operasional**: `Cancel`

#### 🗂️ Menu: **Setting COA**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/coa`
- **Konstruksi Tabel Master**: `KETERANGAN`, `ACCOUNT`, `AKSI`
- **Aksi / Fitur Operasional**: `Cancel`

#### 🗂️ Menu: **User Group**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/user`
- **Konstruksi Tabel Master**: `NAMA USER`, `EMAIL`, `PASSWORD`, `AKUN UTAMA`, `REPORT`, `KETERANGAN`, `AKSI`, `ADMIN`, `TEMPLATE`, `EXCEL`, `Display`, `Deskripsi`, `View`, `Tambah`, `Edit`, `Hapus`, `MASTER`, `Master`, `INVENTORY`, `Inventory`, `PEMBELIAN`, `Purchasing`, `PENJUALAN`, `Sales`, `KAS & BANK`, `Payable`, `GL`, `ANALISA`, `Analisa`, `FASILITAS`, `Tools`
- **Aksi / Fitur Operasional**: `Tambah Group`, `Buat User`, `Cancel`, `Simpan`, `Hapus`, `Select`, `Unselect`, `View`, `Add`, `Edit`, `Delete`, `Simpan Perubahan`
- **Struktur Tab Halaman Add/Detail**:
  - **Tab Detail** ➔ Fields: `group`, `h_nama_group`, `h_keterangan`, `h_active_flag`, `hd_nama_group`, `hd_keterangan`, `hd_active_flag`
  - **Tab Menu** ➔ Fields: `group`, `h_nama_group`, `h_keterangan`, `h_active_flag`, `hd_nama_group`, `hd_keterangan`, `hd_active_flag`
- **Skenario Validasi Aturan Wajib**: 
  - 🔴 `sulastri`

#### 🗂️ Menu: **Setting Menu**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/menu`
- **Konstruksi Tabel Master**: `DISPLAY`, `KODE NOMOR TRANSAKSI`, `EDIT TANGGAL`, `AUTO INSERT`, `BARCODE`, `CETAKAN`, `AKSI`, `0`, `1`, `2`, `3`
- **Aksi / Fitur Operasional**: `Save`, `Cancel`

#### 🗂️ Menu: **Cetakan**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/cetakan`
- **Konstruksi Tabel Master**: `No`, `Menu`, `Name`, `Modul`, `Cetakan A4`, `Layout`, `Content`
- **Aksi / Fitur Operasional**: `Ext`, `Edit Layout`, `Edit Content`, `Save`, `Cancel`, `Test Query`

#### 🗂️ Menu: **List Laporan**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/report`
- **Konstruksi Tabel Master**: `URUTAN`, `NAMA`, `DISPLAY`, `KETERANGAN`, `MODUL`, `AKTIF`, `DESKRIPSI`, `AKSI`
- **Aksi / Fitur Operasional**: `Save`, `Cancel`

#### 🗂️ Menu: **Cetak Barcode**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/cetak_barcode`
- **Konstruksi Tabel Master**: `BARCODE`, `KODE ITEM`, `NAMA ITEM`, `SATUAN`, `JUMLAH`
- **Aksi / Fitur Operasional**: `Tambah Cetak Barcode`, `Export Excel`, `Cancel`
- **Kamus Field Form (Add Data)**: `Search`, `table_length`, `item_code`, `barcode`, `display_name`, `uom`, `entered_qty`

#### 🗂️ Menu: **Info Whatsapp**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/whatsapp`
- **Konstruksi Tabel Master**: `TANGGAL`, `NAMA`, `PESAN`, `STATUS`, `TIPE`
- **Aksi / Fitur Operasional**: `Clear Filter`, `Cari Pesan`

#### 🗂️ Menu: **New**
- **Endpoint GUI**: `https://web.ninesoft.id/site/master/item/create`
- **Konstruksi Tabel Master**: `SATUAN LAIN`, `KONVERSI`, `KETERANGAN`, `DEFAULT`, `ACTION`, `SATUAN`, `HARGA BELI`, `HARGA JUAL`, `DISKON`, `AKTIF`, `HARGA 1`, `HARGA 2`, `HARGA 3`, `HARGA 4`, `Rp`, `%`, `GUDANG`, `STOK AWAL`, `JUMLAH`, `DISKON %`, `DISKON Rp`
- **Aksi / Fitur Operasional**: `View`, `New`, `Delete`, `Save`, `Undo`, `Info`, `Add Record`, `Cancel`, `Set as Default`, `Sync Acc`, `Sinkron All Item`
- **Struktur Tab Halaman Add/Detail**:
  - **Tab Detail** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table1_length`, `to_uom`, `to_qty`, `uom_flag`
  - **Tab Account** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table1_length`, `to_uom`, `to_qty`, `uom_flag`
  - **Tab Saldo Awal Program** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table1_length`, `to_uom`, `to_qty`, `uom_flag`
  - **Tab Harga** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table1_length`, `to_uom`, `to_qty`, `uom_flag`
  - **Tab Diskon** ➔ Fields: `item_code`, `item_name`, `item_kms`, `item_description`, `barcode`, `uom_code`, `kelompok`, `lokasi`, `min_stok`, `note`, `jenis`, `tipe_item`, `merek`, `made_in`, `grade`, `gambar`, `hpp_awal`, `rakitan`, `status`, `max_stock`, `max_order`, `table1_length`, `to_uom`, `to_qty`, `uom_flag`


---

## 15. Bukti Scraping Otomatis Ninesoft - Modul Transaksional

Bagian ini dihasilkan secara mandiri oleh skrip `catatan_ninesoft_transaksi.py` (Bot AntiGravity). Berisi jejak ekstraksi dari form operasional harian yang mencakup Purchasing, Penjualan, Inventory, dan Finance Kas/Bank.

#### 💼 Modul: **Inventory**
- **Endpoint GUI**: `https://web.ninesoft.id/site/inventory/info`
- **Konstruksi Data Tabel (Grids)**: `Kode Item`, `Nama Item`, `Stok`, `Satuan`, `HPP`, `Nilai`, `Analisa Jual`, `Order`, `Sisa`, `Jumlah`, `Selisih`, `Total:`, `-6,971,486.50`
- **Fitur Modul**: `Refresh`

#### 💼 Modul: **Pembelian**
- **Endpoint GUI**: `https://web.ninesoft.id/site/purchasing/info`
- **Konstruksi Data Tabel (Grids)**: `TANGGAL`, `NO PO`, `NO REFF`, `STATUS PO`, `DIBUTUHKAN`, `SUPPLIER`, `GUDANG`, `TOTAL`, `NAMA`, `LOKASI`, `Total:`, `0.00`, `Nama Supplier`, `Kode Supplier`, `Lokasi`, `Termin`, `Aksi`
- **Fitur Modul**: `Refresh`, `Add Supplier`

#### 💼 Modul: **Penjualan**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/info`
- **Konstruksi Data Tabel (Grids)**: `TANGGAL`, `NO SO`, `STATUS SO`, `CUSTOMER`, `PPN`, `TOTAL`, `SALES`, `NO REFF`, `KEPERLUAN`, `NAMA`, `LOKASI`, `Total:`, `0.00`, `Nama Customer`, `Kode customer`, `Lokasi`, `Termin`, `Aksi`
- **Fitur Modul**: `Refresh`, `Add Customer`

#### 💼 Modul: **Kas/Bank**
- **Endpoint GUI**: `https://web.ninesoft.id/site/finance/info`
- **Konstruksi Data Tabel (Grids)**: `KODE ACC`, `NAMA KAS BANK`, `SALDO AWAL`, `DEBET`, `KREDIT`, `SALDO`
- **Fitur Modul**: `Refresh`

#### 💼 Modul: **Sales Order**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/order`
- **Konstruksi Data Tabel (Grids)**: `STATUS`, `NO SO`, `NO REFF`, `TANGGAL`, `DIBUTUHKAN`, `CUSTOMER`, `SALES`, `TOTAL`, `AKSI`
- **Fitur Modul**: `New`, `Filter`, `Reset`, `Import`, `Download Template`, `Save`, `Cancel`, `Filter Data`
- **Struktur Tab Transaksi & Input Fields**:
  - **Tab Detail** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `sales`, `document_reff_no`, `table_length`
  - **Tab Info SO-SJ-INVOICE** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `sales`, `document_reff_no`
  - **Tab Stok** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `sales`, `document_reff_no`
  - **Tab Konversi** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `sales`, `document_reff_no`
  - **Tab Harga** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `sales`, `document_reff_no`
- **Alert & Validasi (Contingency)**: 
  - 🔴 `234,080`

#### 💼 Modul: **Surat Jalan**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/delivery`
- **Konstruksi Data Tabel (Grids)**: `STATUS`, `NO SJ`, `NO REFF`, `TANGGAL`, `CUSTOMER`, `NO SO`, `AKSI`
- **Fitur Modul**: `New`, `Filter`, `Reset`, `Import`, `Download Template`, `Save`, `Cancel`, `Filter Data`
- **Struktur Tab Transaksi & Input Fields**:
  - **Tab Detail** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `no_so`, `warehouse_id`, `document_reff_no`, `table_length`
  - **Tab Info SO-SJ-INVOICE** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `no_so`, `warehouse_id`, `document_reff_no`
  - **Tab Stok** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `no_so`, `warehouse_id`, `document_reff_no`
  - **Tab Konversi** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `no_so`, `warehouse_id`, `document_reff_no`
  - **Tab Harga** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `no_so`, `warehouse_id`, `document_reff_no`
- **Alert & Validasi (Contingency)**: 
  - 🔴 `224,000`

#### 💼 Modul: **Penjualan Cash**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/cash`
- **Konstruksi Data Tabel (Grids)**: `STATUS`, `NO FAKTUR`, `NO REFF`, `TANGGAL`, `CUSTOMER`, `SALES`, `TOTAL`, `AKSI`
- **Fitur Modul**: `New`, `Filter`, `Reset`, `Import`, `Download Template`, `Save`, `Cancel`, `Filter Data`
- **Struktur Tab Transaksi & Input Fields**:
  - **Tab Stok** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `note`, `sales`, `warehouse_id`, `document_reff_no`, `INPUT BARCODE`, `table_length`
  - **Tab Konversi** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `note`, `sales`, `warehouse_id`, `document_reff_no`, `INPUT BARCODE`, `table_length`
  - **Tab Harga** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `note`, `sales`, `warehouse_id`, `document_reff_no`, `INPUT BARCODE`, `table_length`
- **Alert & Validasi (Contingency)**: 
  - 🔴 `22,400`

#### 💼 Modul: **Penjualan Barcode (POS)**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/barcode`
- **Konstruksi Data Tabel (Grids)**: `STATUS`, `NO FAKTUR`, `NO REFF`, `TANGGAL`, `CUSTOMER`, `SALES`, `TOTAL`, `AKSI`
- **Fitur Modul**: `New`, `Filter`, `Reset`, `Filter Data`
- **Struktur Tab Transaksi & Input Fields**:
  - **Tab Stok** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `INPUT BARCODE`, `table_length`
  - **Tab Konversi** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `INPUT BARCODE`, `table_length`
  - **Tab Harga** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `INPUT BARCODE`, `table_length`
- **Alert & Validasi (Contingency)**: 
  - 🔴 `13,608`

#### 💼 Modul: **Faktur Penjualan**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/faktur`
- **Konstruksi Data Tabel (Grids)**: `STATUS`, `NO FAKTUR`, `NO REFF`, `TANGGAL`, `CUSTOMER`, `SALES`, `TOTAL`, `AKSI`
- **Fitur Modul**: `New`, `Filter`, `Reset`, `Import`, `Download Template`, `Save`, `Cancel`, `Filter Data`
- **Struktur Tab Transaksi & Input Fields**:
  - **Tab Detail** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `tipe_invoice`, `___.___-__.________`, `no_so`, `no_sj`, `sales`, `warehouse_id`, `document_reff_no`, `table_length`
  - **Tab Uang Muka** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `tipe_invoice`, `___.___-__.________`, `no_so`, `no_sj`, `sales`, `warehouse_id`, `document_reff_no`
  - **Tab Pelunasan** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `tipe_invoice`, `___.___-__.________`, `no_so`, `no_sj`, `sales`, `warehouse_id`, `document_reff_no`, `total_piutang`, `total_bayar`, `sisa_piutang`
  - **Tab Info SO-SJ-INVOICE** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `tipe_invoice`, `___.___-__.________`, `no_so`, `no_sj`, `sales`, `warehouse_id`, `document_reff_no`
  - **Tab Stok** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `tipe_invoice`, `___.___-__.________`, `no_so`, `no_sj`, `sales`, `warehouse_id`, `document_reff_no`
  - **Tab Konversi** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `tipe_invoice`, `___.___-__.________`, `no_so`, `no_sj`, `sales`, `warehouse_id`, `document_reff_no`
  - **Tab Harga** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `tipe_invoice`, `___.___-__.________`, `no_so`, `no_sj`, `sales`, `warehouse_id`, `document_reff_no`

#### 💼 Modul: **Faktur Retur Jual**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/retur`
- **Konstruksi Data Tabel (Grids)**: `STATUS`, `NO RETUR`, `NO REFF`, `TANGGAL`, `CUSTOMER`, `SALES`, `TOTAL`, `AKSI`
- **Fitur Modul**: `New`, `Filter`, `Reset`, `Import`, `Download Template`, `Save`, `Cancel`, `Filter Data`
- **Struktur Tab Transaksi & Input Fields**:
  - **Tab Detail** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `no_faktur`, `sales`, `warehouse_id`, `document_reff_no`, `table_length`
  - **Tab Pelunasan** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `no_faktur`, `sales`, `warehouse_id`, `document_reff_no`, `table_pelunasan_length`, `total_piutang`, `total_bayar`, `sisa_piutang`
  - **Tab Stok** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `no_faktur`, `sales`, `warehouse_id`, `document_reff_no`, `table_pelunasan_length`, `total_piutang`, `total_bayar`, `sisa_piutang`
  - **Tab Konversi** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `no_faktur`, `sales`, `warehouse_id`, `document_reff_no`, `table_pelunasan_length`, `total_piutang`, `total_bayar`, `sisa_piutang`
  - **Tab Harga** ➔ `KOSONG OTOMATIS`, `document_date`, `person_name`, `person_code`, `site_name`, `address1`, `payment_term`, `no_faktur`, `sales`, `warehouse_id`, `document_reff_no`, `table_pelunasan_length`, `total_piutang`, `total_bayar`, `sisa_piutang`
- **Alert & Validasi (Contingency)**: 
  - 🔴 `8,100`

#### 💼 Modul: **Laporan Penjualan**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/report`
- **Fitur Modul**: `Cancel`

#### 💼 Modul: **SO**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/info/so`
- **Konstruksi Data Tabel (Grids)**: `TANGGAL`, `NO SO`, `STATUS SO`, `CUSTOMER`, `PPN`, `TOTAL`, `SALES`, `NO REFF`, `KEPERLUAN`, `NAMA`, `LOKASI`, `Total:`, `0.00`, `Nama Customer`, `Kode customer`, `Lokasi`, `Termin`, `Aksi`
- **Fitur Modul**: `Refresh`, `Add Customer`

#### 💼 Modul: **Penjualan & Retur**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/info/inv`
- **Konstruksi Data Tabel (Grids)**: `TANGGAL`, `NO FAKTUR`, `STATUS`, `CUSTOMER`, `GUDANG`, `PPN`, `TOTAL`, `DISKON`, `PPN AMOUNT`, `TOTAL BERSIH`, `SALES`, `NAMA`, `LOKASI`, `Total:`, `0.00`, `Nama Supplier`, `Kode Supplier`, `Lokasi`, `Termin`, `Aksi`, `PERSON ID`, `Nama Customer`, `Kode customer`, `Alamat`, `Person Site Id`
- **Fitur Modul**: `Refresh`, `Add Supplier`

#### 💼 Modul: **Pelunasan Piutang**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/info/piutang`
- **Konstruksi Data Tabel (Grids)**: `TANGGAL TERIMA`, `NO VOUCER`, `NO FAKTUR`, `BANK`, `CUSTOMER`, `NILAI`, `POTONGAN`, `NILAI FAKTUR`, `STATUS`, `NAMA`, `LOKASI`, `Total:`, `0.00`, `Nama Supplier`, `Kode Supplier`, `Lokasi`, `Termin`, `Aksi`, `PERSON ID`, `Nama Customer`, `Kode customer`, `Alamat`, `Person Site Id`
- **Fitur Modul**: `Refresh`, `Add Supplier`

#### 💼 Modul: **Summary Piutang**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/info/sum_piutang`
- **Konstruksi Data Tabel (Grids)**: `Supplier`, `Saldo Awal`, `Debet`, `Kredit`, `Saldo Akhir`, `Nama`, `Kode`, `Total`, `0.00`, `241,912,382.54`, `217,077,282.29`, `24,835,100.25`, `PERSON ID`, `Nama Customer`, `Kode customer`, `Lokasi`, `Alamat`, `Termin`, `Aksi`, `Person Site Id`
- **Fitur Modul**: `Refresh`

#### 💼 Modul: **Kartu Piutang**
- **Endpoint GUI**: `https://web.ninesoft.id/site/sales/info/kartu`
- **Konstruksi Data Tabel (Grids)**: `Tanggal`, `No Transaksi`, `Transaksi`, `Debet`, `Kredit`, `Saldo Akhir`, `Keterangan`, `Nama Customer`, `Kode Customer`, `Lokasi`, `Termin`, `Aksi`, `PERSON ID`, `Kode customer`, `Alamat`, `Person Site Id`
- **Fitur Modul**: `Refresh`, `Add Customer`

