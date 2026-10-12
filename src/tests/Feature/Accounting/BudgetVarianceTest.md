# Dokumentasi Testing: BudgetVarianceTest

## Deskripsi
Memastikan sistem mampu menganalisis variansi antara anggaran (Budget) yang telah ditetapkan dengan realisasi (Actual) serta memberikan peringatan jika melampaui batas.

## Daftar Method Test & Penjelasan Detail

### 1. `test_actual_vs_budget_variance_with_alerts` [✅ **PASS**]
- **Deskripsi**: Verifikasi akurasi saldo variansi dan deteksi status *Over Budget*.
- **Logika**: Membandingkan angka actual dengan target budget fixed.
- **Verifikasi**: Assert `variance` adalah Rp 2.000.000 (20%) dan status `isOverBudget` adalah true.

---
*Dihasilkan secara otomatis oleh Antigravity.*
