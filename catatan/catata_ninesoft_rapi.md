# ANALISA FITUR NINESOFT ACCOUNTING ONLINE

## Panduan Kelompok Fitur untuk Referensi Pengembangan StoreSoft

---

Dokumen ini menyajikan seluruh fitur NineSoft Accounting Online yang telah diobservasi, dikelompokkan secara terstruktur agar mudah dipahami dan dijadikan acuan pengembangan aplikasi StoreSoft. **Tidak ada informasi yang dihapus atau diubah**, hanya dikelompokkan ulang untuk kejelasan.

---

## DAFTAR ISI

| Kelompok | Halaman |
|----------|---------|
| 0. Dashboard & Notifikasi Utama | 1 |
| 1. Master Data (Fondasi Sistem) | 2 |
| 2. Modul Inventory (Manajemen Stok) | 5 |
| 3. Modul Pembelian (Purchasing) | 7 |
| 4. Modul Penjualan (Sales) | 9 |
| 5. Modul Kas & Bank (Finance) | 12 |
| 6. Modul General Ledger (Akuntansi) | 14 |
| 7. Laporan & Analitik Manajerial | 16 |
| 8. Fasilitas & Konfigurasi Sistem | 18 |
| 9. Pola UI, Aksi, dan Fitur Ekspor | 20 |
| 10. Fitur Lanjutan & Batasan Sistem | 22 |
| 11. Katalog Ekspor & Daftar Field | 23 |
| 12. WhatsApp & Notifikasi | 24 |
| 13. Alur Proses Bisnis End-to-End | 25 |
| 14. Hasil Scraping - Modul Master | 26 |
| 15. Hasil Scraping - Modul Transaksional | 30 |

---

# KELOMPOK 0: DASHBOARD & NOTIFIKASI UTAMA

---

Sistem menyediakan dashboard interaktif untuk pemantauan real-time kondisi bisnis.

### A. Widget Monitoring

| Widget | Fungsi | Interaktivitas |
|--------|--------|----------------|
| **Stok** | Menampilkan nilai persediaan | Click-through ke laporan detail |
| **Pembelian** | Menampilkan nilai pembelian | Click-through ke laporan detail |
| **Omzet (Sales)** | Menampilkan total penjualan | Click-through ke laporan detail |
| **Hutang (AP)** | Menampilkan kewajiban ke supplier | Click-through ke laporan detail |
| **Piutang (AR)** | Menampilkan tagihan dari customer | Click-through ke laporan detail |
| **Saldo Kas/Bank** | Menampilkan posisi likuiditas | Click-through ke laporan detail |

### B. Alert & Reminder (To-Do List)

| Jenis Alert | Trigger | Tujuan |
|-------------|---------|--------|
| **Barang Harus Dipesan** | Stok di bawah minimum (Reorder Point) | Purchasing segera order |
| **PO Outstanding** | Pesanan ke supplier belum diterima | Monitoring pengadaan |
| **SO Outstanding** | Pesanan customer belum dikirim | Bagian gudang segera kirim |
| **Piutang Jatuh Tempo** | Tagihan melewati batas kredit | Collection segera tagih |

### C. Visualisasi Dashboard

| Visualisasi | Jenis | Kegunaan |
|-------------|-------|----------|
| Grafik batang | Perbandingan Bulan Ini vs Bulan Lalu | Analisis tren harian |
| Grafik Donut/Pie | Komposisi kelompok barang | Identifikasi kontribusi penjualan |
| Pie Chart Umur Piutang | Belum jatuh tempo, <30, 31-60, 61-90, >90 hari | Manajemen risiko piutang |

---

# KELOMPOK 1: MASTER DATA (Fondasi Sistem)

---

Menu Master adalah fondasi sistem NineSoft yang mengelola seluruh data entitas bisnis secara hierarkis dan terpusat.

## 1.A. Barang dan Jasa (Master Item)

Modul paling detail yang mencakup 5 tab fungsional untuk setiap item.

### Tab 1: Identitas Utama (Header & Data Stok)

| Field | Fungsi |
|-------|--------|
| `Kode Item` | Identitas unik barang |
| `Nama Item` (dua baris) | Nama lengkap dan nama alternatif |
| `Barcode` | Untuk scanning di POS |
| `Satuan Utama` | Unit dasar (pcs, box, dll) |
| `Kelompok` | Kategori barang |
| `Gudang Utama` | Lokasi penyimpanan default |
| `Min Stok` | Batas minimal untuk alert reorder |
| `Max Stok` | Batas maksimal kapasitas gudang |
| `Min Order` | Minimal pemesanan ke supplier |
| `Max Order` | Maksimal pemesanan ke supplier |
| `Rakitan` (checkbox) | Menandai barang hasil produksi (BOM) |
| `Status (Aktif)` | Menonaktifkan barang tanpa menghapus |
| `Tipe Item` | Persediaan / Jasa / Biaya |

### Tab 2: Harga Jual (Pricing)

| Fitur | Keterangan |
|-------|-------------|
| **Multi-Level Pricing** | Mendukung 4 level harga (`Harga 1` s/d `Harga 4`) |
| **Harga Beli terakhir** | Referensi untuk menentukan harga jual |
| **Diskon default** | Diskon per item (Rp atau %) |

### Tab 3: Konversi Satuan (Unit Conversion)

| Fungsi | Contoh |
|--------|--------|
| Mengatur rasio unit | 1 `BOX` = 12 `PCS` |
| Menjaga akurasi stok | Stok tetap akurat saat beli besar, jual kecil |

### Tab 4: Mapping Akun (G/L Link)

Menghubungkan setiap pergerakan item dengan Akuntansi secara otomatis.

| Slot Akun | Fungsi |
|-----------|--------|
| `Akun Penjualan` | Pendapatan saat menjual |
| `Akun Persediaan` | Nilai stok di neraca |
| `Akun Diskon Jual` | Potongan harga |
| `Akun HPP` | Harga Pokok Penjualan |
| `Akun Retur Jual` | Pengembalian barang dari customer |
| `Akun Retur Beli` | Pengembalian barang ke supplier |
| `Akun Hutang Suspend` | Hutang yang ditangguhkan |

### Tab 5: Saldo Awal

Untuk input stok awal per gudang saat migrasi ke sistem.

---

## 1.B. Customer & Supplier

Manajemen database mitra bisnis dengan fitur kontrol finansial.

### Fitur Customer

| Field | Fungsi |
|-------|--------|
| `Kode & Nama Customer` | Identitas unik |
| `Batas Piutang` (Rp) | Limit kredit, sistem peringatan jika terlampaui |
| `Batas Jatuh Tempo` (Hari) | Maksimal hari pembayaran |
| `Rayon` | Pengelompokan wilayah |
| `Salesman` | Penugasan tenaga penjual |
| `Termin Pembayaran` | TOP (Terms of Payment) |
| `NPWP` | Untuk faktur pajak |
| `Nama PKP` | Pengusaha Kena Pajak |
| `Alamat Pajak` | Untuk pembuatan faktur pajak |

### Fitur Multi-Address Management

| Jenis Alamat | Kegunaan |
|--------------|----------|
| `Alamat Pusat` | Kantor pusat customer |
| `Alamat Tagihan` | Untuk pengiriman invoice |
| `Alamat Pengiriman` | Untuk pengiriman barang |

### Fitur Supplier

| Field | Fungsi |
|-------|--------|
| `Kode & Nama Supplier` | Identitas unik |
| `Batas Hutang` (Rp) | Memantau kewajiban perusahaan |
| `Termin Pembayaran` | Jatuh tempo pembayaran ke supplier |

---

## 1.C. Formula (BOM - Bill of Materials)

Digunakan untuk mendefinisikan struktur produk rakitan (Finished Goods).

| Komponen | Keterangan |
|----------|-------------|
| **Header** | Memilih item hasil produksi (yang sudah dicentang `Rakitan`) |
| **Detail (Bahan Baku)** | Daftar item bahan baku + jumlah untuk membuat 1 unit barang jadi |
| **Masa berlaku formula** | `Mulai` & `Selesai` |

---

## 1.D. Account (Master Akun & COA)

### Chart of Accounts (COA)

Pengelolaan bagan akun hierarkis.

| Tipe Akun | Contoh |
|-----------|--------|
| Harta (Aset) | Kas, Bank, Persediaan, Piutang |
| Hutang (Liabilitas) | Hutang Dagang, Hutang Pajak |
| Modal (Ekuitas) | Modal Disetor, Laba Ditahan |
| Pendapatan | Penjualan, Pendapatan Jasa |
| Biaya | HPP, Biaya Operasional |

### Setting COA (Automatic Mapping)

Konfigurasi "default akun" untuk seluruh sistem.

