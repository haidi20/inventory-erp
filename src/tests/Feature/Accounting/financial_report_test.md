# Dokumentasi Pengujian FinancialReportTest

File ini mendokumentasikan pengujian fungsional untuk laporan keuangan utama: Neraca (Balance Sheet) dan Laporan Laba Rugi (Income Statement). Pengujian ini memastikan bahwa data dari saldo akun (account balances) dikonversi menjadi laporan yang akurat dan seimbang.

---

## Struktur Kelas

- **`FinancialReportTest`** — Kelas test untuk validasi output laporan keuangan.
- Menggunakan trait `RefreshDatabase`.
- Menggunakan `FinancialReportService` untuk menghasilkan data laporan.

---

## Daftar Skenario Pengujian

### 1. `test_balance_sheet_generation_and_balancing` (Neraca)
- **Tujuan:** Memvalidasi bahwa laporan Neraca menunjukkan posisi Saldo Aset = Kewajiban + Ekuitas (Accounting Equation).
- **Proses:**
    - Membuat data akun Aset (Debit), Kewajiban (Credit), dan Ekuitas (Credit) untuk periode Maret 2026.
    - Menginput saldo penutup masing-masing akun.
    - Memanggil `getBalanceSheet()`.
- **Ekspektasi:** 
    - Total Aset harus sesuai input.
    - Total Kewajiban dan Ekuitas harus sesuai input.
    - Atribut `is_balanced` harus bernilai `true`.

### 2. `test_income_statement_generation` (Laba Rugi)
- **Tujuan:** Memastikan perhitungan Laba Bersih (Net Income) dari Pendapatan dikurangi Beban adalah benar.
- **Proses:**
    - Membuat akun Pendapatan (Revenue) dan Beban (Expense).
    - Menginput saldo penutup masing-masing akun untuk periode Maret 2026.
    - Memanggil `getIncomeStatement()`.
- **Ekspektasi:**
    - Total Revenue dan Expense harus sesuai input.
    - `net_income` harus merupakan selisih antara Revenue dan Expense.

---

## Ringkasan Cakupan

| Jenis Laporan | Komponen Diuji | Validasi Utama |
|---|---|---|
| Neraca | Aset, Hutang, Modal | Keseimbangan (Balanced) |
| Laba Rugi | Pendapatan, Beban | Akurasi Laba Bersih |

**Total: 2 test cases, assertions pada total saldo dan logika balancing.**

## Cara Menjalankan
```bash
php artisan test tests/Feature/Accounting/FinancialReportTest.php
```
