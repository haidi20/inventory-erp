# Dokumentasi Testing: JournalInterBranchTest

## Deskripsi
Memverifikasi kemampuan satu paket jurnal (Journal Package) untuk menampung baris transaksi dari cabang yang berbeda (Inter-branch Entry).

## Daftar Method Test & Penjelasan Detail

### 1. `test_journal_lines_can_belong_to_different_branches` [✅ **PASS**]
- **Deskripsi**: Memasukkan record Debit di Cabang Jakarta dan Kredit di Cabang Surabaya dalam satu nomor jurnal.
- **Logika**: 
    1. Membuat dua objek Branch (JKT & SUB).
    2. Menutup periode di kedua cabang.
    3. Membuat jurnal dimana `branch_id` pada setiap baris (`lines`) berbeda.
- **Verifikasi**: Assert bahwa sistem berhasil menyimpan baris tersebut dengan `branch_id` masing-masing tanpa dipaksa mengikuti `branch_id` header.

---
*Dihasilkan secara otomatis oleh Antigravity.*