| Mapping | Akun Tujuan |
|---------|-------------|
| Uang Muka | Akun penampung deposit |
| Selisih Kurs | Akun gain/loss kurs |
| Laba Ditahan | Akun saat closing periode |

### Aktiva Tetap (Fixed Assets)

Modul khusus untuk mencatat aset perusahaan.

| Field | Keterangan |
|-------|-------------|
| `Nama Asset` | Gedung, Kendaraan, Peralatan |
| `Nilai Perolehan` | Harga beli aset |
| `Masa Tanggal (Start/End)` | Periode pemakaian |
| `Umur Asset` (Tahun) | Masa manfaat |
| `Rate Depresiasi` | Persentasi penyusutan per tahun |
| `Metode Penyusutan` | **Straight Line** (Garis Lurus) |
| `Akun Aset` | Akun neraca untuk nilai aset |
| `Akun Akumulasi Penyusutan` | Kontra-aset |
| `Akun Biaya Penyusutan` | Akun laba rugi |

---

## 1.E. Fitur Master Lainnya

| Menu | Fungsi |
|------|--------|
| **Daftar Harga** | Price list hingga **6 level harga** (`Harga 1` s/d `Harga 6`) untuk klasifikasi pelanggan (Retail, Grosir, Distributor) |
| **Import Data Master** | Impor **Akun (COA)**, **Barang**, **Customer**, dan **Supplier** dari Excel |
| **User Group** | Role-based Access Control (RBAC) |
| **Setting Menu** | Kustomisasi tampilan menu per user |
| **Cetakan** | Template faktur, surat jalan, dan dokumen lainnya |
| **Info Whatsapp** | Integrasi notifikasi ke WhatsApp |
| **Satuan** | Manajemen unit barang |
| **Gudang** | Manajemen lokasi penyimpanan |
| **Jangka Waktu Kredit** | Termin pembayaran |
| **Pajak** | Kode pajak (PPN/PPH) |
| **Alamat** | Manajemen alamat pengiriman & penagihan |
| **Sales** | Data tenaga penjual untuk tracking komisi |

---

# KELOMPOK 2: MODUL INVENTORY (Manajemen Stok)

---

Fokus pada pengelolaan siklus hidup stok barang, mulai dari pemantauan saldo, mutasi antar gudang, hingga audit fisik (opname).

## 2.A. Pusat Informasi Stok (Info Barang Stok)

Modul monitoring real-time yang menjadi jantung operasional gudang.

### Filter

| Filter | Opsi |
|--------|------|
| `Lokasi` | Pilih cabang |
| `Gudang` | Spesifik atau Semua |
| `Rentang Tanggal` | Periode tertentu |
| `Tampilkan Nol` | Filter item yang tidak memiliki saldo |

### Tab Stok (Real-time Balance)

| Komponen | Penjelasan |
|----------|------------|
| `Stok Fisik` | Jumlah barang di gudang |
| `Order` | Barang sedang dipesan (belum datang) |
| `Sisa` | Tersedia untuk dijual (`Stok Fisik` - `Order`) |
| `HPP` | Harga Pokok Penjualan saat ini |
| `Total Nilai` | Qty x HPP |
| `Analisa Jual` | Perbandingan `Stok Saat Ini + Order` vs `Qty Terjual` (deteksi stock-out) |

### Tab Summary Stok (Mutasi Periode)

Rekapitulasi pergerakan barang dalam periode:

```
Stok Awal + Masuk - Keluar = Stok Akhir
```

### Tab Kartu Stok (Audit Trail)

Riwayat mendetail per item secara kronologis.

| Field | Keterangan |
|-------|-------------|
| `Tanggal` | Waktu transaksi |
| `Nomor Transaksi` | No dokumen (PO, SJ, dll) |
| `No Referensi` | Referensi tambahan |
| `Pihak/Relasi` | Customer atau Supplier |
| `Masuk` | Qty masuk |
| `Keluar` | Qty keluar |
| `Saldo` | Saldo setelah transaksi |
| `Gudang` | Lokasi penyimpanan |

**Fitur Khusus:**
- `Kartu Stok Semua Gudang` → Mutasi lintas seluruh gudang dalam satu tampilan
- Filter rentang tanggal, kategori barang, pemilihan satu atau beberapa gudang sekaligus (PUSAT & CABANG A)
- Kirim laporan via **Email** langsung dari sistem

---

## 2.B. Mutasi Stok (Pindah Gudang)

Mencatat perpindahan fisik barang antar lokasi internal perusahaan.

| Komponen | Keterangan |
|----------|-------------|
| `No Mutasi` | Auto-generated |
| `Tanggal` | Tanggal mutasi |
| `Gudang Asal` | Lokasi sumber |
| `Gudang Tujuan` | Lokasi tujuan |
| `No Referensi` | Dokumen pendukung |
| `Keterangan` | Alasan mutasi |
| `Detail (Grid)` | Item, Qty, Satuan |

**Efek Sistem:**
- Saldo Gudang Asal berkurang
- Saldo Gudang Tujuan bertambah
- **Tidak ada** efek ke jurnal akuntansi (hanya pindah lokasi)

---

## 2.C. Koreksi & Penyesuaian (Stok Adjustment)

Mengelola perubahan stok akibat faktor non-transaksi (barang rusak, bonus supplier).

| Fitur | Keterangan |
|-------|-------------|
| **Akun GL (COA)** | Wajib memilih akun pengimbang (misal: Biaya Barang Rusak) |
| **Adjust Nilai (checkbox)** | OFF → Koreksi kuantitas saja. ON → Koreksi nilai persediaan (HPP) tanpa mengubah fisik |
| `No Bukti` | Nomor unik adjustment |
| `Alasan/Uraian` | Penjelasan penyesuaian |

---

## 2.D. Audit Stok (Stok Opname)

Fitur krusial untuk sinkronisasi data sistem dengan kenyataan fisik di lapangan.

| Komponen | Penjelasan |
|----------|------------|
| **Stok Sistem** | Saldo menurut aplikasi (Read-only) |
| **Stok Fisik** | Angka input user setelah hitungan fisik |
| **Selisih** | Otomatis dihitung (`Fisik` - `Sistem`), akan diposting sebagai adjustment |

### Fitur Efisiensi

| Fitur | Fungsi |
|-------|--------|
| **Bulk Import** | Tombol `Excel` untuk unggah hasil hitungan fisik massal |
| **Barcode Search** | Input barcode untuk pencatatan cepat |
| **Stok Baru** | Menambah item yang ditemukan di gudang tapi belum terdaftar |

**Workflow:** Dilakukan saat "Cut-off" atau akhir periode sebelum closing.

---

## 2.E. Perakitan (Assembly) & BOM Integration

Menangani proses produksi sederhana atau rebranding barang.

| Langkah | Proses |
|---------|--------|
| 1 | Pilih `Item Hasil` (dari Master yang bertipe Rakitan) |
| 2 | Input jumlah yang akan dibuat |
| 3 | Sistem otomatis menarik daftar `Item Bahan` dari Formula (BOM) |
| 4 | HPP barang jadi = total HPP seluruh bahan baku |

---

# KELOMPOK 3: MODUL PEMBELIAN (Purchasing)

---

Mengelola siklus pengadaan barang mulai dari pemesanan (PO), penerimaan barang, hingga pelunasan kewajiban (Hutang).

## 3.A. Info Pembelian (Dashboard & Monitoring)

Pusat kendali untuk memantau status pengadaan.

### Visualisasi Utama

| Grafik | Fungsi |
|--------|--------|
| **Grafik Umur Hutang (Aging)** | Analisis jatuh tempo hutang |
| **Grafik Pembelian** | Tren nilai pembelian harian |

### Tracking Area (Tabulated View)

| Tab | Isi |
|-----|-----|
| **Tab PO** | Pesanan yang masih outstanding (belum diterima) |
| **Tab Pembelian & Retur** | Daftar lengkap mutasi invoice dan pengembalian |
| **Tab Pelunasan** | Histori pembayaran yang telah dilakukan |
| **Tab Kartu Hutang** | Buku pembantu hutang per supplier |
| **Summary Outstanding Uang Muka** | Saldo DP ke supplier yang belum dikompensasi faktur |

---

## 3.B. Order Pembelian (PO - Purchase Order)

Dokumen komitmen pemesanan. **Belum** mempengaruhi stok maupun laporan keuangan.

### Header

| Field | Keterangan |
|-------|-------------|
| `No PO` | Auto-generated |
| `Tanggal` | Tanggal order |
| `Supplier` | Pilih dari master supplier |
| `Gudang` | Tujuan penyimpanan |
| `Termin Pembayaran` | Jatuh tempo |
| `Tanggal Dibutuhkan` | Target kedatangan |
| `No Referensi` | Dokumen pendukung |
| `Keterangan` | Catatan |

