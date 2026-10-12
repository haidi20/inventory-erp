# Dokumentasi Testing: JournalConsolidationTest

## Deskripsi
Memverifikasi keamanan data antar cabang (Multi-branch Isolation) dan kemampuan pelaporan konsolidasi.

## Daftar Method Test & Penjelasan Detail

### 1. `test_branch_isolation_prevents_unauthorized_post` [✅ **PASS**]
- **Deskripsi**: User Cabang A dilarang memposting ke Cabang B.
- **Verifikasi**: Assert melempar `UnauthorizedException`.

### 2. `test_consolidated_query_returns_all_branches` [✅ **PASS**]
- **Deskripsi**: Laporan konsolidasi harus mampu menarik data dari seluruh cabang.
- **Verifikasi**: Assert hasil laporan berisi data jurnal dari Branch A dan Branch B sekaligus.

### 3. `test_prevents_unauthorized_detail_access` [✅ **PASS**]
- **Deskripsi**: Melindungi privasi data (Row-Level Security) saat melihat detail transaksi.
- **Verifikasi**: Assert user tidak bisa melihat detail jurnal milik cabang lain.

---
*Dihasilkan secara otomatis oleh Antigravity.*
