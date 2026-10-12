# Dokumentasi Testing: GeneralLedgerSimulationTest

## Deskripsi
Pengujian skalabilitas ekstrem untuk memvalidasi performa Stored Procedure dalam memproses jutaan jurnal sekaligus.

## Daftar Method Test & Penjelasan Detail

### 1. `test_performance_50000_journals_via_sql` [✅ **PASS**]
- **Deskripsi**: Verifikasi kecepatan pemrosesan 50.000 jurnal.
- **Logika**: Injeksi 50.000 data ke tabel `journals` dan `journal_lines`, kemudian menjalankan `syncByPeriod()`.
- **Verifikasi**: Assert bahwa sistem mampu menyelesaikan sinkronisasi dalam waktu yang wajar (Benchmarking).

### 2. `test_performance_9500000_journals_via_sql` [✅ **PASS**]
- **Deskripsi**: Uji batas beban ekstrem (9,5 Juta Jurnal).
- **Logika**: Simulasi sinkronisasi dataset masif untuk menemukan titik jenuh sistem.
- **Verifikasi**: Assert durasi eksekusi dan integritas data akhir di tabel `general_ledgers`.

---
*Dihasilkan secara otomatis oleh Antigravity.*
