# 📘 Skenario #17: Validasi Saldo Awal (Opening Balance)

Memastikan bahwa ketika periode akuntansi baru dibuat (`ClosingPeriod`), sistem secara otomatis menyalin **Saldo Akhir (Closing Balance)** dari periode sebelumnya menjadi **Saldo Awal (Opening Balance)** di periode yang baru untuk akun Neraca (`balance_sheet`), sedangkan akun Laba Rugi (`income_statement`) harus disetel ulang menjadi `0`.

---

## 🔬 Spesifikasi Pengujian

| Komponen | Penjelasan |
|---|---|
| **File** | `tests/Feature/Accounting/OpeningBalanceTest.php` |
| **Metode** | `test_opening_balance_initialization_from_previous_period` |
| **Pemicu** | `ClosingPeriod::create($dataFebruari)` |
| **Ekspektasi** | `AccountBalance` Februari terbuat membawa saldo aset dari Januari. |

---

## 🛠️ Langkah TDD (Test-Driven Development)

1. **[RED] Setup Test**: Membuat test yang mencatat saldo closing di Jan, lalu membuat periode Feb.
2. **[RED] Run**: Menjalankan test dan **Gagal** (saldoFebruari masih `Null` karena model belum menginisialisasi apa-apa).
3. **[GREEN] Code Fix**: Menambahkan handler `created` event di `ClosingPeriod.php` atau hook service untuk mentransfer saldo.
4. **[GREEN] Run**: Menjalankan test kembali dan **Lolos (Passed)**.
