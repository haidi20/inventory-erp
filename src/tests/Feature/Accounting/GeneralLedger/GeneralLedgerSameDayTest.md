# Dokumentasi Testing: GeneralLedgerSameDayTest

## Deskripsi
Menguji urutan perhitungan saldo berjalan (*running balance*) untuk transaksi yang terjadi pada tanggal yang sama.

## Daftar Method Test & Penjelasan Detail

### 1. `test_running_balance_same_date_order_by_id` [✅ **PASS**]
- **Deskripsi**: Menjamin saldo berjalan dihitung berdasarkan urutan input (ID) jika tanggal transaksinya identik.
- **Logika**: Memasukkan 3 jurnal dengan tanggal yang sama (Debit 100rb, Debit 50rb, Kredit 75rb).
- **Verifikasi**: 
    - Baris 1: Saldo 100.000.
    - Baris 2: Saldo 150.000.
    - Baris 3: Saldo 75.000.

---
*Dihasilkan secara otomatis oleh Antigravity.*