### Detail (Grid)

| Field | Keterangan |
|-------|-------------|
| `Item` | Pilih dari master barang |
| `Qty` | Jumlah pesanan |
| `Satuan` | Unit |
| `Harga Input` | Harga kesepakatan |
| `Diskon (Rp/%)` | Potongan |
| `Subtotal` | Qty x Harga - Diskon |

**Fitur Khusus:** Tombol `Browse Item` untuk pemilihan barang masal, `Info PO-INVOICE` untuk cek status pemenuhan order.

---

## 3.C. Faktur Pembelian (Purchase Invoice)

Modul krusial untuk **pengakuan persediaan masuk** (stok) dan **pengakuan kewajiban** (hutang).

### Integrasi Pintar (Matching)

Tombol **`Pilih No PO`** untuk menarik data pesanan → memastikan konsistensi harga dan jumlah antara pesanan dan penerimaan.

### Sistem 4 Tab Utama

| Tab | Fungsi |
|-----|--------|
| **Tab 1: Detail** | Input item komoditas (Barang/Jasa) |
| **Tab 2: Uang Muka** | Mengompensasi DP yang telah dibayarkan untuk memotong total tagihan |
| **Tab 3: Pelunasan** | Pembayaran langsung di tempat. Field: `Voucher`, `Akun Kas/Bank`, `No Cek/Giro`, `Potongan`, `Jumlah Bayar` |
| **Tab 4: Info PO-INVOICE** | Perbandingan per baris item: Qty PO vs Qty difakturkan |

---

## 3.D. Retur Pembelian (Faktur Retur Beli)

Mencatat pengembalian barang ke pemasok.

| Fitur | Keterangan |
|-------|-------------|
| **Pilih No Faktur** | Referensi faktur asal |
| **Efek** | Nilai retur memotong saldo hutang pada faktur spesifik (bukan hanya saldo global) |
| **Efek Stok** | Mengurangi stok barang di gudang |
| **Efek GL** | Mengurangi saldo Hutang Dagang di Buku Besar |

---

## 3.E. Pelunasan Hutang (Settlement)

Proses penyelesaian tagihan melalui modul Kas & Bank (menu Kas/Bank > Bukti Kas Keluar).

### Alur Kerja

| Langkah | Aksi |
|---------|------|
| 1 | Pilih `Supplier` dan `Akun Kas/Bank` sumber dana |
| 2 | Klik **`Browse Nota`** → tarik seluruh faktur yang belum lunas |
| 3 | Input jumlah `Bayar` dan `Potongan` (diskon pembayaran dipercepat) |
| 4 | Untuk Giro: isi `No Check/Giro` dan `Tgl Jatuh Tempo` |
| 5 | `Tab Lain-lain`: catat biaya admin bank atau pembulatan |

### Credit Limit Warning

Notifikasi atau blokir otomatis jika total hutang ke supplier melampaui `Batas Hutang` di Master.

### Automatic Journaling

Setiap simpan data Faktur/Retur akan langsung membentuk jurnal akuntansi real-time berdasarkan mapping akun GL per item atau kelompok barang.

---

# KELOMPOK 4: MODUL PENJUALAN (Sales)

---

Mengelola seluruh siklus pendapatan pelanggan mulai dari pesanan (SO), pengiriman barang (Surat Jalan), hingga penagihan (Invoice) dan pelunasan piutang.

## 4.A. Info Penjualan (Dashboard & Tracking)

Pusat pemantauan kinerja penjualan dan kesehatan piutang dagang.

### Analisis Visual

| Grafik | Fungsi |
|--------|--------|
| **Grafik Penjualan** | Tren harian omzet |
| **Fast Moving Item** | Produk terlaris (kuantitas & kategori) untuk optimasi stok |
| **Umur Piutang (Aging AR)** | Ringkasan saldo piutang berdasarkan usia tagihan |

### Monitoring Operasional

Tab khusus untuk melacak status Sales Order (SO) dan Surat Jalan (SJ) yang belum difakturkan.

---

## 4.B. Sales Order (SO - Pesanan Penjualan)

Dokumen awal untuk mencatat pesanan pelanggan sebagai dasar perencanaan stok dan pengiriman.

### Header

| Field | Keterangan |
|-------|-------------|
| `No SO` | Auto-generated |
| `Tanggal` | Tanggal pesanan |
| `Customer` | Pilih dari master customer |
| `Lokasi` | Cabang |
| `Termin` | Jatuh tempo pembayaran |
| `Salesman` | Tenaga penjual |
| `No Referensi` | Dokumen pendukung |
| `Keterangan` | Catatan |

### Detail (Grid)

| Field | Keterangan |
|-------|-------------|
| `Barcode/Item` | Pilih dari master |
| `Nama Item` | Otomatis terisi |
| `Qty` | Jumlah pesanan |
| `Satuan` | Unit |
| `Harga Jual` | Otomatis berdasarkan level harga customer |
| `Diskon (%)` | Potongan |
| `Subtotal` | Perhitungan otomatis |

**Logika:** SO **belum** memotong stok fisik maupun stok di Buku Besar. SO menjadi "dokumen induk" bagi proses selanjutnya.

---

## 4.C. Surat Jalan (SJ - Delivery Order)

Dokumen instruksi pengeluaran barang yang secara otomatis **mengurangi saldo fisik di gudang**.

| Fitur | Keterangan |
|-------|-------------|
| **Header Khusus** | `Gudang` sumber barang, identifikasi Customer |
| **Integrasi Pintar** | Tombol **`Pilih No SO`** → bagian gudang menarik item dari pesanan yang disetujui |
| **Efek Sistem** | Stok fisik berkurang saat SJ disimpan |

Status SJ akan dipantau hingga menjadi Faktur Penjualan.

---

## 4.D. Faktur Penjualan (Sales Invoice)

Modul pengakuan pendapatan dan timbulnya piutang dagang.

### Fleksibilitas Jalur (Workflow)

| Jalur | Cara |
|-------|------|
| **SO-Invoice** | Menarik data langsung dari SO (via `Pilih No SO`) |
| **SJ-Invoice** | Menarik data dari pengiriman yang sudah dilakukan (via `Browse No SJ`) |

### Struktur 4 Tab Utama

| Tab | Fungsi |
|-----|--------|
| **Tab 1: Detail** | Grid input item atau impor dari dokumen referensi |
| **Tab 2: Uang Muka** | Memotong total tagihan dengan deposit/uang muka |
| **Tab 3: Pelunasan** | Fitur kasir instan untuk pembayaran tunai/kartu saat faktur diterbitkan |
| **Tab 4: Info SO-SJ-INVOICE** | Laporan sinkronisasi per item (Qty Pesanan vs Kirim vs Faktur) |

---

## 4.E. Penjualan Barcode (POS - Point of Sale)

Antarmuka khusus ritel untuk transaksi cepat di meja kasir.

| Karakteristik | Keterangan |
|---------------|-------------|
| **Desain** | Minimalis, input terpusat di field **Barcode** |
| **Otomatisasi** | Setiap scan barcode otomatis menambah baris dan kuantitas item |
| **Cetak** | Mendukung printer thermal secara langsung |

---

## 4.F. Pelunasan Piutang (Collection)

Proses penerimaan uang dari pelanggan melalui menu Kas/Bank > Bukti Kas Masuk (BKM).

### Alur Kerja

| Langkah | Aksi |
|---------|------|
| 1 | Pilih `Customer` dan `Akun Kas/Bank` penampung dana |
| 2 | Klik **`Browse Nota`** → tampilkan faktur yang masih memiliki saldo piutang |
| 3 | Masukkan `Bayar` (Lunas/Parsial) dan `Potongan` (diskon/pinalti) |
| 4 | Untuk non-tunai: isi `No Check/Giro`, `Bank`, `Tanggal Jatuh Tempo` |

---

## 4.G. Credit Control & Pricing Strategy

| Fitur | Mekanisme |
|-------|-----------|
| **Credit Control** | Sistem memperingatkan/memblokir transaksi jika piutang melampaui `Batas Piutang` atau `Batas Jatuh Tempo` |
| **Pricing Strategy** | Harga jual otomatis terisi berdasarkan `Item` + `Level Harga` pada Master Pelanggan (Harga 1-5) |
| **Sales Performance** | Setiap transaksi terikat dengan `Salesman` untuk komisi dan analisis performa |

---

## 4.H. Faktur Retur Jual

Mencatat pengembalian barang dari pelanggan.

| Komponen | Keterangan |
|----------|-------------|
| **Referensi** | Pilih `No Faktur` asal |
| **Efek** | Nilai retur memotong piutang pada faktur spesifik |
| **Stok** | Menambah stok barang di gudang |
| **GL** | Mengurangi saldo Piutang Dagang |

