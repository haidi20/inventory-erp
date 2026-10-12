# 📘 Skenario #21: Validasi Akun Header

Mencegah posting jurnal ke akun yang bertindak sebagai **Header** (`is_header = 1`). Akun header hanya digunakan untuk pengelompokan (struktur pohon COA) dan tidak boleh memiliki rincian debit/kredit langsung.

---

## 🔬 Spesifikasi Pengujian

| Komponen | Penjelasan |
|---|---|
| **File** | `tests/Feature/Accounting/AccountValidationTest.php` |
| **Metode** | `test_cannot_post_to_header_account` |
| **Pemicu** | `JournalService::createJournal($dto)` |
| **Ekspektasi** | Melempar `Exception` berisi pesan `"Cannot post to header account"` |

---

## 🛠️ Langkah TDD (Test-Driven Development)

1. **[RED] Setup Test**: Membuat test yang mencoba memasukkan `account_id` milik akun header ke dalam `JournalLine`.
2. **[RED] Run**: Menjalankan test dan **Gagal** (belum ada proteksi di service).
3. **[GREEN] Code Fix**: Menambahkan validasi `is_header` di `JournalService.php` agar menolak entry tersebut.
4. **[GREEN] Run**: Menjalankan test kembali dan **Lolos (Passed)**.

---

## 📝 Catatan Implementasi
Proteksi ini dipasang di dalam `prepareJournalData()` atau loop rincian baris untuk memastikan segala jenis jurnal (Sistem maupun Manual) tunduk pada aturan akuntansi baku ini.
