# Dokumentasi Testing: JournalFiskalCorrectionTest

## Deskripsi
Memastikan pencatatan koreksi fiskal (Positive/Negative) untuk kebutuhan pelaporan pajak akhir tahun sesuai aturan perpajakan.

## Daftar Method Test & Penjelasan Detail

### 1. `test_journal_with_fiscal_correction` [✅ **PASS**]
- **Deskripsi**: Pencatatan beban non-deductible (Koreksi Fiskal Positif).
- **Logika**: Menandai baris jurnal dengan `is_fiscal = true` dan `fiscal_type = positive`.
- **Verifikasi**: 
    - Assert flag fiskal tersimpan di database.
    - Assert tipe fiskal terbaca sebagai `positive`.

---
*Dihasilkan secara otomatis oleh Antigravity.*