---

# KELOMPOK 5: MODUL KAS & BANK (Finance)

---

Pusat pengelolaan arus kas (Cash Management) yang mencakup seluruh aktivitas penerimaan, pengeluaran, serta rekonsiliasi instrumen perbankan (Cek/Giro).

## 5.A. Info Kas Bank (Monitoring Likuiditas)

Dashboard real-time untuk memantau posisi dana dan tren arus kas perusahaan.

| Fitur | Keterangan |
|-------|-------------|
| **Grafik Penerimaan & Pengeluaran** | Perbandingan cash-in dan cash-out antar periode (bulan ini vs bulan lalu) |
| **Saldo Kas/Bank** | Ringkasan saldo akhir per akun (Kas Tunai, Rekening Bank, Petty Cash) |

---

## 5.B. Bukti Kas Masuk (BKM - Cash Receipt)

Mencatat seluruh uang yang masuk ke dalam sistem perusahaan.

### Komponen Header

| Field | Keterangan |
|-------|-------------|
| `No Voucher` | Auto-generated |
| `Tanggal` | Tanggal transaksi |
| `Akun Kas/Bank` | Tujuan dana |
| `Dari` | Customer / Pihak Lain |
| `Total` | Jumlah uang masuk |
| `No Cek/Giro` | Untuk pembayaran non-tunai |
| `Tanggal Jatuh Tempo` | Untuk cek/giro |

### Integrasi Transaksi (Detail Tab)

| Tab | Fungsi |
|-----|--------|
| **Tab Detail (Piutang)** | `Browse Nota` → pilih faktur penjualan yang dilunasi. Input `Potongan` (Diskon) dan nominal `Terima` |
| **Tab Lain-lain** | Pendapatan non-operasional (Bunga Bank, Penjualan Aset) dengan pemetaan langsung ke akun GL |
| **Tab Hutang** | Pencatatan pengembalian dana dari supplier (Retur Beli Tunai) |

---

## 5.C. Bukti Kas Keluar (BKK - Cash Disbursement)

Mencatat seluruh pengeluaran dana perusahaan.

### Alur Operasional

| Jenis Transaksi | Cara |
|----------------|------|
| **Pelunasan Hutang** | Melunasi faktur pembelian supplier via `Browse Nota` |
| **Biaya Operasional** | Mencatat beban usaha (Gaji, Listrik, Sewa) melalui tab `Lain-lain` |
| **Biaya Transfer** | Tombol cepat untuk input biaya bank |
| **Tolakan Giro** | Menangani giro yang ditolak secara sistematis |

---

## 5.D. Tracking Cek & Giro (BG Masuk/Keluar)

Manajemen siklus hidup instrumen pembayaran nontunai.

### Siklus Status

| Status | Keterangan |
|--------|-------------|
| **NEW** | Giro baru dicatat, belum masuk tanggal efektif pencairan |
| **CAIR (Cleared)** | Setelah kliring bank, status diubah CAIR → perbarui saldo akun bank |
| **TOLAK (Rejected)** | Giro ditolak bank (dana tidak cukup/salah data) |

### Audit Tool

Filter radio button (NEW, CAIR, TOLAK) untuk memantau beban atau potensi dana yang masih menggantung.

---

## 5.E. Laporan Buku Kas-Bank (Account Ledger)

Laporan kronologis mutasi untuk setiap akun kas atau bank.

| Struktur | Field |
|----------|-------|
| **Header** | Tanggal, No Voucher, Keterangan |
| **Mutasi** | Penerimaan (Debit), Pengeluaran (Credit) |
| **Saldo** | Saldo kumulatif |

### Opsi Output

- Layout Portrait / Landscape
- Ekspor Excel
- Cetak PDF
- Kirim Email

---

## 5.F. UI Patterns & Logic

| Fitur | Keterangan |
|-------|-------------|
| **Voucher Prefixing** | Penomoran voucher memiliki prefix berdasarkan jenis akun (`K` untuk Kas, `B` untuk Bank) |
| **Split-Row Entry** | Satu voucher BKM/BKK untuk melunasi banyak nota sekaligus atau membagi pengeluaran ke beberapa akun biaya |

---

# KELOMPOK 6: MODUL GENERAL LEDGER (Akuntansi)

---

Modul inti akuntansi untuk pelaporan keuangan, pencatatan jurnal penyesuaian, dan finalisasi periode pembukuan (Closing).

## 6.A. Transaksi Jurnal (General Journal)

Digunakan untuk mencatat transaksi yang tidak tercover modul operasional atau jurnal penyesuaian (Adjustment).

### Header Formulir

| Field | Keterangan |
|-------|-------------|
| `No Jurnal` | Otomatis (prefix `GL-`) |
| `Tanggal` | Tanggal transaksi |
| `No Referensi` | Dokumen pendukung |
| `Keterangan` | Narasi global transaksi |

### Detail Line (Double-Entry Logic)

| Field | Keterangan |
|-------|-------------|
| `Akun` | Pilih via `Deskripsi Account` atau `Kode Account` |
| `Uraian` | Keterangan spesifik per baris |
| `Debet` | Nominal debit |
| `Kredit` | Nominal kredit |

### Kontrol Integritas

| Fitur | Keterangan |
|-------|-------------|
| **Total Debet & Kredit** | Ringkasan real-time |
| **Selisih** | Ditampilkan secara real-time |
| **Validasi** | Data hanya dapat disimpan jika `Selisih = 0` |

### Isolasi Jurnal Otomatis (Read-Only Logic)

| Sumber Jurnal | Apakah muncul di menu GL? |
|---------------|---------------------------|
| **Otomatis** (dari Penjualan, Pembelian, Kas) | ❌ **TIDAK** muncul |
| **Manual** (input via GL) | ✅ Muncul |

**Integritas:** Perubahan nilai pada jurnal otomatis hanya bisa dilakukan dengan merevisi dokumen sumbernya (mengedit Faktur Penjualan). Ini mencegah diskoneksi antara nilai invoice dan nilai jurnal.

---

## 6.B. Closing Periode Akuntansi (Penutupan Buku)

Fitur untuk mengunci transaksi dan memfinalisasi laporan keuangan bulanan/tahunan.

### Workflow Closing

| Langkah | Aksi |
|---------|------|
| 1 | Sistem menampilkan daftar 12 bulan + status (`New/Open` atau `Closed`) |
| 2 | Klik **`Closing`** → eksekusi penguncian data dan pemindahan saldo laba/rugi ke akun modal (Retained Earnings) |
| 3 | **Fitur Undo** → membuka kembali periode terkunci (otoritas supervisor) |

### Efek Closing

| Perubahan | Keterangan |
|-----------|-------------|
| Transaksi terkunci | Tidak bisa edit/hapus di bulan tersebut |
| Laba/Rugi dipindahkan | Ke akun Laba Ditahan |
| Saldo akhir | Menjadi saldo awal bulan berikutnya |

---

## 6.C. Rekalkulasi Sistem (Fasilitas > Reproses)

Serangkaian fungsi pemeliharaan data untuk menjamin sinkronisasi antara operasional dan akuntansi.

### Jenis Reproses

| Reproses | Fungsi | Wajib Sebelum Closing |
|----------|--------|----------------------|
| **Reproses Stok** | Sinkronisasi saldo fisik gudang dengan catatan mutasi kartu stok | ✅ YA |
| **Reproses HPP** | Menghitung ulang nilai perolehan persediaan dan HPP | ✅ YA |
| **Reproses Accounting** | Memastikan semua transaksi terposting ke Buku Besar (GL) | ✅ YA |

---

## 6.D. Laporan Neraca Saldo (Trial Balance)

Laporan komprehensif yang menampilkan seluruh akun beserta pergerakannya.

| Komponen | Keterangan |
|----------|-------------|
| `Saldo Awal` | Posisi keuangan awal periode |
| `Mutasi (Debet/Kredit)` | Total aktivitas selama periode |
| `Saldo Akhir` | Posisi keuangan akhir periode |

**Fitur:** Ekspor ke Excel dengan hierarki akun tetap terjaga (Level 1-5).

---

## 6.E. Real-time Posting & Drill-down

| Fitur | Keterangan |
|-------|-------------|
| **Real-time Posting** | Tidak ada tombol "Post". Setiap transaksi yang disimpan langsung memperbarui saldo GL dan laporan keuangan |
| **Drill-down Capability** | Klik nomor dokumen di laporan digital → lihat detail formulir transaksi asal |

---

# KELOMPOK 7: LAPORAN & ANALITIK MANAJERIAL

---

Menu ini merupakan pusat informasi strategis bagi pemilik bisnis dan akuntan untuk mengevaluasi performa perusahaan secara mendalam.

## 7.A. Laporan Keuangan (Financial Statements)

