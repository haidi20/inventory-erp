# Dokumentasi Testing: JournalBoundaryTest

## Deskripsi
Menguji batas-batas parameter (Boundary Conditions) seperti nilai nominal ekstrem, presisi desimal, dan pergantian periode waktu.

## Daftar Method Test & Penjelasan Detail

### 1. `test_handles_large_trillion_scale_transaction` [✅ **PASS**]
- **Deskripsi**: Mendukung transaksi skala besar (Miliar/Triliun).
- **Verifikasi**: Assert nilai 999 Miliar tersimpan dengan presisi yang benar di database.

### 2. `test_handles_small_decimal_precision` [✅ **PASS**]
- **Deskripsi**: Mendukung nilai desimal terkecil (0.01).
- **Verifikasi**: Assert nilai 0.01 tidak dibulatkan menjadi 0 oleh sistem.

### 3. `test_handles_exact_start_of_month_boundary` [✅ **PASS**]
- **Deskripsi**: Validasi transaksi tepat di tanggal 1 awal bulan.
- **Verifikasi**: Assert jurnal berhasil diposting pada tanggal batas periode.

### 4. `test_transaction_sequence_resets_on_new_month` [✅ **PASS**]
- **Deskripsi**: Reset nomor urut (`transaction_code`) setiap awal bulan.
- **Logika**: Simulasi posting di Maret (nomor urut berlanjut) -> Pindah ke April.
- **Verifikasi**: Assert nomor urut di April kembali ke `0001`.

---
*Dihasilkan secara otomatis oleh Antigravity.*
