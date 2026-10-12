# Dokumentasi Testing: AdjustingJournalTest

## Deskripsi
Memastikan validasi dan otomatisasi pembuatan Jurnal Penyesuaian (Adjusting Journals) untuk berbagai skenario akuntansi seperti akrual beban, beban dibayar dimuka (prepaid), dan penyusutan aset.

## Daftar Method Test & Penjelasan Detail

### 1. `test_scenario_1_accrual_expense_generation` [✅ **PASS**]
- **Deskripsi**: Verifikasi pembuatan jurnal penyesuaian untuk akrual beban (contoh: utang gaji).
- **Verifikasi**: Assert jurnal bertipe `ADJ` dan flag `is_adjusting` bernilai true.

### 2. `test_scenario_2_prepaid_and_depreciation_generation` [✅ **PASS**]
- **Deskripsi**: Verifikasi amortisasi beban dibayar dimuka dan penyusutan aset tetap.
- **Verifikasi**: Assert nilai debit/kredit sesuai dengan nominal yang diinput.

### 3. `test_scenario_13_idempotency_validation` [✅ **PASS**]
- **Deskripsi**: Memastikan jurnal penyesuaian tidak dibuat ganda untuk periode yang sama.
- **Verifikasi**: Assert melempar exception jika mencoba generate ulang di periode yang sudah ada jurnal penyesuaiannya.

### 4. `test_scenario_14_transactional_rollback` [✅ **PASS**]
- **Deskripsi**: Memastikan integritas data tetap terjaga (rollback) jika terjadi kegagalan saat proses batch posting.
- **Verifikasi**: Assert jumlah jurnal di database tidak bertambah jika terjadi error di tengah proses.

---
*Dihasilkan secara otomatis oleh Antigravity.*
