# Dokumentasi Testing: TrialBalanceCurrencyTest

## Deskripsi
Memastikan Trial Balance mampu menangani transaksi multi-valuta (Multi-Currency) dan menghitung selisih kurs (*Exchange Gain/Loss*) secara akurat sesuai standar akuntansi.

## Daftar Method Test & Penjelasan Detail

### 1. `test_trial_balance_handles_foreign_currency_translation` [✅ **PASS**]
- **Deskripsi**: Verifikasi konversi nominal dari valuta asing (USD) ke mata uang fungsional (IDR).
- **Logika**: Posting jurnal USD 100 dengan kurs 15.000.
- **Verifikasi**: Assert saldo di TB tercatat sebagai IDR 1.500.000.

### 2. `test_exchange_gain_loss_realized_on_payment` [✅ **PASS**]
- **Deskripsi**: Penghitungan laba/rugi selisih kurs saat terjadi pelunasan dengan kurs yang berbeda.
- **Verifikasi**: Assert akun "Laba/Rugi Selisih Kurs" menunjukkan saldo yang benar (Selisih Kurs Beli vs Kurs Jual).

### 3. `test_unrealized_gain_loss_revaluation` [✅ **PASS**]
- **Deskripsi**: Revaluasi saldo akun valuta asing pada akhir periode.
- **Verifikasi**: Assert saldo penyesuaian otomatis muncul di TB sesuai kurs penutupan terbaru.

---
*Dihasilkan secara otomatis oleh Antigravity.*
