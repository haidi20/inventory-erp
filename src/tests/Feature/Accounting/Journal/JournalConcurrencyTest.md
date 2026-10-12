# Dokumentasi Testing: JournalConcurrencyTest

## Deskripsi
Menguji ketahanan sistem terhadap akses bersamaan (Concurrency) untuk mencegah duplikasi nomor referensi dan *double approval*.

## Daftar Method Test & Penjelasan Detail

### 1. `test_prevents_duplicate_reference_submission` [✅ **PASS**]
- **Deskripsi**: Proteksi terhadap pengiriman ulang data yang sama (Idempotency Key).
- **Logika**: Mencoba membuat dua jurnal dengan nomor referensi yang sama persis.
- **Verifikasi**: Assert sistem menolak submission kedua dengan pesan error duplikasi.

### 2. `test_prevents_manual_parallel_approval` [✅ **PASS**]
- **Deskripsi**: Mencegah kondisi balapan (Race Condition) saat dua user menyetujui jurnal yang sama secara bersamaan.
- **Verifikasi**: Assert sistem melempar error jika status jurnal sudah berubah saat percobaan approval kedua dilakukan.

### 3. `test_high_frequency_sequential_journal_creation` [✅ **PASS**]
- **Deskripsi**: Uji beban untuk pembuatan nomor transaksi (`transaction_code`) dalam frekuensi tinggi.
- **Logika**: Loop pembuatan 10 jurnal secara berurutan dalam waktu milidetik.
- **Verifikasi**: 
    - Assert semua 10 `transaction_code` unik.
    - Assert format penomoran (YY/MM/XXXX) tetap konsisten dan berurutan.

---
*Dihasilkan secara otomatis oleh Antigravity.*
