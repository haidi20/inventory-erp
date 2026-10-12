# Dokumentasi Testing: GeneralLedgerYearEndTest

## Deskripsi
Pengujian kepatuhan akuntansi pada batas tahun fiskal (Year-End) dan verifikasi laporan konsolidasian antar cabang.

## Daftar Method Test & Penjelasan Detail

### 1. `test_pnl_reset_at_year_end` [✅ **PASS**]
- **Deskripsi**: Menjamin pendapatan tahun lalu tidak tercampur ke pendapatan tahun baru.
- **Logika**: Input revenue di 31 Des 2025.
- **Verifikasi**: Assert `getOpeningBalance()` untuk akun pendapatan di 1 Jan 2026 bernilai 0.

### 2. `test_balance_sheet_accumulation_at_year_end` [✅ **PASS**]
- **Deskripsi**: Menjamin saldo akun Neraca (Aset) terbawa ke tahun berikutnya (*Carryforward*).
- **Logika**: Input saldo Kas di 31 Des 2025.
- **Verifikasi**: Assert saldo awal Kas di 1 Jan 2026 mencerminkan total akumulasi tahun sebelumnya.

### 3. `test_consolidated_ledger_report` [✅ **PASS**]
- **Deskripsi**: Penarikan laporan Buku Besar gabungan dari seluruh cabang klinik.
- **Logika**: Input transaksi di Branch 1 dan Branch 2, lalu query dengan `branchId = null`.
- **Verifikasi**: Assert saldo akhir laporan konsolidasi adalah total penjumlahan dari mutasi di kedua cabang.

### 4. `test_description_propagation` [✅ **PASS**]
- **Deskripsi**: Memastikan deskripsi dari header jurnal diwariskan dengan benar ke setiap baris Buku Besar.
- **Logika**: Membuat jurnal dengan deskripsi "Pembayaran Listrik" tanpa deskripsi di level line.
- **Verifikasi**: Assert record di tabel GL menyimpan deskripsi "Pembayaran Listrik".

---
*Dihasilkan secara otomatis oleh Antigravity.*