| Laporan | Varian |
|---------|--------|
| **Laba Rugi (Profit & Loss)** | Per Item, per Customer, per GL (Buku Besar), per Sales, Perbandingan Tahunan |
| **Neraca (Balance Sheet)** | Standar, Common Sized (Persentase), Perbandingan 2 Bulan, Perbandingan Tahunan |
| **Arus Kas (Cash Flow)** | Melacak sumber dan penggunaan dana real-time |
| **Neraca Saldo (Trial Balance)** | Ringkasan saldo seluruh akun untuk audit |

---

## 7.B. Laporan Manajerial (Business Performance)

Fokus pada indikator kunci keberhasilan bisnis.

| Laporan | Fungsi |
|---------|--------|
| **10 Top Customers** | Pelanggan dengan kontribusi pendapatan terbesar |
| **Fast & Slow Moving Items** | Daftar hingga 500 item terbaik/terburuk untuk rotasi stok optimal |
| **Analisis Laba Kotor** | Membedah margin per penjualan atau per pelanggan |
| **Laba Rugi Item / Barang (%)** | Visualisasi margin kotor per barang (persentase) → identifikasi High Margin items |
| **Laba Rugi Per Sales** | Performa profitabilitas berdasarkan tim sales (bukan hanya omzet) |
| **Laba Rugi Nota By Surat Jalan** | Analisis laba berdasarkan pengiriman barang (kritikal bagi bisnis dengan jeda waktu) |
| **Laba/Rugi Penjualan Per Item** | Margin kotor per barang + filter Kelompok Barang & Gudang |
| **Analisis Konsistensi** | Perbandingan Qty Pesanan vs Qty Kirim vs Qty Faktur (deteksi selisih pengiriman) |

---

## 7.C. Karakteristik Output

| Karakteristik | Keterangan |
|---------------|-------------|
| **Tanpa Grafik Statis** | Menu Analisa mengandalkan tabel data mendetail |
| **Visualisasi tren** | Dipusatkan di halaman **Dashboard Utama** |
| **Filter Fleksibel** | Periode (Bulan/Tahun), Cabang/Lokasi, Sorting |

---

## 7.D. Laporan Umur Piutang (AR Aging)

**Akses:** `Penjualan` > `Laporan Penjualan` > `Umur Piutang`

### Pembagian Periode (Buckets)

| Bucket | Keterangan |
|--------|-------------|
| **Belum Jatuh Tempo** | Invoice belum melewati tanggal jatuh tempo |
| **1 - 30 Hari** | Terlambat hingga 1 bulan |
| **31 - 60 Hari** | Terlambat 2 bulan |
| **61 - 90 Hari** | Terlambat 3 bulan |
| **> 90 Hari** | Piutang macet/peringatan keras |

**Dasar Perhitungan:** Selisih antara `Document Date` (atau Due Date) dengan `Cut-off Date` laporan.

---

# KELOMPOK 8: FASILITAS & KONFIGURASI SISTEM

---

Bagian ini menjelaskan bagaimana sistem NineSoft dikonfigurasi untuk menyesuaikan kebutuhan bisnis.

## 8.A. Persiapan Singkat (Profil Perusahaan)

**Akses:** Fasilitas > Persiapan Singkat

| Field | Keterangan |
|-------|-------------|
| `Nama Perusahaan` | Identitas usaha |
| `Email` | Kontak perusahaan |
| `Alamat` (Baris 1 & 2) | Alamat lengkap |
| `NPWP` | Nomor Pokok Wajib Pajak |
| `No Seri Faktur Pajak` | NSFP dari DJP |
| `No Pengukuhan PKP` | Surat Pengukuhan PKP |
| `Tgl Pengukuhan PKP` | Tanggal pengukuhan |
| `Logo` | Upload logo untuk cetakan |

---

## 8.B. Setup Sistem (Konfigurasi Global)

**Akses:** Fasilitas > Setup

### Tab General

| Field | Opsi / Keterangan |
|-------|-------------------|
| `Tanggal Mulai Program` | Batas awal sistem mencatat transaksi |
| `Format No Transaksi` | Bulanan atau Tahunan |
| `Jumlah Desimal` | 0 s/d 4 desimal |
| `Sistem Jual/Beli` | Gunakan Surat Jalan atau Langsung Faktur |
| `Maksimum Cetak` | Limitasi pengulangan cetak dokumen |

### Tab Transaksi

| Field | Keterangan |
|-------|-------------|
| `Stok Boleh Minus` | Mengizinkan penjualan meski stok habis (cocok ritel cepat) |
| `Matching PO/SO` | Validasi Qty Faktur terhadap Qty Pesanan (cegah over-delivery) |
| `Limit Hutang Piutang` | Kontrol kredit proaktif (blokir/peringatan) |
| `HPP Display` | Sembunyikan/tampilkan nilai modal di layar POS/Sales (privasi) |
| `Tampilkan Note For Today` | Pengaturan pengingat tugas harian |

### Tab Web (Integration & Output)

| Field | Keterangan |
|-------|-------------|
| `Mode Print` | Dengan Preview atau Direct Print |
| `Jenis Form` | Besar/Kecil/Thermal |
| **Bluetooth Printer Support** | Dukungan cetak struk dari browser mobile/web |
| `Sistem Pembelian` | Tanpa Barang Datang (Langsung Invoice) atau Dengan Barang Datang (PO-SJ-Inv) |

---

## 8.C. Manajemen Pengguna (Security)

### User Group

| Field | Keterangan |
|-------|-------------|
| `Nama Group` | Identitas role |
| `Deskripsi` | Penjelasan role |
| **Otoritas** | Hak akses granular (**VIEW, ADD, EDIT, DELETE**) per modul |

### Data User

| Field | Keterangan |
|-------|-------------|
| `Username` | Login ID |
| `Email` | Kontak user |
| `Password` | Secure |
| `Full Name` | Nama lengkap |
| `Jabatan` | Posisi |
| `Masa Aktif Akun` | Expiry date |
| **Izin Khusus** | Checkbox `Admin` (semua akses), `Excel` (izin export), `Portal` |

---

## 8.D. Pengaturan Menu & Dokumen

| Menu | Fungsi |
|------|--------|
| **Setting Menu** | Prefix nomor otomatis (misal: `FJ` untuk Faktur Jual), izin `Edit Tanggal`, `Auto Insert` |
| **Custom Cetakan** | Editor layout visual (Grid Designer). Placeholders: `[NAME]`, `[ADDRESS1]`, `[DOCUMENT_NO]`, `[DOCUMENT_DATE]` |
| **Cetak Barcode** | Pengaturan label barcode barang |

---

## 8.E. Pemeliharaan & Audit (Maintenance)

| Fitur | Keterangan |
|-------|-------------|
| **History Transaksi (Audit Log)** | `Tanggal`, `Menu`, `Tipe Aksi` (ADD/EDIT/DELETE), `No Dokumen`, `Username` |
| **Database Tools** | `Restore Database`, `Database Patching` |

---

## 8.F. Manajemen Pajak (Tax Management)

### Konfigurasi Master Pajak

**Akses:** `Master` > `Customer & Supplier` > `Pajak`

| Fitur | Keterangan |
|-------|-------------|
| **Tipe Pajak** | PPN (11%/12%), PPN INCL (Termasuk Pajak), Non-Pajak |
| **Mapping Akun** | Setiap kode pajak dipetakan ke Akun PPN Masukan (Pembelian) dan Akun PPN Keluaran (Penjualan) |
| **Metode Perhitungan** | PPN (Exclusive) dan INCL (Inclusive) per transaksi |
| **Ekspor PPN Masukan** | Ekspor data pajak ke format kompatibel **e-Faktur DJP** |

---

# KELOMPOK 9: POLA UI, AKSI, DAN FITUR EKSPOR

---

NineSoft memiliki pola antarmuka yang konsisten di seluruh modul.

## 9.A. Fitur pada Daftar (List View)

| Aksi | Fungsi |
|------|--------|
| **Tombol AKSI (Kaca Pembesar)** | Buka formulir detail (edit atau lihat) |
| **Icon Plus Hijau (+)** | "Expander" → lihat detail baris tanpa pindah halaman (stok per gudang, rincian jurnal) |
| **Import Excel** | Tombol pojok kanan atas untuk unggah data massal |
| **Accordion History** | Log perubahan data langsung di bawah filter |

---

## 9.B. Fitur pada Formulir Transaksi (Input Form)

### Toolbar Utama

| Tombol | Fungsi |
|--------|--------|
| `Simpan` | Menyimpan data |
| `Undo` | Membatalkan perubahan |
| `Hapus` | Menghapus transaksi (tergantung hak akses) |
| `Cetak (Dropdown)` | A4, Half Letter, Surat Jalan, dll |
| `Excel` | Ekspor data transaksi ke Excel |
| `Info` | Pop-up audit log (pembuat, pengubah terakhir) |

