# Dokumentasi Testing: GeneralLedgerUpsertTest

## Deskripsi
Memastikan mekanisme sinkronisasi Buku Besar bersifat *Idempotent* (menggunakan pola UPSERT) untuk mencegah duplikasi data saat terjadi update atau sinkronisasi ulang.

## Daftar Method Test & Penjelasan Detail

### 1. `test_gl_sync_handles_upsert_on_update` [✅ **PASS**]
- **Deskripsi**: Menjamin perubahan pada jurnal tidak membuat baris baru di Buku Besar.
- **Logika**: Membuat jurnal -> Sync -> Update deskripsi jurnal -> Sync ulang.
- **Verifikasi**: Assert deskripsi di GL terupdate dan ID record GL tetap sama (tidak bertambah).

### 2. `test_gl_sync_cleans_up_stale_entries` [✅ **PASS**]
- **Deskripsi**: Pembersihan baris GL secara otomatis jika jurnal asalnya dihapus.
- **Logika**: Membuat jurnal -> Sync -> Hapus jurnal -> Sync ulang.
- **Verifikasi**: Assert record terkait di tabel `general_ledgers` ikut terhapus (*Cleanup Stale*).

### 3. `test_gl_sync_adds_new_entries` [✅ **PASS**]
- **Deskripsi**: Penambahan record baru ke GL untuk jurnal yang belum pernah disinkronkan.
- **Verifikasi**: Assert jumlah record GL bertambah 1 setelah input jurnal baru dan sinkronisasi dijalankan.

### 4. `test_opening_balance_fiscal_year_reset` [✅ **PASS**]
- **Deskripsi**: Validasi reset saldo akun Laba/Rugi saat pergantian tahun fiskal.
- **Logika**: Transaksi pendapatan di 31 Des 2025.
- **Verifikasi**: Assert saldo awal akun tersebut di 1 Jan 2026 kembali menjadi 0.

### 5. `test_ledger_filters` [✅ **PASS**]
- **Deskripsi**: Validasi filter pencarian (Keyword, Nominal Range).
- **Verifikasi**: Assert hasil query laporan GL menyaring data sesuai kriteria deskripsi dan rentang nominal yang ditentukan.

### 6. `test_ledger_trial_balance_consistency` [✅ **PASS**]
- **Deskripsi**: Verifikasi kesetimbangan total Debit/Kredit di level global setelah proses Upsert massal.
- **Verifikasi**: Assert `is_balanced = true`.

---
*Dihasilkan secara otomatis oleh Antigravity.*
