# Dokumentasi Pengujian AdvancedLedgerScenarioTest

File ini mendokumentasikan skenario pengujian tingkat lanjut (Advanced) untuk Buku Besar (General Ledger). Pengujian ini mencakup aspek-aspek kritis seperti akumulasi saldo antar periode, mekanisme pembatalan (storno), penguncian periode, dan logika reset akhir tahun.

---

## Struktur Kelas

- **`AdvancedLedgerScenarioTest`** — Kelas test yang fokus pada integritas data lintas waktu dan status.
- Menggunakan trait `RefreshDatabase`.
- Mendefinisikan akun Kas Audit (`1101.01`) dan Pendapatan Audit (`4101.01`) untuk pengujian yang terisolasi.

---

## Daftar Skenario Pengujian

### 1. `test_opening_balance_time_travel` ("Time-Travel" Saldo Awal)
- **Tujuan:** Memastikan saldo akhir periode sebelumnya terbawa dengan benar ke saldo awal periode berikutnya.
- **Proses:**
    - Membuat periode Jan, Feb, dan Maret 2026.
    - Menginput transaksi di Jan (Debit 10jt) dan Feb (Kredit 2jt).
    - Melakukan sinkronisasi Buku Besar untuk Jan dan Feb.
    - Menarik laporan Buku Besar untuk bulan Maret.
- **Ekspektasi:** Saldo awal Maret harus menunjukkan Rp 8.000.000 (10jt - 2jt) meskipun tidak ada transaksi di bulan Maret.

### 2. `test_storno_immutability` ("The Storno Effect")
- **Tujuan:** Memverifikasi bahwa pembatalan transaksi (void) tetap meninggalkan jejak audit (audit trail) dan menyeimbangkan saldo kembali ke nol.
- **Proses:**
    - Membuat entri jurnal salah (Debit 5jt).
    - Melakukan `voidJournal()` pada transaksi tersebut.
    - Menjalankan sinkronisasi Buku Besar.
- **Ekspektasi:** Harus ada 2 baris transaksi di Buku Besar (Entri Orisinil + Entri Storno/Pembalik). Saldo akhir setelah storno harus kembali ke 0.

### 3. `test_period_locking_steel_door` ("The Steel Door")
- **Tujuan:** Memastikan perlindungan data pada periode yang sudah ditutup (closed).
- **Proses:**
    - Membuat periode Januari dan mengubah statusnya menjadi `closed`.
    - Mencoba membuat jurnal baru pada tanggal di bulan Januari.
- **Ekspektasi:** Sistem harus melempar `Exception` dengan pesan bahwa periode sudah ditutup.

### 4. `test_year_end_reset_logic` ("Year-End Reset")
- **Tujuan:** Memvalidasi perbedaan perlakuan antara akun Neraca (Balance Sheet) dan Laba Rugi (P&L) saat pergantian tahun fisik.
- **Proses:**
    - Menginput transaksi Revenue dan Asset pada 31 Des 2025.
    - Memeriksa laporan Buku Besar pada 1 Jan 2026.
- **Ekspektasi:**
    - Akun Asset (Kas) harus *carry forward* saldo (tetap ada saldo awal 5jt).
    - Akun Revenue harus *reset* ke 0 (tutup buku tahunan).

---

## Ringkasan Cakupan

| Skenario | Kategori | Fitur yang Diuji |
|---|---|---|
| Time Travel | Akumulasi | Carry-over saldo antar bulan |
| Storno | Audit Trail | Immutability & Reversal (Pembalik) |
| Steel Door | Keamanan | Period Locking (Penguncian Periode) |
| Year-End | Integritas | Reset Saldo P&L vs Asset |

**Total: 4 test cases, assertions pada saldo dan integritas data.**

## Cara Menjalankan
```bash
php artisan test tests/Feature/Accounting/AdvancedLedgerScenarioTest.php
```