### Aksi pada Tabel Detail (Grid)

| Aksi | Fungsi |
|------|--------|
| `Browse Item` | Katalog pencarian barang |
| `Pilih Ref (SO/SJ/PO)` | Menarik data dari dokumen referensi |
| `Delete All` | Menghapus seluruh baris detail |
| `Quick Add (+)` | Icon plus biru untuk tambah data master baru tanpa keluar form |

---

## 9.C. Fitur pada Laporan (Reporting)

| Opsi Output | Fungsi |
|-------------|--------|
| `Preview` | Tampilkan di layar browser |
| `Export Excel` | Unduh `.xlsx` dengan tata letak rapi |
| `Cetak` | Kirim ke printer |
| `Kirim Email` | Lampiran email langsung dari sistem |
| `Layout Control` | Portrait atau Landscape |

---

## 9.D. Fitur Impor Data Massal

| Data yang Mendukung Impor | Template |
|---------------------------|----------|
| Master Barang & Jasa | `IMPORT_WEB_ITEM.csv` |
| Master Customer & Supplier | `customer.xlsx` |
| Faktur Pembelian & Penjualan | `beli.xlsx` / `jual_faktur_sj.xlsx` |
| Transaksi Jurnal (GL) | `IMPORT_WEB_GL.csv` |

**Mekanisme:** Setiap pop-up impor menyediakan tombol **"Download Template"** (kuning) untuk memastikan struktur kolom sesuai.

---

## 9.E. Browse Popup (Pencarian & Pemilihan)

| Fitur | Keterangan |
|-------|-------------|
| **Multi-Criteria Search** | Kolom filter di setiap header tabel. Filter berdasarkan berbagai kolom bersamaan (Kode + Kelompok) |
| **Batch Selection** | Checkbox per baris + 'Check All' → pilih banyak barang sekaligus |
| **In-Form Creation** | Tombol `+` di popup → tambah data master (customer, barang) tanpa menutup form transaksi |

---

# KELOMPOK 10: FITUR LANJUTAN & BATASAN SISTEM

---

## 10.A. Fitur Lanjutan

| Fitur | Keterangan |
|-------|-------------|
| **Pengelolaan Pajak** | PPN Exclusive/INCL, Multiple rates, e-Faktur DJP export |
| **Real-time Posting** | Tidak ada tombol "Post". Simpan langsung update GL |
| **Audit Trail** | Tombol `Info` di setiap formulir |
| **Kontrol Periode** | Closing periode untuk mengunci bulan-bulan sebelumnya |
| **Multi-Cabang** | Pemisahan operasional melalui entitas `Gudang` dan `Lokasi` |
| **Bluetooth Printing** | Dukungan printer thermal untuk POS mobile |

---

## 10.B. Batasan Sistem (Varian Demo)

| Batasan | Keterangan |
|---------|-------------|
| **Multi-Currency** | ❌ Hanya mendukung **IDR**. Tidak ada mata uang asing atau selisih kurs |
| **Payroll & HR** | ❌ Tidak ada modul penggajian, absensi, database karyawan |
| **Project/Department** | ❌ Tidak ada tracking biaya/pendapatan per Proyek atau Departemen |
| **Produksi Kompleks** | ❌ Hanya perakitan sederhana (Assembly). Tidak ada Work Order (WO), MRP, atau biaya overhead |
| **Rekonsiliasi Bank** | ❌ Tidak ada pencocokan otomatis dengan rekening koran |

---

# KELOMPOK 11: KATALOG EKSPOR & DAFTAR FIELD

---

## 11.A. Ekspor Data Master

### Data Barang & Jasa

| Field | Field | Field |
|-------|-------|-------|
| `KODE` | `KEMASAN` | `BARCODE` |
| `NAMA` | `RAKITAN` | `SATUAN` |
| `SATUAN2` | `SATUAN3` | `KONV2` |
| `KONV3` | `KELOMPOK` | `JENIS_BARANG` |
| `TIPE` | `MERK` | `SUPPLIER_KODE` |
| `KETERANGAN` | `AKTIF` | |

### Data Customer

| Field | Field | Field |
|-------|-------|-------|
| `KODE CUSTOMER` | `NAMA CUSTOMER` | `JANGKA WAKTU` |
| `BATAS PIUTANG` | `RAYON` | `AKTIF` |

### Data Supplier

| Field | Field | Field |
|-------|-------|-------|
| `KODE SUPPLIER` | `NAMA SUPPLIER` | `JANGKA WAKTU` |
| `RAYON` | `AKTIF` | |

---

## 11.B. Ekspor Daftar Transaksi

### Daftar Penjualan

| Field | Field | Field |
|-------|-------|-------|
| `STATUS` | `TANGGAL` | `NOMOR` |
| `NO REFERENSI` | `CUSTOMER` | `TOTAL` |
| `USER` | | |

### Daftar Pembelian

| Field | Field | Field |
|-------|-------|-------|
| `STATUS` | `TANGGAL` | `NOMOR` |
| `NO REFERENSI` | `SUPPLIER` | `TOTAL` |
| `USER` | | |

### Daftar Jurnal (GL)

| Field | Field | Field |
|-------|-------|-------|
| `TANGGAL` | `TRANSAKSI` | `NO DOKUMEN` |
| `ITEM` | `KETERANGAN` | `NO REFERENSI` |
| `ACCOUNT` | `DEBET` | `KREDIT` |

---

## 11.C. Ekspor Laporan Keuangan

### Neraca Saldo

| Field | Field | Field |
|-------|-------|-------|
| `NO` | `KODE AKUN` | `NAMA ACCOUNT` |
| `SALDO AWAL` | `MUTASI DEBET` | `MUTASI KREDIT` |
| `SALDO AKHIR` | | |

### Laba Rugi Standard

| Field | Field |
|-------|-------|
| `KODE AKUN` | `NAMA AKUN` |
| `NILAI` (Nominal per akun pendapatan/biaya) | |

### Neraca Standard

| Field | Field |
|-------|-------|
| `KODE AKUN` | `NAMA AKUN` |
| `NILAI` (Nominal per akun harta/hutang/modal) | |

---

# KELOMPOK 12: WHATSAPP & NOTIFIKASI

---

Sistem NineSoft terintegrasi dengan layanan pengiriman pesan instan.

## 12.A. Log Pesan (Outbound Log)

| Field | Keterangan |
|-------|-------------|
| `User Email` | Identitas pengirim (staf) |
| `Phone` | Nomor WhatsApp tujuan (pelanggan/supplier) |
| `Message` | Isi pesan yang dikirimkan |
| `Status` | `Sent` / `Pending` |
| `Time` | Waktu presisi pengiriman |

## 12.B. Jenis Notifikasi Otomatis

| Jenis | Trigger |
|-------|---------|
| **Transaksi** | PO/SO baru, konfirmasi pengiriman barang (SJ) |
| **Keuangan** | Pengingat saldo piutang (H-N atau H+N jatuh tempo) |
| **CRM** | Pesan apresiasi setelah pelunasan piutang |

---

# KELOMPOK 13: ALUR PROSES BISNIS END-TO-END

---

## Tahap 1: Persiapan & Input Data Master

| Langkah | Aktivitas |
|---------|-----------|
| 1 | **Setup Awal**: Profil perusahaan dan sistem (Fasilitas > Setup) |
| 2 | **Chart of Accounts (COA)**: Susun daftar akun dan mapping akun otomatis (Setting COA) |
| 3 | **Data Induk**: Input/impor Barang, Supplier, Customer via Excel |
| 4 | **Saldo Awal**: Input Saldo Awal Akun (Neraca) dan Saldo Awal Stok per gudang |

---

## Tahap 2: Siklus Operasional Harian

### Siklus Pembelian

| Langkah | Dokumen | Efek |
|---------|---------|------|
| 1 | **Order Pembelian (PO)** ke supplier | Belum ada efek stok & GL |
| 2 | **Faktur Pembelian** | Stok bertambah, Hutang bertambah |
| 3 | **Pelunasan Hutang** via Kas/Bank | Hutang berkurang, Kas berkurang |

### Siklus Penjualan

| Langkah | Dokumen | Efek |
|---------|---------|------|
| 1 | **Sales Order (SO)** | Belum ada efek stok & GL |
| 2 | **Surat Jalan (SJ)** | Stok fisik berkurang |
| 3 | **Faktur Penjualan** | Piutang bertambah, Pendapatan bertambah |
| 4 | **Pelunasan Piutang** via Kas/Bank | Kas bertambah, Piutang berkurang |

### Kas/Bank & Memorial

