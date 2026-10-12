catatan terbaru taruh di atas
# Rencana Pengembangan Klinik Starter (Descending Order)

## 11. Konsistensi Soft Delete pada Repository (DB Facade)
- [ ] Audit seluruh Repository yang menggunakan `DB::table()` atau Query Builder mentah (bukan Eloquent).
- [ ] Pastikan semua query pencarian (`find`, `get`, `first`, `findAll`) menyertakan kondisi filter `->whereNull('deleted_at')` agar data yang sudah dihapus tidak muncul.
- [ ] Buat dan Update Testing dengan konsep **TDD** untuk memverifikasi bahwa fungsionalitas soft delete berjalan dengan benar di seluruh layer Repository.

## 10. Optimalisasi Indexing Database [DONE]
- [x] Audit seluruh file migrasi (folder `v1`) untuk memastikan kolom kunci (`uuid`, `id`) dan kolom relasi (`*_uuid`) memiliki index yang tepat (`->index()`, `->unique()`, atau `->primary()`). (DONE — 107 tabel, 355 single-column index dibackport ke migration create table, terutama `2026_04_15_000001_create_permission_tables.php`)
- [x] Pastikan kolom pencarian dan filter yang sering digunakan (seperti `document_no`, `invoice_no`, `code`, `name`, `status`) sudah ter-index untuk menjamin performa query. (DONE — termasuk composite standar industri: `(branch_uuid, deleted_at)`, `(company_uuid, deleted_at)`, `(branch_uuid, status)`, `(branch_uuid, invoice_no|document_no)`, `(branch_uuid, invoice_date)`)
- [x] Buat dan Update Testing dengan konsep **TDD** untuk memverifikasi keberadaan index pada kolom-kolom kritikal di skema database hasil migrasi. (DONE via `tests/Feature/Migrations/IndexOptimizationTest.php` — 9 test method mencakup primary key, leading-column, dan 5 jenis composite)

## 9. Prioritas Strategis (Segera)
- [x] **Audit Konsistensi Output API**: Pastikan seluruh API Resource telah menggunakan Trait `HasFormattedTimestamps` agar field `*_formatted` tersedia di seluruh endpoint tanpa terkecuali. (DONE via `ApiResourceConsistencyTest`)
- [x] **Penyelesaian Porting Service Sisa**: Selesaikan porting `StockBalanceService`, `StockTransferService`, dan `OpeningBalanceService` dari Go ke PHP dengan standar TDD yang sama. (DONE)
- [x] **Implementasi Repository Konkrit**: Buat/verifikasi implementasi Eloquent untuk semua interface baru (Permission, PurchaseOrder, Stock, OpeningBalance, dll). (DONE - 8 Repositories implemented)
- [x] **Integration Test (Flow Transaksi)**: Buat Feature Test untuk menguji flow lengkap (End-to-End), contoh: alur *Purchase Order* -> *Purchase* -> *Stock Movement* -> *Journal Entry*. (DONE via `ProcurementFlowTest`)
- [x] **Cleanup Residu Kode Go**: Lakukan pembersihan massal terhadap seluruh komentar kode Go yang masih tersisa di file-file Service untuk menjaga kebersihan codebase. (DONE via `GoCodeCleanupTest`)

## 8. Pemeliharaan & Kualitas (Jangka Menengah) [DONE]
- [x] **Contract Test API Response**: tambah test untuk memastikan semua endpoint utama selalu mengembalikan field timestamp mentah (`unixtime`) + field display (`*_formatted`, `*_formatted_long`) secara konsisten. (DONE via `ApiResourceConsistencyTest`)
- [x] **Standarisasi Validasi Input Tanggal**: audit seluruh `FormRequest` agar input tanggal dari client tervalidasi ketat dan langsung dinormalisasi ke `unixtime` sebelum masuk service. (DONE)
- [x] **Error Handling & Logging Terstruktur**: rapikan logging di service transaksi/laporan (context: `branch_id`, `user_id`, rentang tanggal) supaya debugging produksi lebih cepat. (DONE)
- [x] **Regression Test Laporan Keuangan**: perluas skenario test untuk batas tanggal (awal/akhir bulan, tahun kabisat, timezone WIB/WITA/WIT) agar tidak ada selisih hasil laporan. (DONE)
- [x] **Performance Check Query Tanggal**: Review query filter periode (journal, ledger, report) dan tambahkan index yang relevan untuk kolom `bigInteger` tanggal agar performa tetap stabil saat data besar. (DONE)
- [x] **CI Quality Gate**: Pastikan pipeline wajib lulus `lint`, `phpstan` (atau static analysis setara), `unit test`, dan `feature test` sebelum merge ke branch utama. (DONE)

