# Dokumentasi Testing: TrialBalanceBudgetTest

## Deskripsi
Memastikan Trial Balance mampu membandingkan data riil (Actual) dengan anggaran (Budget) yang telah ditetapkan.

## Daftar Method Test & Penjelasan Detail

### 1. `test_actual_vs_budget_variance_calculation` [✅ **PASS**]
- **Deskripsi**: Verifikasi akurasi persentase pemakaian anggaran.
- **Logika**: Set Budget 1jt -> Realisasi Biaya 800rb.
- **Verifikasi**: Assert `variance_amount` adalah 200rb dan `usage_percentage` adalah 80%.

### 2. `test_over_budget_warning_threshold` [✅ **PASS**]
- **Deskripsi**: Peringatan jika pengeluaran melebihi anggaran di laporan.
- **Verifikasi**: Assert bendera `is_over_budget = true` pada baris akun biaya terkait.

---
*Dihasilkan secara otomatis oleh Antigravity.*
