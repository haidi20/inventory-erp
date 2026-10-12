# Dokumentasi Testing: JournalWorkflowSimulationTest

## Deskripsi
Simulasi *end-to-end* siklus hidup jurnal dari pembuatan hingga penguncian otomatis (Finalization).

## Daftar Method Test & Penjelasan Detail

### 1. `test_full_approval_workflow_simulation` [✅ **PASS**]
- **Deskripsi**: Simulasi alur kerja lengkap (Draft -> Approved -> Posted).
- **Logika**: 
    1. Membuat jurnal status `draft`.
    2. Menjalankan proses approval.
- **Verifikasi**: 
    - Assert status akhir adalah `posted` dan `approved`.
    - Assert percobaan approval ulang (Double Approval) gagal dengan pesan "Jurnal sudah diposting".

---
*Dihasilkan secara otomatis oleh Antigravity.*
