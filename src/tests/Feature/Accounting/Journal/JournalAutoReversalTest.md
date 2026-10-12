# Dokumentasi Testing: JournalAutoReversalTest

## Deskripsi
Menguji mekanisme pembalikan jurnal otomatis (Accrual/Prepayment Reversal) pada awal periode berikutnya.

## Daftar Method Test & Penjelasan Detail

### 1. `test_auto_reversal_for_expense_accrual` [✅ **PASS**]
- **Deskripsi**: Menguji alur otomatis penjurnalan balik untuk biaya akrual (contoh: biaya gaji yang diakui bulan ini tapi dibayar bulan depan).
- **Logika**: 
    1. Membuat jurnal dengan flag `is_auto_reverse = true`.
    2. Menjalankan `ProcessAutoReversalJob`.
- **Verifikasi**: 
    - Jurnal pembalik otomatis terbentuk pada tanggal `reversal_date`.
    - Posisi Debit dan Kredit pada jurnal pembalik harus terbalik (flipped) dibanding jurnal asli.
    - Status asli berubah menjadi `reversed`.

### 2. `test_manual_reversal` [✅ **PASS**]
- **Deskripsi**: Menguji fungsi `reverseJournal` yang dipicu secara manual oleh user.
- **Logika**: Mengambil ID jurnal yang sudah ada dan memanggil service pembalik.
- **Verifikasi**: Assert terbentuknya jurnal pembalik dengan prefix deskripsi "MANUAL REV:".

### 3. `test_cannot_reverse_in_closed_period` [✅ **PASS**]
- **Deskripsi**: Validasi keamanan tingkat tinggi untuk mencegah pembalikan jurnal pada periode yang sudah dikunci (Audit Lock).
- **Logika**: Mengunci periode target, lalu mencoba melakukan reversal.
- **Verifikasi**: Assert melempar `Exception` dengan pesan "Cannot reverse journal in closed period".

---
*Dihasilkan secara otomatis oleh Antigravity.*
