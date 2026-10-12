# Dokumentasi Testing: MultiBranchTest

## Deskripsi
Memastikan integritas dan isolasi data akuntansi antar cabang (Multi-Branch) tetap terjaga saat menjalankan proses-proses batch akuntansi.

## Daftar Method Test & Penjelasan Detail

### 1. `test_scenario_11_multi_branch_isolation` [✅ **PASS**]
- **Deskripsi**: Verifikasi bahwa proses generate jurnal penyesuaian di satu cabang tidak mempengaruhi atau tercampur dengan data cabang lain.
- **Logika**: Menjalankan proses untuk Branch Jakarta dan Branch Surabaya secara terpisah.
- **Verifikasi**: Assert hasil untuk kedua cabang adalah array yang valid dan terpisah.

---
*Dihasilkan secara otomatis oleh Antigravity.*
