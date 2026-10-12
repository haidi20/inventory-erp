# Dokumentasi Testing: JournalMultiCurrencyCompleteTest

## Deskripsi
Test suite ini mencakup pengujian komprehensif untuk transaksi dalam mata uang asing (USD, SGD) dan perhitungan selisih kurs (Exchange Rate Gains/Losses).

## Daftar Method Test & Penjelasan Detail

### 1. `test_scenario_1_cash_sale_usd` [✅ **PASS**]
- **Deskripsi**: Penjualan tunai dalam USD menggunakan kurs saat ini.
- **Verifikasi**: Assert nilai IDR dihitung benar (USD 1000 * 15500 = Rp 15.500.000) dan tersimpan di metadata line.

### 2. `test_scenario_2_credit_sale_and_payment_usd_gain` [✅ **PASS**]
- **Deskripsi**: Skenario Realized Gain (Laba Selisih Kurs).
- **Logika**: 
    - Penjualan kredit saat USD = 15.500.
    - Pelunasan piutang saat USD = 15.800.
- **Verifikasi**: Assert sistem otomatis mencatat `Exchange Gain` sebesar Rp 300.000.

### 3. `test_scenario_3_purchase_and_payment_sgd_loss` [✅ **PASS**]
- **Deskripsi**: Skenario Realized Loss (Rugi Selisih Kurs).
- **Logika**: Pembayaran hutang SGD saat kurs lebih mahal (11.800) dibanding saat pembelian (11.500).
- **Verifikasi**: Assert pencatatan `Exchange Loss` di sisi Debit akun Selisih Kurs.

### 4. `test_scenario_4_revaluation_end_of_month_usd` [✅ **PASS**]
- **Deskripsi**: Revaluasi Unrealized (Penyesuaian Kurs Akhir Bulan).
- **Logika**: Menyesuaikan saldo Kas USD di pembukuan sesuai kurs penutupan tanpa mutasi kas fisik.
- **Verifikasi**: Assert saldo IDR di Ledger berubah mengikuti kurs penutupan (MTM - Mark to Market).

### 5. `test_scenario_6_multi_currency_transaction_usd_to_sgd` [✅ **PASS**]
- **Deskripsi**: Transaksi Cross-Currency (Transfer antar Valas).
- **Verifikasi**: Assert satu jurnal bisa menangani baris USD dan SGD secara bersamaan melalui konversi basis IDR.

### 6. `test_scenario_7_fixed_asset_historical_rate` [✅ **PASS**]
- **Deskripsi**: Penggunaan Kurs Historis untuk Aset Tetap.
- **Verifikasi**: Memastikan nilai aset tetap tidak direvaluasi meskipun kurs pasar berubah, sesuai prinsip akuntansi biaya historis.

---
*Dihasilkan secara otomatis oleh Antigravity.*
