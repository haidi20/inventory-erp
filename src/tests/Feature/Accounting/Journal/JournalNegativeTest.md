# Dokumentasi Testing: JournalNegativeTest

## Deskripsi
Kumpulan "Negative Testing" untuk memastikan sistem menolak input data yang tidak valid secara finansial (Defense in Depth).

## Daftar Method Test & Penjelasan Detail

### 1. `test_rejects_zero_value_journal` [✅ **PASS**]
- **Deskripsi**: Jurnal dengan nominal Nol (0) harus ditolak.
- **Verifikasi**: Assert melempar error "Journal total amount must be greater than zero".

### 2. `test_rejects_single_line_journal` [✅ **PASS**]
- **Deskripsi**: Menjamin prinsip Berpasangan (Double-entry). Jurnal tidak boleh hanya satu baris.
- **Verifikasi**: Assert error "must have at least two lines".

### 3. `test_rejects_unbalanced_journal` [✅ **PASS**]
- **Deskripsi**: Menolak jurnal jika total Debit tidak sama dengan total Kredit.
- **Verifikasi**: Assert error "Journal unbalanced".

---
*Dihasilkan secara otomatis oleh Antigravity.*