## 7. Standardisasi Nama Kolom Relasi (`*_uuid`) [DONE]
- [x] Update seluruh file migrasi (folder `v1` dan `v2`) agar kolom relasi yang merujuk ke tabel lain menggunakan suffix `_uuid` (contoh: `branch_id` -> `branch_uuid`, `product_id` -> `product_uuid`, `item_id` -> `item_uuid`).
- [x] Update Model Eloquent untuk menyesuaikan nama kolom relasi, properti `$fillable`, dan definisi relasi (`belongsTo`, `hasMany`, dll).
- [x] Update Repository Layer agar sinkron dengan perubahan nama kolom di database.
- [x] Buat dan Update Testing dengan konsep **TDD** untuk memastikan seluruh relasi antar tabel tetap berfungsi dengan benar paska perubahan nama kolom. (Divalidasi via `ProcurementFlowTest`)


## 6. Automation Testing (Regression) [DONE]
- [x] Jalankan regression test menggunakan script di folder `scratch/` dan Unit Test Service.
- [x] Pastikan fitur laporan (General Ledger, Neraca, Laba Rugi) tidak error saat melakukan filter berdasarkan periode tanggal (Verifikasi via `GeneralLedgerServiceTest`).

## 5. Verifikasi Integrasi SATUSEHAT [DONE]
- [x] Verifikasi pengiriman data di `SatusehatService`.
- [x] Pastikan ada logika konversi dari `bigInteger` (database) ke string format **ISO8601** yang diwajibkan oleh standar FHIR Kemenkes.

## 4. Implementasi Helper/Trait Display (Frontend) [DONE]
- [x] Buat Trait `App\Traits\HasFormattedTimestamps` untuk pemformatan manual di Model atau Service.
- [x] Tambahkan Unit Test `tests/Unit/Traits/HasFormattedTimestampsTest.php` untuk verifikasi format Indonesia.
- [x] Pastikan standard format lokal Indonesia tersedia secara otomatis di layer API Resource via `ApiDataResource` (menggunakan concern `HasFormattedTimestamps`).

## 3. Validasi & Casting di Service Layer [DONE]
- [x] Review `LookupService` dan `LookupSetService` untuk memastikan pencarian range tanggal menggunakan integer.
- [x] Update `JournalEntity` untuk melakukan casting otomatis ke Unix Timestamp.
- [x] Pastikan Service transaksi (Sales, Purchase, Journal) melakukan casting input string tanggal dari frontend menjadi integer sebelum disimpan.
- [x] Update `JournalService`, `FinancialReportService`, dan `GeneralLedgerService` untuk menggunakan integer comparison pada filter tanggal.

## 2. Sinkronisasi Seeder & Factory [DONE]
- [x] Update seluruh Factory agar menggunakan `time()` atau `strtotime()`.
- [x] Update Seeder V1 (Demo, COA, Product Data, Simulasi) agar menggunakan Unix timestamp.
- [x] Ganti query `whereDate()` menjadi `whereBetween()` atau perbandingan integer pada file simulasi.

## 1. Skema Database & Eloquent [DONE]
- [x] Konversi seluruh file migrasi (44 file) dari `timestamp/date/dateTime` ke `bigInteger`.
- [x] Tambahkan `protected $dateFormat = 'U';` di seluruh Model (113 file) agar Eloquent menangani integer sebagai timestamp.
- [x] Pastikan kolom audit (`created_at`, `updated_at`, `deleted_at`) dan kolom bisnis (`birth_date`, `journal_date`, `synced_at`, dll) konsisten.
