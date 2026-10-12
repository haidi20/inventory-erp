# Dokumentasi Testing: JournalSubLedgerCompleteTest

## Deskripsi
Memverifikasi integritas data Sub-Ledger dan kewajiban pengaitan transaksi Piutang/Hutang dengan identitas Kontak (Pasien/Supplier).

## Daftar Method Test & Penjelasan Detail

### 1. `test_mandatory_contact_id_for_receivable` [✅ **PASS**]
- **Deskripsi**: Mencegah kebocoran data Piutang tanpa identitas Pasien.
- **Logika**: Mencoba membuat jurnal Piutang tanpa menyertakan `contact_id`.
- **Verifikasi**: Assert sistem memblokir transaksi dengan `Exception` validasi Sub-Ledger.

### 2. `test_mandatory_contact_id_for_payable` [✅ **PASS**]
- **Deskripsi**: Mencegah pencatatan Hutang tanpa identitas Supplier.
- **Verifikasi**: Assert penolakan pembuatan jurnal hutang jika vendor tidak diidentifikasi.

### 3. `test_success_with_contact_id` [✅ **PASS**]
- **Deskripsi**: Alur sukses pencatatan piutang dengan tagging pasien yang valid.
- **Verifikasi**: Assert `contact_id` tersimpan di database dan jurnal berhasil diposting.

### 4. `test_direct_journal_permitted_for_non_subledger` [✅ **PASS**]
- **Deskripsi**: Memastikan fleksibilitas untuk akun operasional (Non-SubLedger).
- **Logika**: Membuat jurnal Kas atau Biaya Umum tanpa `contact_id`.
- **Verifikasi**: Assert jurnal berhasil dibuat tanpa hambatan validasi.

---
*Dihasilkan secara otomatis oleh Antigravity.*
