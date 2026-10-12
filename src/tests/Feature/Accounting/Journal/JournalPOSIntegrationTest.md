# Dokumentasi Testing: JournalPOSIntegrationTest

## Deskripsi
Memastikan integrasi *seamless* antara modul operasional (Point of Sale & Rekam Medis) dengan modul akuntansi melalui pembuatan jurnal otomatis.

## Daftar Method Test & Penjelasan Detail

### 1. `test_pos_sale_generates_correct_journal_lines` [✅ **PASS**]
- **Deskripsi**: Siklus penuh transaksi penjualan kasir (POS) masuk ke Ledger.
- **Logika**: Memicu `storeJournal` dari objek `Sale`.
- **Verifikasi**: 
    - Assert status jurnal langsung `POSTED`.
    - Assert keseimbangan (Balance) Debit dan Kredit.
    - Assert akun Kas terdebet sesuai nilai total penjualan.

### 2. `test_medical_encounter_generates_correct_journal_lines` [✅ **PASS**]
- **Deskripsi**: Otomasi pengakuan pendapatan dari layanan pemeriksaan medis (Encounter).
- **Verifikasi**: Assert terbentuknya baris pengakuan Pendapatan Jasa pada akun yang dipetakan (Account Mapping).

---
*Dihasilkan secara otomatis oleh Antigravity.*