| Dokumen | Fungsi |
|---------|--------|
| **Bukti Kas Keluar** | Biaya operasional (Gaji, Listrik, Sewa) |
| **Transaksi Jurnal (GL)** | Penyesuaian non-kas (penyusutan, alokasi) |

---

## Tahap 3: Prosedur Akhir Bulan (Period-End)

**WAJIB** dijalankan sebelum closing:

| Urutan | Reproses | Fungsi |
|--------|----------|--------|
| 1 | **ReProses Stok** | Hitung ulang saldo stok fisik seluruh gudang |
| 2 | **ReProses HPP** | Hitung ulang HPP (FIFO/Average) |
| 3 | **ReProses Accounting** | Sinkronisasi seluruh transaksi ke GL |

---

## Tahap 4: Penutupan Buku (Closing)

| Langkah | Aktivitas |
|---------|-----------|
| 1 | Buka `GL > Closing Periode Akuntansi` |
| 2 | Pilih bulan dan tahun |
| 3 | Klik **Closing** |

### Efek Closing

| Perubahan | Keterangan |
|-----------|-------------|
| Transaksi terkunci | Tidak bisa edit/hapus |
| Saldo Laba/Rugi | Dipindahkan ke akun Laba Ditahan (Retained Earnings) |
| Saldo akhir | Menjadi saldo awal bulan berikutnya |

---

# KELOMPOK 14: HASIL SCRAPING - MODUL MASTER

---

Bagian ini dihasilkan oleh skrip otomatis (`catatan_ninesoft_scraper.py`) berisi ekstraksi relasi tabel, fields panel, dan pola validasi di area Master Data NineSoft.

---

## 📁 Menu: Master

**Endpoint GUI:** `https://web.ninesoft.id/site/master/info`

### Konstruksi Tabel Master

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `KODE ITEM` | `NAMA ITEM` | `SATUAN` |
| `HARGA BELI TERAKHIR` | `HARGA JUAL TERAKHIR` | `HARGA BELI` |
| `HARGA JUAL` | `AKTIF` | `AKSI` |

### Aksi / Fitur Operasional

`New` | `Filter` | `Reset` | `Import` | `Filter Data`

---

## 📁 Menu: Barang dan Jasa

**Endpoint GUI:** `https://web.ninesoft.id/site/master/item`

### Struktur Tab Halaman Add/Detail

#### Tab Detail

| Field | Field | Field |
|-------|-------|-------|
| `item_code` | `item_name` | `item_kms` |
| `item_description` | `barcode` | `uom_code` |
| `kelompok` | `lokasi` | `min_stok` |
| `note` | `jenis` | `tipe_item` |
| `merek` | `made_in` | `grade` |
| `gambar` | `hpp_awal` | `rakitan` |
| `status` | `max_stock` | `max_order` |
| `to_uom` | `to_qty` | `uom_flag` |

#### Tab Account

| Field | Field | Field |
|-------|-------|-------|
| `acc_jual` | `acc_jual_code` | `acc_inventory` |
| `acc_inventory_code` | `acc_disc` | `acc_disc_code` |
| `acc_hpp` | `acc_hpp_code` | `acc_retur_jual` |
| `acc_retur_jual_code` | `acc_retur_beli` | `acc_retur_beli_code` |
| `acc_hutang_suspend` | `acc_hutang_suspend_code` | |

#### Tab Harga

| Field | Field | Field |
|-------|-------|-------|
| `HARGA 1` | `HARGA 2` | `HARGA 3` |
| `HARGA 4` | `Rp` | `%` |

#### Tab Diskon

| Field | Field |
|-------|-------|
| `DISKON %` | `DISKON Rp` |

#### Tab Saldo Awal Program

| Field | Field |
|-------|-------|
| `GUDANG` | `STOK AWAL` |
| `JUMLAH` | |

---

## 📁 Menu: Customer

**Endpoint GUI:** `https://web.ninesoft.id/site/master/customer`

### Konstruksi Tabel Master

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `KODE CUSTOMER` | `NAMA CUSTOMER` | `JANGKA WAKTU` |
| `BATAS PIUTANG` | `RAYON` | `AKSI` |

### Struktur Tab Halaman Add/Detail

#### Tab Detail

| Field | Field | Field |
|-------|-------|-------|
| `person_code` | `person_name` | `termin` |
| `term_number` | `jtempo` | `limit_piutang` |
| `rayon` | `no_npwp` | `discount_percen_head` |
| `discount_price_head` | `email` | `note` |
| `karyawan_id` | `coa_piut_code` | `coa_piut_name` |
| `tipe_harga_jual` | `check_supp` | `active_flag` |

#### Tab Address / Contact

Multi-address management untuk alamat pusat, tagihan, dan pengiriman.

---

## 📁 Menu: Supplier

**Endpoint GUI:** `https://web.ninesoft.id/site/master/supplier`

### Konstruksi Tabel Master

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `KODE SUPPLIER` | `NAMA SUPPLIER` | `JANGKA WAKTU` |
| `BATAS HUTANG` | `AKSI` | |

### Struktur Tab Halaman Add/Detail

| Field | Field | Field |
|-------|-------|-------|
| `person_code` | `person_name` | `termin` |
| `term_number` | `limit_hutang` | `no_npwp` |
| `discount_percen_head` | `discount_price_head` | `email` |
| `note` | `coa_hut_code` | `coa_hut_name` |
| `check_cus` | `active_flag` | |

---

## 📁 Menu: Formula (BOM)

**Endpoint GUI:** `https://web.ninesoft.id/site/master/formula`

### Konstruksi Tabel Master

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `NO FORMULA` | `NAMA ITEM` | `KODE ITEM` |
| `SATUAN` | `BERLAKU MULAI` | `BERLAKU SAMPAI` |
| `AKTIF` | `AKSI` | |

### Kamus Field Form (Add Data)

| Field | Field | Field |
|-------|-------|-------|
| `document_no` | `item_description_head` | `item_code_head` |
| `uom_code_head` | `start_date` | `end_date` |
| `document_reff_no` | `active_flag` | `keterangan` |

---

## 📁 Menu: Account

**Endpoint GUI:** `https://web.ninesoft.id/site/master/account`

### Konstruksi Tabel Master

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `KODE ACC` | `NAMA ACCOUNT` | `TIPE ACCOUNT` |
| `AKTIF` | `AKSI` | |

### Aksi / Fitur Operasional

`Add Record` | `Create Coa`

---

## 📁 Menu: Asset (Aktiva Tetap)

**Endpoint GUI:** `https://web.ninesoft.id/site/master/asset`

### Konstruksi Tabel Master

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `NAMA ASSET` | `KODE ASSET` | `JML` |
| `NILAI` | `UMUR` | `METODE DEPRESIASI` |
| `AKTIF` | `AKSI` | |

### Struktur Tab Halaman Add/Detail

#### Tab Asset

| Field | Keterangan |
|-------|-------------|
| `asset_code` | Kode unik aset |
| `asset_name` | Nama aset |
| `entered_qty` | Jumlah unit |
| `nilai_asset` | Harga perolehan |
| `susut_year` | Tahun penyusutan |
| `umur_asset` | Masa manfaat (tahun) |
| `rate` | Rate depresiasi |
| `note` | Keterangan |
| `active_flag` | Status aktif |

#### Tab Account

| Field | Keterangan |
|-------|-------------|
| `coa_asset` | Akun aset (Neraca) |
| `coa_debet` | Akun biaya penyusutan (Laba/Rugi) |
| `coa_kredit` | Akun akumulasi penyusutan |
| `metode_depresiasi` | Metode penyusutan |

---

## 📁 Menu: User Group

**Endpoint GUI:** `https://web.ninesoft.id/site/master/user`

### Konstruksi Tabel Master

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `NAMA USER` | `EMAIL` | `PASSWORD` |
| `AKUN UTAMA` | `REPORT` | `KETERANGAN` |
| `ADMIN` | `TEMPLATE` | `EXCEL` |

### Otoritas (Per Modul)

| Modul | Hak Akses |
|-------|-----------|
| `MASTER` | VIEW, ADD, EDIT, DELETE |
| `INVENTORY` | VIEW, ADD, EDIT, DELETE |
| `PEMBELIAN` | VIEW, ADD, EDIT, DELETE |
| `PENJUALAN` | VIEW, ADD, EDIT, DELETE |
| `KAS & BANK` | VIEW, ADD, EDIT, DELETE |
| `GL` | VIEW, ADD, EDIT, DELETE |
| `ANALISA` | VIEW |
| `FASILITAS` | VIEW |

### Aksi / Fitur Operasional

`Tambah Group` | `Buat User` | `Simpan` | `Hapus` | `Select` | `Unselect`

---

## 📁 Menu: Daftar Harga

**Endpoint GUI:** `https://web.ninesoft.id/site/master/harga`

