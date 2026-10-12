# Dokumentasi Testing: JournalSimulationTest

## Deskripsi
Simulasi skenario bisnis nyata yang sangat kompleks, mencakup otomatisasi jurnal dari seluruh modul operasional klinik (POS, Laboratorium, Farmasi, Keuangan).

## Daftar Method Test & Penjelasan Detail

### 1. `test_penjualan_obat_full_simulation_dynamic_looping` [✅ **PASS**]
- **Deskripsi**: Integrasi otomatis Penjualan Kasir (POS) ke Akuntansi.
- **Logika**: Mengambil data transaksi `Sale` asli, memetakan 5 akun sekaligus (Kas, PPN, Pendapatan, HPP, Persediaan).
- **Verifikasi**: Assert kelima baris jurnal terbentuk dengan nominal absolut yang akurat.

### 2. `test_pemeriksaan_rekam_medis_simulation_dynamic` [✅ **PASS**]
- **Deskripsi**: Jurnal otomatis dari layanan Medis (Dokter + Obat).
- **Logika**: Simulasi pasien yang membayar Jasa Dokter dan Resep Obat dalam satu tagihan.
- **Verifikasi**: Assert pemisahan pendapatan jasa dan pendapatan produk ke akun yang berbeda sesuai Mapping.

### 3. `test_pembelian_obat_simulation_dynamic` [✅ **PASS**]
- **Deskripsi**: Pencatatan otomatis penambahan aset stok saat beli ke Supplier.
- **Verifikasi**: Assert kenaikan nilai persediaan di Buku Besar.

### 4. `test_atomic_double_entry_rollback_on_failure` [✅ **PASS**]
- **Deskripsi**: Uji Integritas (ACID). Jika baris kedua jurnal gagal, baris kesatu harus dibatalkan (Rollback).
- **Verifikasi**: Assert tidak ada data "setengah jadi" di database jika terjadi error di tengah proses.

### 5. `test_branch_isolation_cross_check` [✅ **PASS**]
- **Deskripsi**: Proteksi keamanan agar data finansial cabang tidak bocor/diubah oleh cabang lain.
- **Verifikasi**: Assert sistem menolak posting lintas cabang dengan `UnauthorizedException`.

---
*Dihasilkan secara otomatis oleh Antigravity.*
