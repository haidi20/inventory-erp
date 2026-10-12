# Dokumentasi Testing: AdjustedTrialBalanceTest

## Deskripsi
Memastikan sistem dapat menghasilkan Neraca Saldo Disesuaikan (Adjusted Trial Balance) dengan struktur data yang benar sesuai standar akuntansi.

## Daftar Method Test & Penjelasan Detail

### 1. `test_scenario_3_adjusted_trial_balance_structure` [✅ **PASS**]
- **Deskripsi**: Verifikasi struktur data laporan Neraca Saldo Disesuaikan.
- **Logika**: Memanggil service untuk mengambil data Adjusted Trial Balance untuk cabang dan periode tertentu.
- **Verifikasi**: Assert hasil adalah array dan memiliki properti akun (`account_code` atau `code`).

---
*Dihasilkan secara otomatis oleh Antigravity.*
