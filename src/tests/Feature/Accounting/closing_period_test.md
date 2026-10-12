# 📘 Skenario #22: Validasi Periode Berurutan

Mencegah pembuatan periode akuntansi yang memiliki **Gap (Lompat Bulan)** atau **Overlap (Tumpang Tindih)** dari tanggal periode sebelumnya untuk menjamin integritas timeline keuangan.

---

## 🔬 Spesifikasi Pengujian

| Komponen | Penjelasan |
|---|---|
| **File** | `tests/Feature/Accounting/ClosingPeriodTest.php` |
| **Metode 1** | `test_cannot_create_gap_between_periods` |
| **Metode 2** | `test_cannot_overlap_periods` |
| **Pemicu** | `ClosingPeriod::create($data)` |
| **Ekspektasi** | Melempar `Exception` berisi pesan yang relevan (Gap / Overlap). |

---

## 🛠️ Langkah TDD (Test-Driven Development)

1. **[RED] Setup Test**: Membuat test yang mencoba membuat periode Maret tanpa membuat Februari terlebih dahulu.
2. **[RED] Run**: Menjalankan test dan **Gagal** (periode berhasil terbuat).
3. **[GREEN] Code Fix**: Menambahkan validasi `creating` event di `ClosingPeriod.php` atau `booted()` hook untuk verifikasi relasi tanggal.
4. **[GREEN] Run**: Menjalankan test kembali dan **Lolos (Passed)**.
