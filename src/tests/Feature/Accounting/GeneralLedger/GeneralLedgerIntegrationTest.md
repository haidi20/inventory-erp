# Dokumentasi Testing: GeneralLedgerIntegrationTest

## Deskripsi
Menguji skenario end-to-end mulai dari posting jurnal harian, sinkronisasi Buku Besar, hingga proses tutup bulan dan proteksi integritas data.

## Daftar Method Test & Penjelasan Detail

### 1. `test_end_to_end_monthly_closing` [✅ **PASS**]
- **Deskripsi**: Simulasi aktivitas operasional sebulan penuh hingga penguncian periode.
- **Logika**: 
    1. Posting 10 pasang jurnal (Revenue & Expense) secara harian.
    2. Menjalankan `syncByPeriod()`.
    3. Mengubah status periode menjadi `closed`.
- **Verifikasi**: 
    - Assert Trial Balance sebelum tutup periode dalam kondisi seimbang.
    - Assert sistem menolak posting jurnal baru ke periode yang sudah tertutup.

### 2. `test_void_journal_reversal` [✅ **PASS**]
- **Deskripsi**: Verifikasi dampak pembatalan jurnal terhadap saldo Buku Besar.
- **Logika**: Posting jurnal (menambahkan saldo) -> Void jurnal.
- **Verifikasi**: Assert saldo akhir kembali ke nilai semula (sebelum posting) karena adanya reversal otomatis.

### 3. `test_data_integrity_after_rollback` [✅ **PASS**]
- **Deskripsi**: Menjamin atomisitas data. Jika jurnal gagal simpan, tidak boleh ada jejak di Buku Besar.
- **Logika**: Mencoba memposting jurnal yang tidak seimbang (Unbalanced).
- **Verifikasi**: Assert jumlah record jurnal tidak bertambah dan saldo Buku Besar tidak berubah.

### 4. `test_sequential_periods_carry_forward` [✅ **PASS**]
- **Deskripsi**: Kontinuitas data antar periode (Monthly Roll-forward).
- **Logika**: Posting di Januari, Februari, dan Maret secara berurutan.
- **Verifikasi**: Assert saldo akhir Januari menjadi saldo awal Februari, dan seterusnya.

---
*Dihasilkan secara otomatis oleh Antigravity.*
