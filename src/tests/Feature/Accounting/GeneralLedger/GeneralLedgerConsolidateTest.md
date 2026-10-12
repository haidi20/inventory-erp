# Dokumentasi Testing: GeneralLedgerConsolidateTest

## Deskripsi
Memverifikasi kemampuan sistem dalam menghasilkan laporan keuangan (Neraca) yang dikonsolidasikan dari berbagai cabang.

## Daftar Method Test & Penjelasan Detail

### 1. `test_consolidated_balance_sheet_v2` [✅ **PASS**]
- **Deskripsi**: Menjumlahkan saldo akun dari Cabang A dan Cabang B ke dalam satu laporan tunggal.
- **Logika**: 
    1. Membuat transaksi penjualan di Cabang 1 (Rp 500.000).
    2. Membuat transaksi penjualan di Cabang 2 (Rp 300.000).
    3. Memanggil `getBalanceSheet()` dengan parameter `branch_id = null` (konsolidasi).
- **Verifikasi**: Assert saldo Kas di laporan konsolidasi adalah 800.000 (belum termasuk saldo awal).

---
*Dihasilkan secara otomatis oleh Antigravity.*
