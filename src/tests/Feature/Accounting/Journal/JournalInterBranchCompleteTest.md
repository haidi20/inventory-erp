# Dokumentasi Testing: JournalInterBranchCompleteTest

## Deskripsi
Memverifikasi mekanisme transaksi antar cabang (Inter-branch), akun eliminasi/clearing, dan konsolidasi laporan keuangan lintas entitas.

## Daftar Method Test & Penjelasan Detail

### 1. `test_stock_transfer_and_elimination` [✅ **PASS**]
- **Deskripsi**: Menguji perpindahan stok antar cabang dan otomatisasi eliminasi saldo clearing.
- **Logika**: 
    1. Mencatat pengiriman barang dari Head Office (HO) ke Cabang A.
    2. Menggunakan akun clearing (Inter-branch Clearing) di kedua sisi.
- **Verifikasi**: Assert saldo akun clearing secara konsolidasi (HO + BRA) harus bernilai Nol (0) karena sudah saling mengeliminasi.

### 2. `test_cost_allocation_and_consolidation` [✅ **PASS**]
- **Deskripsi**: Alokasi biaya dari pusat ke cabang dan dampaknya pada Laporan Laba Rugi konsolidasi.
- **Logika**: HO membayar biaya operasional yang seharusnya dibebankan ke Cabang A.
- **Verifikasi**: Assert Laporan Laba Rugi konsolidasi menampilkan total Pendapatan dan Biaya dari seluruh cabang secara akurat setelah alokasi.

---
*Dihasilkan secara otomatis oleh Antigravity.*
