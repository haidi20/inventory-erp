# Dokumentasi Testing: TrialBalanceApprovalTest

## Deskripsi
Menguji alur persetujuan bertingkat (*Approval Workflow*) pada jurnal dan dampaknya terhadap kesiapan laporan Trial Balance.

## Daftar Method Test & Penjelasan Detail

### 1. `test_trial_balance_reflects_only_approved_journals` [✅ **PASS**]
- **Deskripsi**: Menjamin jurnal draft/pending tidak masuk ke perhitungan saldo formal.
- **Logika**: Posting jurnal -> Set status `pending_approval`.
- **Verifikasi**: Assert saldo di TB belum berubah. Setelah disetujui (`approved`), baru saldo terupdate.

### 2. `test_multiple_approval_levels_impact` [✅ **PASS**]
- **Deskripsi**: Simulasi alur dari SPV -> Manager.
- **Verifikasi**: Assert saldo TB hanya terupdate setelah level approval terakhir terpenuhi.

### 3. `test_rejected_journal_reversion` [✅ **PASS**]
- **Deskripsi**: Memastikan jurnal yang ditolak tidak meninggalkan jejak di Buku Besar/TB.
- **Verifikasi**: Assert `total_debit` dan `total_credit` tetap konsisten (seperti sebelum posting).

---
*Dihasilkan secara otomatis oleh Antigravity.*