### Konstruksi Tabel Master

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `KODE ITEM` | `NAMA ITEM` | `SATUAN` |
| `BELI` | `HARGA 1` | `DISKON` |
| `KELOMPOK` | `AKSI` | |

### Level Harga (Harga 1 s/d 10)

Mendukung hingga **10 level harga** untuk klasifikasi pelanggan (Retail, Grosir, Distributor, dll).

---

## 📁 Menu: Setting COA (Mapping Akun Otomatis)

**Endpoint GUI:** `https://web.ninesoft.id/site/master/coa`

### Konstruksi Tabel Master

| Kolom | Kolom |
|-------|-------|
| `KETERANGAN` | `ACCOUNT` |
| `AKSI` | |

Memetakan akun GL ke aktivitas transaksi (Penjualan, HPP, Persediaan, PPN, Piutang, dll). Setiap transaksi operasional langsung menjurnal ke buku besar tanpa input manual.

---

# KELOMPOK 15: HASIL SCRAPING - MODUL TRANSAKSIONAL

---

Bagian ini dihasilkan oleh skrip otomatis (`catatan_ninesoft_transaksi.py`) berisi ekstraksi form operasional harian (Purchasing, Penjualan, Inventory, Finance Kas/Bank).

---

## 💼 Modul: Inventory

**Endpoint GUI:** `https://web.ninesoft.id/site/inventory/info`

### Konstruksi Tabel (Grids)

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `Kode Item` | `Nama Item` | `Stok` |
| `Satuan` | `HPP` | `Nilai` |
| `Analisa Jual` | `Order` | `Sisa` |

### Fitur Modul

`Refresh`

---

## 💼 Modul: Pembelian

**Endpoint GUI:** `https://web.ninesoft.id/site/purchasing/info`

### Konstruksi Tabel (Grids)

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `TANGGAL` | `NO PO` | `NO REFF` |
| `STATUS PO` | `DIBUTUHKAN` | `SUPPLIER` |
| `GUDANG` | `TOTAL` | |

### Fitur Modul

`Refresh` | `Add Supplier`

---

## 💼 Modul: Sales Order (SO)

**Endpoint GUI:** `https://web.ninesoft.id/site/sales/order`

### Konstruksi Tabel (Grids)

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `STATUS` | `NO SO` | `NO REFF` |
| `TANGGAL` | `DIBUTUHKAN` | `CUSTOMER` |
| `SALES` | `TOTAL` | `AKSI` |

### Fitur Modul

`New` | `Filter` | `Reset` | `Import` | `Save` | `Cancel`

---

## 💼 Modul: Surat Jalan (SJ)

**Endpoint GUI:** `https://web.ninesoft.id/site/sales/delivery`

### Konstruksi Tabel (Grids)

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `STATUS` | `NO SJ` | `NO REFF` |
| `TANGGAL` | `CUSTOMER` | `NO SO` |
| `AKSI` | | |

### Fitur Modul

`New` | `Filter` | `Reset` | `Import` | `Save` | `Cancel`

---

## 💼 Modul: Faktur Penjualan

**Endpoint GUI:** `https://web.ninesoft.id/site/sales/faktur`

### Konstruksi Tabel (Grids)

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `STATUS` | `NO FAKTUR` | `NO REFF` |
| `TANGGAL` | `CUSTOMER` | `SALES` |
| `TOTAL` | `AKSI` | |

### Struktur Tab Transaksi

| Tab | Fungsi |
|-----|--------|
| **Tab Detail** | Input item, referensi SO/SJ |
| **Tab Uang Muka** | Kompensasi deposit |
| **Tab Pelunasan** | Pembayaran langsung |
| **Tab Info SO-SJ-INVOICE** | Sinkronisasi kuantitas |
| **Tab Stok** | Informasi stok item |
| **Tab Konversi** | Unit conversion |
| **Tab Harga** | Detail harga |

---

## 💼 Modul: Penjualan Barcode (POS)

**Endpoint GUI:** `https://web.ninesoft.id/site/sales/barcode`

### Fitur Utama

- Desain minimalis dengan input terpusat di field **`INPUT BARCODE`**
- Setiap scan barcode otomatis menambah baris dan kuantitas item
- Mendukung cetak struk thermal

---

## 💼 Modul: Faktur Retur Jual

**Endpoint GUI:** `https://web.ninesoft.id/site/sales/retur`

### Konstruksi Tabel (Grids)

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `STATUS` | `NO RETUR` | `NO REFF` |
| `TANGGAL` | `CUSTOMER` | `SALES` |
| `TOTAL` | `AKSI` | |

### Struktur Tab Transaksi

| Tab | Fungsi |
|-----|--------|
| **Tab Detail** | Referensi faktur asal |
| **Tab Pelunasan** | Pengembalian dana |
| **Tab Stok** | Penambahan stok |
| **Tab Konversi** | Unit conversion |
| **Tab Harga** | Detail harga |

---

## 💼 Modul: Kas & Bank

**Endpoint GUI:** `https://web.ninesoft.id/site/finance/info`

### Konstruksi Tabel (Grids)

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `KODE ACC` | `NAMA KAS BANK` | `SALDO AWAL` |
| `DEBET` | `KREDIT` | `SALDO` |

### Fitur Modul

`Refresh`

---

## 💼 Modul: Bukti Kas Masuk (BKM)

**Endpoint GUI:** `https://web.ninesoft.id/site/finance/Bkm`

### Komponen Header

| Field | Keterangan |
|-------|-------------|
| `No Voucher` | Auto-generated |
| `Tanggal` | Tanggal transaksi |
| `Akun Kas/Bank` | Tujuan dana |
| `Dari` | Customer / Pihak Lain |
| `No Cek/Giro` | Untuk non-tunai |
| `Tanggal Jatuh Tempo` | Untuk cek/giro |

### Detail Tab

| Tab | Fungsi |
|-----|--------|
| **Tab Detail (Piutang)** | `Browse Nota` untuk pilih faktur yang dilunasi |
| **Tab Lain-lain** | Pendapatan non-operasional (Bunga Bank, Penjualan Aset) |
| **Tab Hutang** | Pengembalian dana dari supplier |

---

## 💼 Modul: Bukti Kas Keluar (BKK)

**Endpoint GUI:** `https://web.ninesoft.id/site/finance/Bkk`

### Alur Operasional

| Jenis | Cara |
|-------|------|
| **Pelunasan Hutang** | `Browse Nota` → pilih faktur pembelian |
| **Biaya Operasional** | Tab `Lain-lain` → pilih akun biaya |
| **Biaya Transfer** | Tombol cepat |
| **Tolakan Giro** | Status sistematis |

---

## 💼 Modul: Laporan Keuangan

### Neraca Standard

**Endpoint:** `https://web.ninesoft.id/site/report/analysis/LprnNrcStndrd/`

### Laba Rugi Standard

**Endpoint:** `https://web.ninesoft.id/site/report/analysis/LbRgGl/`

### Parameter Filter

| Parameter | Format |
|-----------|--------|
| `p[12][1]` | Periode (`YYYYMM`, contoh: `202603`) |
| `p[13][1]` | Lokasi/Gudang (ID internal) |
| `submitForm` | `preview` |
| `printer` | `portrait` / `landscape` |

---

## 💼 Modul: Laporan Piutang & Kartu Piutang

### Kartu Piutang

**Endpoint:** `https://web.ninesoft.id/site/sales/info/kartu`

### Konstruksi Tabel

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `Tanggal` | `No Transaksi` | `Transaksi` |
| `Debet` | `Kredit` | `Saldo Akhir` |
| `Keterangan` | | |

### Fitur Modul

`Refresh` | `Add Customer`

---

## 💼 Modul: Summary Piutang

**Endpoint:** `https://web.ninesoft.id/site/sales/info/sum_piutang`

### Konstruksi Tabel

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `Supplier` | `Saldo Awal` | `Debet` |
| `Kredit` | `Saldo Akhir` | |

### Fitur Modul

`Refresh`

---

## 💼 Modul: Pelunasan Piutang

**Endpoint:** `https://web.ninesoft.id/site/sales/info/piutang`

### Konstruksi Tabel

| Kolom | Kolom | Kolom |
|-------|-------|-------|
| `TANGGAL TERIMA` | `NO VOUCER` | `NO FAKTUR` |
| `BANK` | `CUSTOMER` | `NILAI` |
| `POTONGAN` | `NILAI FAKTUR` | `STATUS` |

---

**Akhir Dokumen**

---

*Dokumen ini merupakan kumpulan seluruh fitur NineSoft Accounting Online yang telah diobservasi, dikelompokkan secara terstruktur untuk referensi pengembangan StoreSoft. Tidak ada informasi yang dihapus atau diubah.*
