# 📘 Skenario #27: Validasi Akun Aktif

Mencegah posting jurnal ke akun yang statusnya **Tidak Aktif** (`is_active = 0`). Akun inaktif tidak boleh digunakan untuk transaksi berjalan.

---

## 🔬 Spesifikasi Pengujian

| Komponen | Penjelasan |
|---|---|
| **File** | `tests/Feature/Accounting/ActiveAccountTest.php` |
| **Metode** | `test_cannot_post_to_inactive_account` |
| **Pemicu** | `JournalService::createJournal($dto)` |
| **Ekspektasi** | Melempar `Exception` berisi pesan `"Account is inactive"` |

---

## 🛠️ Langkah TDD (Test-Driven Development)

1. **[RED] Setup Test**: Membuat test yang mencoba memasukkan `account_id` milik akun inaktif ke dalam `JournalLine`.
2. **[RED] Run**: Menjalankan test dan **Gagal** (belum ada proteksi di service).
3. **[GREEN] Code Fix**: Menambahkan validasi `is_active` di `JournalService.php` agar menolak entry tersebut.
4. **[GREEN] Run**: Menjalankan test kembali dan **Lolos (Passed)**.
