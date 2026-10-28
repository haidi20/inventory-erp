# Progress Log Modularisasi

> Dipindahkan dari \_plan.md\ agar plan tetap menjadi sumber keputusan stabil.
> Log harian eksekusi ada di file ini.

### 17.16 Progress Log - 2026-09-16

#### DB Transaction Audit (Selesai)
- Audit seluruh 78 repository di semua modul.
- 3 repo diperbaiki (multi-write tanpa transaction):
  - `Operational\Inventory\StockAdjustmentRepository` - `storeAdjustment()`, `deleteAdjustment()`
  - `Operational\Inventory\StockTransferRepository` - `store()`, `delete()`
  - `Operational\Procurement\PurchaseRepository` - `save()`, `delete()`
- 3 false positive dikonfirmasi tidak perlu transaction (if/else mutually exclusive):
  - `Clinical\Integration\BpjsSettingsRepository::save()`
  - `Clinical\Registration\PatientRepository::save()`
  - `Core\Platform\SettingsRepository::saveSatusehatSettings()`
- Aturan wajib DB transaction ditambahkan ke Section 20.15.

#### Missing state.ts (Selesai)
- 29 VM files yang tidak punya pasangan `.state.ts` sudah dibuatkan:
  - `Core\Identity` - login, forgot_password, reset_password, role, user
  - `Core\Organization` - branch, company
  - `Core\Platform` - backup
  - `Core\shared\Components\common` - DataTable
  - `Operational\Master` - product
  - `Operational\Sales` - sales_transaction, examination_transaction, payable_transaction, purchase_transaction, receivable_transaction (re-export dari transaction.state)
  - `Clinical\MedicalRecord` - complaint
  - `Clinical\Pharmacy` - farmacy, farmacy_accounting, farmacy_dashboard, farmacy_inventory, farmacy_management, farmacy_opening_balance, farmacy_recipe, farmacy_report, farmacy_stock_adjustment, farmacy_stock_mutation, farmacy_stock_opname, farmacy_transaction
  - `Analytics\Reports\presentations\vm` - dashboard
- Total: 45 VM files sekarang semua punya pasangan state.ts ✅

#### Missing Routes (Selesai)
- `Clinical\Pharmacy\routes\api.php` - dibuat (placeholder, endpoint dispensing ditambahkan saat implementasi)
- `Operational\Inventory\routes\api.php` - dibuat (placeholder, endpoint REST inventory ditambahkan saat implementasi)
- `Operational\Manufacturing\routes\web.php` - dibuat (placeholder)
- `Operational\Procurement\routes\web.php` - dibuat (placeholder)
- `Analytics\Dashboard\routes\api.php` - dibuat (dashboard overview + tab-data)
- `routes/api.php` loader diupdate: tambah Clinical\Pharmacy, Operational\Inventory, Analytics\Dashboard
- `routes/web.php` loader diupdate: tambah Operational\Manufacturing, Operational\Procurement
- Semua 17 modul sekarang punya api.php dan web.php ✅


#### Scaffold Clinical\Ancillary (Selesai)
- Dibuat struktur lengkap: Entities, Repositories (+ Interface), Services, Http\Controllers, routes
- Files: LabOrderEntity, LabOrderItemEntity, RadiologyOrderEntity
- LabOrderRepository + RadiologyOrderRepository - keduanya pakai `DB::transaction()` di `save()`
- LabOrderService + RadiologyOrderService
- LabOrderController + RadiologyOrderController (full CRUD + audit log)
- `routes/api.php`: `clinic/lab-orders`, `clinic/radiology-orders` (sesuai freeze Section 16)
- Didaftarkan ke `ModuleServiceProvider` dan `routes/api.php` loader ✅

#### Scaffold Clinical\Classification (Selesai)
- Dibuat struktur lengkap: Entity, Repository (+ Interface), Service, Controller, routes
- Icd10CodeEntity, Icd10CodeRepository, Icd10CodeService, Icd10CodeController
- `routes/api.php`: `master/icd-codes` (sesuai freeze Section 16)
- Didaftarkan ke `ModuleServiceProvider` dan `routes/api.php` loader ✅

#### Summary Akhir 2026-09-16
| Item | Before | After |
|---|---|---|
| Repos dengan DB transaction | 22 | 27 |
| VM tanpa state.ts | 29 | 0 |
| Modul tanpa api.php | 4 | 0 |
| Modul tanpa web.php | 2 | 0 |
| Clinical\Ancillary scaffold | 3 files (models only) | 16 files lengkap |
| Clinical\Classification scaffold | 1 file (model only) | 8 files lengkap |


#### Audit Import .vue (Selesai)
- Scan 152 .vue files untuk violation import VM dan state.
- 2 real violation ditemukan dan diperbaiki:
  1. `Analytics\Reports\presentations\views\DashboardIndex.vue` - import dari path `vm/` yang sudah dihapus → difix ke `viewmodels/`
  2. `Operational\Master\Http\Controllers\WebLookUpController::index()` - render ke `Inventory/presentations/views/LookupIndex` (salah modul) → difix ke `Master/presentations/views/LookupIndex`; file duplikat `Inventory\LookupIndex.vue` dihapus
- Dikonfirmasi bukan violation:
  - `import type { X } from '...state'` untuk `defineProps` - TypeScript type-only import yang sah
  - `lov_set.vm` cross-module - sudah di-cover exception rule Section 20.14
  - `DataTable.vm` dari `Core\shared` - shared layer boleh diimport semua modul
- Hasil akhir: 152 .vue files BERSIH ✅

#### State Sharing Refactor -- Operational\Sales Transaction (Selesai)
- 5 state files yang sebelumnya re-export dari `transaction.state.ts` sudah dipecah menjadi definisi mandiri:
  - `sales_transaction.state.ts` -- `SalesTransactionFilters`, `SalesTransactionMeta`, `SALES_TRANSACTION_DEFAULT_FILTERS`, `SALES_TRANSACTION_PER_PAGE_OPTIONS`
  - `purchase_transaction.state.ts` -- `PurchaseTransactionFilters`, `PurchaseTransactionMeta`, `PURCHASE_TRANSACTION_DEFAULT_FILTERS`, `PURCHASE_TRANSACTION_PER_PAGE_OPTIONS`
  - `receivable_transaction.state.ts` -- `ReceivableTransactionFilters`, `ReceivableTransactionMeta`, `RECEIVABLE_TRANSACTION_DEFAULT_FILTERS`, `RECEIVABLE_TRANSACTION_PER_PAGE_OPTIONS`
  - `payable_transaction.state.ts` -- `PayableTransactionFilters`, `PayableTransactionMeta`, `PAYABLE_TRANSACTION_DEFAULT_FILTERS`, `PAYABLE_TRANSACTION_PER_PAGE_OPTIONS`
  - `examination_transaction.state.ts` -- `ExaminationTransactionFilters`, `ExaminationTransactionMeta`, `EXAMINATION_TRANSACTION_DEFAULT_FILTERS`, `EXAMINATION_TRANSACTION_PER_PAGE_OPTIONS`
- 5 VM files diupdate import ke state masing-masing (tidak lagi import dari `transaction.state`)
- 5 Vue files diupdate import ke state masing-masing
- `transaction.state.ts` (shared/orphan) dihapus
- Hasil akhir: 0 file yang import dari `transaction.state` ?
### 17.17 Progress Log - 2026-09-16 (Sesi 2)

#### State Sharing Refactor - Operational\Sales Transaction (Selesai)
- 5 state files yang re-export dari `transaction.state.ts` dipecah jadi definisi mandiri masing-masing
- 5 VM files + 5 Vue files diupdate import ke state miliknya sendiri
- `transaction.state.ts` (shared orphan) dihapus
- Hasil: 0 file import dari `transaction.state` ✅

#### CRUD Controller Tests - Real Data (Selesai)
Tests baru yang dibuat (semua real data, no mock, data cleanup via RefreshDatabase rollback):
- `tests/Feature/Controllers/Organization/CompanyControllerTest.php` - 6 tests ✅
- `tests/Feature/Controllers/Organization/BranchControllerTest.php` - 7 tests ✅
- `tests/Feature/Masters/LocationControllerTest.php` - 9 tests (ganti mock jadi real data) ✅
- `tests/Feature/Controllers/MasterData/LookupControllerTest.php` - 11 tests (lov/types + lov/sets) ✅

Tests yang sudah ada dan tetap hijau:
- `UnitControllerTest` - 7 pass, 4 skip (isolation belum enforce di controller)
- `TaxControllerTest` - 9 pass ✅
- `PaymentTermControllerTest` - 9 pass ✅
- `WarehouseControllerTest` - 7 pass ✅

Total: 64 passed, 5 skipped, 0 failed

#### Bug Fixes Ditemukan & Diperbaiki
- `BranchRepository::getPageData()` tidak filter by `company_uuid` → ditambahkan filter
- `BranchController::show()` memanggil `getByUuid()` yang tidak ada → difix ke `show()`
- `BranchController::index()` tidak pass `company_uuid` dari auth user → difix
- `TestCase::migrateFreshUsing()` tidak include path migration modules → ditambahkan 19 path module
- `migration add_is_super_admin_to_users` duplikat kolom yang sudah ada di `create_users_table` → ditambahkan `hasColumn` guard
- `PaymentTerm::newFactory()` salah namespace (`Master` → `Operational`) → difix
- `LookupTypeFactory` dan `LookupValueFactory` salah model namespace → difix ke `Core\Platform`
- `LookupSetFactory` salah model namespace → difix ke `Operational\Inventory`
- Tax dan PaymentTerm tidak punya `GET /{uuid}` route → ditambahkan
- `LookupRepository` tidak punya `deleteType` method → ditambahkan ke interface, repo, service, controller, route
- `LookupSetRepository::storeSet()` hardcode `company_uuid = ''` → difix pakai `$entity->company_uuid`
- `LookupSetRepository.php` line 46 corrupt akibat PowerShell string expansion → difix manual

#### Summary Akhir 2026-09-16 Sesi 2
| Item | Status |
|---|---|
| CRUD test Company | ✅ 6/6 pass |
| CRUD test Branch | ✅ 7/7 pass |
| CRUD test Location/Gudang | ✅ 9/9 pass (real data) |
| CRUD test Lookup (lov/types + lov/sets) | ✅ 11/11 pass |
| CRUD test Unit | ✅ 7/7 pass (4 skip - isolation belum enforce) |
| CRUD test Tax | ✅ 9/9 pass |
| CRUD test PaymentTerm | ✅ 9/9 pass |
| CRUD test Warehouse | ✅ 7/7 pass |
| Total | 64 passed, 5 skipped, 0 failed |

1. ✅ Gudang
2. ✅ Satuan
3. ✅ Pajak
4. ✅ Payment Term
5. ✅ Daftar Nilai (Lookup)
6. ✅ Perusahaan
7. ✅ Cabang
8. ✅ Supplier

### 17.18 Progress Log - 2026-09-22

#### Plan Update & Gap Audit (Selesai)
- Keputusan report: laporan masuk `Analytics\Reports` (bukan Finance\Reports); Section 4.4, 4.5, 19.2 (row 44-47), 20.5, 20.9 diupdate.
- Gap audit: 4 dari 6 temuan tercentang setelah verifikasi codebase.

#### Migration & Model PR/GR (Selesai, commit 785fe5e46)
- Migration `purchase_requisitions` (+items) dan `goods_receipts` (+items) di Operational\Procurement.
- Model: PurchaseRequisition, PurchaseRequisitionItem, GoodsReceipt, GoodsReceiptItem.
- Test migration baru: 8 passed, 12 assertions; php -l lolos; autoload 4 model OK.

#### Diskusi Multi-Org Task #9 -- KEPUTUSAN (Eksekusi Belum Dimulai)
- Audit blast radius: 230 akses langsung kolom legacy `users.company_uuid`/`users.branch_uuid`; fallback 3 lapis (User accessor, CompanyScopeResolver, controller baca kolom langsung).
- Temuan keamanan: header `X-Klinik-Company-Uuid` hanya divalidasi `companyExists()` TANPA cek membership pivot; `userHasAccessToCompany()` tidak pernah dipanggil di jalur resolusi -> risiko tenant isolation bypass via header spoofing.
- Temuan performa: accessor 2 query pivot per panggilan; EncounterController 22x konteks/request (~44 query redundan).
- Keputusan 3 poin: (1) soft deprecate; (2) kolom di-DROP di akhir, bukan cache permanen (konteks aktif per-request, tidak bisa diwakili kolom statis); (3) resolusi via middleware `ResolveCompanyContext` sekali per request + helper `current_company_uuid()`/`current_branch_uuid()`.
- Rencana eksekusi 6 langkah tercatat di Section 16.3.6; task #9 Section 18 status DECIDED.

### 17.19 Todo Eksekusi Task #9 Multi-Org (dimulai 2026-09-22)

> Referensi keputusan: _plan.md Section 16.3.6 (soft deprecate; drop kolom di milestone akhir; resolusi via middleware). TANPA auto-commit.

| # | Task | Status |
|---|------|--------|
| 1 | Middleware `ResolveCompanyContext` (app/Shared/Http/Middleware) + value object `CompanyContext` + helper `current_company_uuid()`/`current_branch_uuid()` via composer autoload files | SELESAI |
| 2 | Rewire `CompanyScope`/`BranchScope` baca context; resolver attributes-first; `userHasAccessToCompany` fallback diperketat (hanya legacy kolom milik user); tambah `userHasAccessToBranch`; accessor User jadi wrapper -- header TIDAK lagi dibaca langsung di accessor (tutup celah spoofing) | SELESAI |
| 3 | Observer `UserContextSyncObserver` (pivot is_default -> kolom legacy users, saved+deleted re-sync); register di AppServiceProvider; fix model pivot `UserCompany`/`UserBranch` (`const UPDATED_AT = null` -- tabel pivot tidak punya kolom updated_at) | SELESAI |
| 4 | Migrasi akses langsung `$user->company_uuid`/`$user->branch_uuid` ke helper -- 131 titik di 22 file | SELESAI (17.36) |
| 5 | Seeder/Import tulis pivot dulu: RuntimeUserSeeder + UserImport (SuperadminSeeder sengaja tanpa pivot -- superadmin tidak diikat company sesuai plan 20.12) | SELESAI |
| 6 | Milestone akhir: hapus fallback legacy accessor+resolver; migration drop kolom/FK/index `users.company_uuid`/`users.branch_uuid` | SELESAI (17.36) |

Test baru: `tests/Feature/Infrastructure/ResolveCompanyContextMiddlewareTest.php` -- 7/7 PASSED (default pivot context, 403 company tanpa membership, switch context via header member, 403 branch lintas company, legacy user tanpa pivot tetap bisa, superadmin exempt, observer sync pivot->users). Regresi: Lookup + Sidebar + Rbac gabungan = 23 passed, 1 skipped, 0 failed. Catatan: `AuthControllerTest` gagal HANYA jika dijalankan setelah test lain dalam satu proses (PDO transaction nesting, SQLite :memory:) -- terverifikasi pre-existing via git stash (gagal juga tanpa perubahan task #9).
Catatan: UserEntity/UserRepository pivot-first menyusul saat migrasi Core\Identity (masuk langkah 4).

#### Langkah 4 -- Encounter Module (Selesai 2026-09-22)

- `EncounterController` tidak lagi membaca `users.company_uuid`/`users.branch_uuid` langsung.
- Seluruh konteks memakai `current_branch_uuid()`/`current_company_uuid()`; fallback ke accessor User hanya untuk transisi/non-HTTP.
- Verifikasi: 0 akses legacy langsung tersisa di `modules/Clinical/MedicalRecord`; `php -l` lolos; `EncounterControllerTest` 7 passed (19 assertions).
- Scan global akses legacy langsung turun menjadi 238 kemunculan pada 34 file non-test.

### 17.20 Refactor Import Path Global (2026-09-22)

- Codemod tokenizer `scripts/refactor-imports.php` mengubah 113 file PHP: FQN inline (`\App\...`, `\Database\...`, `\Tests\...`) dipindah ke use statement di atas file.
- String, docblock, grouped use, dan use function/const dijaga tetap utuh; nama kelas/alias collision dideteksi dan alias `as` dipertahankan.
- Perbaikan pasca-audit: `TestCase as BaseTestCase` dan `BranchRepository as AccountingBranchRepo` dikembalikan agar referensi runtime tidak hilang.
- Verifikasi: `php -l` otomatis pada setiap file hasil codemod; `--dry-run` berikutnya menunjukkan 0 file tersisa; regresi fokus middleware/lookup/sidebar/rbac/soft-delete/unit dijalankan.

### 17.21 Refactor Controller Ke Service/Repository (2026-09-22)

#### Batch 1 - Controller Dengan Operasi Data Aktif (Selesai)

- `WebAuthController`: lookup perusahaan aktif dipindah ke `CompanyService -> CompanyRepository`; repo mendapatkan `findFirstActive()`.
- `WebAccountingController`: operasi periode akuntansi, akun induk/sub akun, dan hapus transaksi dipindah ke `AccountingCycleService`, `AccountService`, dan `JournalService`.
- `GeneralLedgerController`: query daftar, resolusi periode, dan filter export dipindah ke `GeneralLedgerService -> GeneralLedgerRepository`; filter dikapsulkan di `GeneralLedgerFilterEntity`.
- Hasil scan ketiga controller: 0 pemakaian `Model::`, `::query()`, `DB::`, `Rule::exists`, atau `Rule::unique`.
- Regresi: Company/Branch/Account/Journal/GeneralLedger controller test = 25 passed, 62 assertions.

#### Batch 2 - Analytics Reports Scope (Selesai)

- `ReportScopeService` baru menangani resolusi/validasi `ClosingPeriod` dan `Branch` berdasarkan company aktif.
- `FinancialReportController`, `ReportController`, dan `WebCashFlowController` sudah tidak membaca Model langsung.
- Repo diperluas: `AccountSettingRepository::findPeriodByUuidAndCompany()` dan `BranchRepository::findByUuidAndCompany()`.
- Regresi: FinancialReport/Report/CashFlow controller test = 10 passed, 27 assertions.

#### Batch Berikutnya (Belum)

- Controller CRUD dengan `Rule::exists`/`Rule::unique`: Encounter, Registration, Account, Journal, Purchase, Sale, Service, ServicePackage. Validasi semantik ini perlu dipindah ke Service/FormRequest pada batch lanjutan.

### 17.22 Refactor Validasi & Query Layers (2026-09-23)

#### Controller Batch 3 (Selesai)

- Validasi semantik `Rule::exists`/`Rule::unique` dipindah dari controller ke Service, lalu Service memanggil Repository untuk pengecekan data.
- Controller yang dibersihkan: Encounter, Registration, Account, Journal, Purchase, Sale, Service, ServicePackage, Employee, dan CashFlow.
- Endpoint create controller yang mengembalikan resource baru dikembalikan dengan HTTP 201 agar sesuai kontrak test.
- Scan controller aktif: 0 pemakaian `Model::`, `::query()`, `DB::`, `Rule::exists`, `Rule::unique`, atau string `exists:`.

#### Service & Boundary Batch (Selesai)

- Service tidak lagi memakai `Rule::exists`, `Rule::unique`, `DB::`, atau query Eloquent langsung; pengecekan duplikat/keberadaan data didelegasikan ke Repository.
- `FinancialReportQueryService` dan `TrialBalanceQueryService` ditambahkan sebagai facade repository Finance untuk Analytics, sehingga Analytics tidak lagi import Repository lintas modul.
- `BranchService` dipakai sebagai gateway lintas modul untuk lookup/daftar branch; `AuditLogService` dipakai oleh base Controller untuk audit trail.
- Verifikasi boundary: `php scripts/check-module-boundaries.php --strict` = OK (0 pelanggaran).

#### Regresi (Selesai)

- Regresi layer/controller terkait: 68 passed (172 assertions).
- Regresi service terkait: 33 passed (58 assertions).
- `php -l` lolos untuk 124 file PHP dalam diff.

### 17.23 Seeder Modular & Model Access (2026-09-23)

#### Legacy Product Data Cleanup (Selesai)
- Semua penamaan legacy dihilangkan dari nama file, class, output, path data, dan data kode produk di Laravel `src`.
- `legacy_products_main.json` direname menjadi `master_products.json`.
- Product code prefix pada seed source diubah menjadi `PRD-`.
- Verifikasi: `rg -n -i "legacy_product" src --glob '!vendor/**' --glob '!node_modules/**' --glob '!storage/framework/sessions/**'` bersih.

#### Split Seeder Per Domain (Selesai)
- Seeder data produk legacy yang terpisah dihapus.
- Data seed dipindah sesuai ownership domain:
  - `Operational\Master`: `ProductDataSeeder` + `SupplierDataSeeder`.
  - `Operational\Procurement`: `PurchaseDataSeeder`.
  - `Finance\Ledger`: `PayableDataSeeder`.
  - `Operational\Inventory`: `StockMovementDataSeeder`.
- Autoload seeder untuk `Finance\Ledger` dan `Operational\Procurement` ditambahkan ke `composer.json`.

#### Seeder Model Migration (Selesai)
- Semua `DB::table()` pada seeder modul diganti Eloquent Model.
- Seeder yang dibersihkan meliputi Patients, SATUSEHAT, BPJS, ICD-10, chart of accounts, journal, closing period, purchase journal, bootstrap truncate, dan test-data cleanup.
- Model pendukung ditambahkan: `BpjsSetting` dan `ServicePackageItem`; `Patient` dilengkapi fillable `nik`.
- Verifikasi: `php -l` lolos untuk file PHP yang berubah; `composer dump-autoload` berhasil; `rg -n "DB::table" src/modules -g '*Seeder.php'` = 0.

### 17.24 Product UI/UX Refinement (2026-09-23)

- Menyederhanakan header Product Index dan memindahkan tombol kembali ke area heading sebagai aksi kecil.
- Menyeragamkan tinggi kontrol toolbar DataTable (search, filter kolom, per-page) serta memberikan fokus visual yang konsisten.
- Memperbaiki density, alignment, radius, border, dan efek hover pada tabel.
- Merapikan grid modal filter Product dengan jarak, tinggi field, fokus state, divider aksi, dan responsivitas yang lebih konsisten.
- Merapikan layout Product Form menjadi grid empat kolom untuk detail field dan tombol aksi pada layar kecil.
- Mengaktifkan nilai default pada pilihan Satuan, Kelompok, Jenis, Tipe, Merek, Made-In, dan Grade dengan opsi pertama.

### 17.25 Product View To VM Refactor (2026-09-23)

- Memindahkan logika filter perusahaan, opsi filter, validasi filter aktif, dan format rupiah dari `ProductIndex.vue` ke `product.vm.ts`.
- Memindahkan inisialisasi form, default select option, preview gambar, submit, dan cancel dari `ProductForm.vue` ke `product.vm.ts`.
- `ProductModel` dilengkapi properti `image` agar data gambar produk dapat dipakai VM secara konsisten.
- Verifikasi: `npm run build` berhasil; `npm run typecheck` hanya menampilkan error lama pada `kanban.view.spec.ts`.

### 17.26 Batch Vue View To VM Refactor (2026-09-23)

#### View Logic Migration (Selesai)

- Memindahkan inisialisasi query tab dan ekspansi menu dari `ReportIndex.vue` dan `InventoryIndex.vue` ke VM masing-masing.
- Memindahkan pagination links, form mode, state modal, dan submit Item Conversion ke `item_conversion.vm.ts`.
- Memindahkan mapping opsi select, formatter tanggal, URL query, detail resep, subtotal/grand total, dan aksi cetak ke VM terkait.
- Memindahkan filter role, toggle permission, avatar formatter, mode form, file restore, CSRF/selected role, dan listener dismiss import menu ke VM terkait.
- Menambahkan helper reusable `import_menu.ts` agar VM yang memakai import menu memakai alur dismiss yang konsisten.
- Membersihkan pemakaian `document.addEventListener`, `window.location`, transformasi data, dan submit/form logic dari view yang terdampak.
- Memperbaiki duplikasi `downloadImportTemplate` pada `farmacy.vm.ts` dan `user.vm.ts`.

#### Validation (Selesai)

- `npm run build` berhasil.
- `npm run typecheck` hanya menampilkan error lama pada `kanban.view.spec.ts` (`window.Livewire` tidak terdeklarasi pada test).

### 17.27 Product Filter & VM State Refinement (2026-09-23)

#### Backend Product Filter (Selesai)

- Menggunakan import `Carbon` di `ProductRepository` untuk filter periode start/end of month.
- Menambahkan whitelist sorting pada query produk melalui `sort_by` dan `sort_direction`.
- Memastikan filter `filter_unit` menerima UUID maupun nama satuan.
- Superadmin hanya memakai filter perusahaan yang dipilih secara eksplisit; default perusahaan implisit dihapus.

#### Frontend VM & E2E Support (Selesai)

- Memisahkan state `lookup_value` ke `createLookupValueState()` di file state terpisah.
- Memperbaiki race request pada `AsyncSelect2` dengan guard sequence.
- Memperbarui fixture E2E produk ke prefix `AAA-E2E-FILTER`.
- Menambahkan command persiapan data E2E dan memperluas cleanup data E2E.
- Memperbaiki setup Pinia dan mock `usePage` pada test `lookup_value`.

#### Validation (Selesai)

- `php artisan test --filter='ProductRepositoryTest|ProductEntityTest'`: 13 passed, 85 assertions.
- `npm run typecheck`: passed.
- `npx vitest run modules/Operational/Master/tests/lookup/presentation/viewmodels/lookup_value.vm.spec.ts`: 5 passed.
- E2E Product Filter: 25 passed.
- `npm test`: 371 passed, 53 failed; seluruh kegagalan adalah issue lama berupa 49 UI/UX consistency dan 4 validasi login/forgot password.

### 17.28 Product File Policy & UX Refinement (2026-09-24)

#### Product File Policy (Selesai)

- Menambahkan keterangan di form produk bahwa file pertama menjadi file utama dan tampil pada daftar produk.
- Menampilkan badge `File utama` pada preview pertama dan menandainya dengan border biru.
- Memastikan file pertama disimpan sebagai `items.image`; jika file pertama dihapus, file berikutnya otomatis menjadi file utama.
- Menambahkan validasi gabungan existing + upload baru agar total gambar per produk maksimal 10.
- Membatasi penambahan preview di VM hingga maksimal 10 gambar.
- Memperluas dukungan file menjadi `jpg`, `jpeg`, `png`, `webp`, atau `pdf` dengan maksimal 2 MB per file.
- Menolak tipe lain seperti `.php` di frontend dan backend; frontend menampilkan peringatan SweetAlert sebelum submit.
- Menampilkan nama dan ukuran file pada preview, menyediakan tombol hapus semua, dan tombol `Jadikan File Utama`.
- Menampilkan peringatan SweetAlert untuk file lebih dari 2 MB dan penambahan lebih dari 10 file.
- Menonaktifkan tombol simpan saat upload atau submit berlangsung.
- Memvalidasi `existing_image_paths` agar hanya boleh berasal dari produk yang sedang diedit.
- Menghapus file fisik pada storage ketika file tersebut tidak lagi dipakai produk.
- Menambahkan test service, controller, dan E2E untuk upload file, reorder file utama, dan validasi batas upload.

#### Documentation (Selesai)

- Menambahkan kebijakan upload file produk pada `_plan.md`.
- Mencatat implementasi kebijakan tersebut pada progress log `_progress.md`.

### 17.29 Product Form Upsert & Tab Field Support (2026-09-24)

#### Product Upsert Refactor (Selesai)

- Refactor `ProductController` dan `WebProductController` agar mengikuti pola Controller -> Service -> Repository.
- Menambahkan dukungan penyimpanan field form utama dan seluruh tab produk melalui `ProductRepository::syncProductRelations()`.
- Memastikan relasi `accounts`, `units`, `prices`, `opening_balances`, dan `discounts` tersimpan pada alur create/update.
- Memindahkan kolom tab tambahan (`item_units`, `item_prices`, `item_discount_rules`) ke migration utama `2026_04_05_000100_create_item_support_tables.php`.
- Menghapus kebutuhan migration terpisah `2026_09_24_000001_add_product_form_tab_fields.php`.

#### UI/UX Product Form (Selesai)

- Merapikan tampilan tab agar lebih responsif dan konsisten.
- Menyamakan tampilan dan tinggi input/select/checkbox pada tabel tab.
- Memperbaiki dropdown dan kenyamanan tabel tab pada layar kecil.

#### Testing & Cleanup (Selesai)

- Menambahkan PHP feature test real-data pada `ProductFormUpsertTest.php` untuk create, update, dan verifikasi relasi tab.
- Menambahkan unit test payload frontend pada `product.repo_impl.spec.ts`.
- Menambahkan Playwright E2E real UI pada `product.form.upsert.view.spec.ts`.
- Menambahkan command `PrepareProductFormE2eData` dan memperluas `CleanupE2eData` untuk fixture `E2E-FRM-WH`, `E2E-FRM-PL`, serta produk `E2E-FRM-%`.
- Verifikasi: PHP feature test 1 passed (9 assertions), frontend payload test 1 passed, Playwright E2E 1 passed.
- Konfirmasi cleanup real-data: 0 item, 0 location, 0 price level.

### 17.30 Product Edit Hydration, Duplicate Code & Relation Upsert (2026-09-24)

- Memperbaiki hydration form edit agar field relasi tab yang tersimpan tidak kembali ke nilai default atau hilang.
- Memuat kembali gambar, catatan/status satuan, seluruh field harga, satuan diskon, dan `base_qty` diskon.
- Menambahkan fixture seeder idempotent untuk produk `97282e88-883b-4ee2-b7fc-c9a44cfce252` agar seluruh tab memiliki data uji edit.
- Menambahkan input status aktif pada tab satuan dan input periode pada tab saldo awal.
- Mengubah sinkronisasi relasi produk menjadi update/insert berdasarkan UUID atau kunci relasi, serta menghapus baris yang dihapus dari form.
- Menambahkan toast error berbahasa Indonesia ketika create memakai kode produk yang sudah digunakan.
- Menambahkan regresi duplicate-code dan hydration relasi pada PHP feature test.
- Verifikasi: produk fixture memiliki 1 akun, 1 satuan, 1 harga, 1 saldo awal, dan 1 diskon; `ProductFormUpsertTest` 2 passed (20 assertions).

### 17.31 Product Export/Import, Dynamic Row Validation, Vue Architecture & Seeder Path Cleanup (2026-09-25)

#### Product Export/Import (Selesai)

- Implementasi API export CSV (`GET /api/v1/products/export`), download template (`GET /api/v1/products/import/template`), dan import CSV (`POST /api/v1/products/import`) di `ProductController`.
- Import mendukung upsert by `code`, pelaporan error per baris, dan validasi numeric/boolean.
- Tambah UI tombol Import, Template, dan Export di `ProductIndex.vue` dengan handler file upload di `product.vm.ts`.
- Perbaiki path export di `product.repo_impl.ts` dari `/master/products/export` ke `/products/export`.

#### Dynamic Row Validation (Selesai)

- Tambah validasi duplikasi baris di backend (`validateNestedRows()`) untuk: satuan, akun (by role), gudang/periode saldo, level harga, dan diskon.
- Tambah validasi kombinasi diskon persen dan nominal agar tidak diisi bersamaan.
- Tambah validasi duplikasi baris di frontend (`validateTabRows()` di `product.vm.ts`) dengan SweetAlert feedback.
- Tambah PHP feature test `test_nested_rows_reject_duplicates_and_mixed_discount_values` dan `test_nested_rows_reject_remaining_duplicate_types` pada `ProductFormUpsertTest`.

#### Refresh Dropdown Options Tanpa Reload Penuh (Selesai)

- `product_form.vm.ts` sekarang memakai `router.reload({ only: ['options'] })` dengan watcher `options` untuk memperbarui `Select2ClientSide` secara reactive.
- Semua dropdown di `ProductForm.vue` beralih dari `props.options` ke `formVm.options` agar otomatis ter-refresh.

#### E2E Upload File Test (Selesai)

- Tambah Playwright E2E test untuk skenario upload gambar + PDF, reorder file utama, hapus file, batas 10 file, file >2 MB, dan file dengan ekstensi tidak didukung di `product.form.upsert.view.spec.ts`.

#### Vue Architecture Refactor (Selesai)

- Pindahkan konstanta bisnis (`PRODUCT_MAX_IMAGE_ENTRIES`, `PRODUCT_MAX_FILE_SIZE`, `PRODUCT_ALLOWED_FILE_TYPES`, `PRODUCT_ALLOWED_FILE_EXTENSIONS`, `PRODUCT_TABLE_COLUMNS`) dari inline VM ke `product.state.ts`.
- Pindahkan `isPdfFile()` dan `columns` dari `ProductIndex.vue` ke VM/state.
- `ProductIndex.vue` kini purely presentational: hanya import VM, panggil `vm.initFilters()`, render `vm.tableColumns` dan `vm.isPdfFile()`.
- VM tetap Pinia store `defineStore` setup style.

#### BMR Seeder Removal (Selesai)

- Hapus `BmrDataSeeder.go`, `bmr_products.json`, dan `bmr_data_seeder_test.go`.
- Bersihkan semua referensi `SeedBmrData` dan `Seeder_BmrDataSeeder` dari `V1DatabaseSeeder.go`, `registry.go`, `clinic_seeder_profile_test.go`, dan `seeder_uuid_non_empty_contract_test.go`.
- Update komentar `SimulasiTransaksiSeeder.go` untuk menghapus referensi BmrDataSeeder.

#### Seeder External Path Cleanup (Selesai)

- Copy 3 file JSON dari `be/database/data/` ke `src/database/data/`: `operational_purchases.json`, `operational_invoice_items.json`, `excel_akutansi_saldo.json`.
- Hapus fallback `base_path('../be/database/data/')` dari `ProductDataSeeder.php`, `PurchaseDataSeeder.php`, dan `PayableDataSeeder.php`; `dataPath()` sekarang hanya pakai `database_path('data/')`.
- Ubah path `Icd10Seeder.php` dari `base_path('../catatan/...')` ke `database_path('data/ICD-10 e-klaim.xlsx')` (file xlsx belum ada, seeder skip gracefully).
- Hasil audit: 0 referensi `../be` atau `../catatan` tersisa di semua PHP seeder.

#### Verifikasi (Selesai)

- PHP: `ProductFeatureTest` + `ProductFormUpsertTest` — 14 passed (65 assertions).
- Playwright E2E: 2 passed (form upsert semua tab + upload file).
- `npm run typecheck` — passed.
- `npm run build` — passed.
- `go build ./...` — passed.
- Go seeder tests — passed.
- Commit: `eeac165db` di branch `haidi`.

#### Temuan ICD-9/ICD-10 Architecture (Catatan)

- ICD-10 tetap di dedicated table `icd10_codes`, tidak dipindah ke `lookup_sets`/`lookup_values` karena volume 14.000+ baris akan mendominasi `lookup_values` dan merusak performa query reference data kecil.
- ICD-9 belum ada table sama sekali. Rekomendasi: buat dedicated table `icd9_codes` dengan pola sama seperti `icd10_codes`.
- Encrypted FK `encounter_diagnoses.icd10_code` → `icd10_codes.code` harus dipertahankan jika table dedicated digunakan.

#### ICD-9 Dedicated Table (Selesai)

- Buat migration `2026_09_25_000001_create_icd9_codes_table.php` dengan unique `code` dan index nama Indonesia/Inggris.
- Buat model `Icd9Code` di modul `Clinical\Classification` mengikuti pola `Icd10Code`.
- Buat seeder `Icd9Seeder` yang idempoten, membaca `database/data/icd9_codes.json`, lalu daftarkan di `V1DatabaseSeeder`.
- Ambil file `[PUBLIC] ICD-9CM e-klaim.xlsx` dari branch `main` ke `src/database/data/`; seeder kini membaca Excel tersebut dan berhasil mengisi 4.626 baris.

### 17.32 Multi-org Langkah 4 -- Migrasi Legacy Context Access (2026-09-29)

#### Migrasi $user->company_uuid / $user->branch_uuid ke Helper (Selesai)

- Migrasi 131 titik akses langsung di 22 file non-test ke current_company_uuid() / current_branch_uuid().
- Pola replacement: $user->company_uuid ?: ->branch?->company_uuid -> current_company_uuid(), $user->company_uuid ?? ->branch?->company_uuid -> current_company_uuid(), dst.
- File yang dimigrasi: 18 controller, 2 service, 1 import, 1 base controller.
- Distribusi per modul: Operational\Master (73), Operational\Manufacturing (12), Operational\Procurement (10), Clinical (24), Operational\Sales (7), Finance (2), Analytics (2), Core (1).
- File di-exclude (intentional): ResolveCompanyContext.php + CompanyScopeResolver.php (implementasi fallback sendiri, dihapus di langkah 6), UserImport.php ($user adalah model baru yang di-import, bukan auth user), PrepareProductFormE2eData.php (baca dari DB query artisan, bukan konteks HTTP).
- Verifikasi: 0 pemakaian $user->company_uuid/$user->branch_uuid tersisa di modules (hanya 4 file yang di-exclude); php -l lulus untuk semua 22 file.

#### Vue Architecture Audit -- View to VM Refactor (Selesai)

- Scan 161 file .vue di seluruh src/modules/ untuk deteksi logic yang bocor dari VM/state.
- Ekstrak 22 blok computed pagination/filter/sort client-side dari DataTable.vue ke composable baru DataTable.client.ts.
- Ganti mutasi langsung m.triggerReload = () => ... menjadi m.registerReload(...) di 8 view (Company, Branch, Supplier, Warehouse, Unit, Tax, PaymentTerm, Lookup) dengan method 
egisterReload() baru di masing-masing VM.
- Ganti mutasi langsung m.backups = props.recentBackups menjadi m.setBackups() di ackupIndex.vue.
- Inventory: filter semua tab agar hanya tampilkan item bersubmenu dan auto-expand saat klik tab.
- Hasil scan ulang: 0/161 file .vue dengan pelanggaran logic. Typecheck ue-tsc bersih.

#### Inventory Tab Submenu Filter (Selesai)

- Semua tab Inventory (utama, pembelian, penjualan) kini hanya menampilkan item yang punya submenu. Tab tanpa item bersubmenu tampilkan semua item normal.
- Submenu auto-expand saat klik tab via fungsi expandAll() di inventory_index.vm.ts.
- Klik tab memanggil expandAll() bukan clearExpanded().

### 17.33 Perencanaan Performansi & Skalabilitas (2026-09-29)

#### Keputusan Bahasa & Arsitektur (Selesai)

- Diskusi: apakah perlu ganti dari Laravel/PHP?
- Keputusan: **Tetap Laravel 12 (PHP 8.3)**. Tidak ganti bahasa.
- Alasan: 100 user aktif harian (~30 konkuren) masih jauh dari threshold Laravel. Dengan Redis + Queue + Index + OPcache/JIT, Laravel menangani 500-1000 req/s.
- Ganti bahasa hanya jika tercapai trigger Fase 2/3 (user > 200 konkuren atau fitur real-time dense).
- Rencana lengkap ditulis di _plan.md Section 24 (Performance & Scalability Plan).

#### Ringkasan Rencana Performansi

- Fase 1 (immediate, 100 user): Redis cache, Horizon queue, database compound index, PHP 8.3 OPcache+JIT
- Fase 2 (500+ user): horizontal scaling (load balancer + multi app server), read replica, CDN, monitoring
- Fase 3 (1000+ user / real-time): microservice untuk fitur tertentu saja (notification via Node.js, heavy compute via Go) -- Laravel tetap master
- Gate kriteria terukur: P95 < 200ms (Fase 1 selesai), P95 > 300ms konsisten (trigger Fase 2), real-time dense (trigger Fase 3)
- Anti-pattern: jangan premature optimization, jangan microservice domain bisnis, jangan cache tanpa invalidasi

### 17.34 Audit Performansi -- Status vs Section 24 (2026-09-29)

#### Audit Aktual Codebase (Selesai)

- Scan .env.example: CACHE_STORE=file, SESSION_DRIVER=file, QUEUE_CONNECTION=sync -- Redis belum dikonfigurasi.
- Scan composer.json: tidak ada laravel/horizon, laravel/telescope, laravel/pulse, sentry/sentry-laravel. PHP constraint ^8.2 (belum 8.3).
- Scan migration: 0 compound index company_uuid+branch_uuid+deleted_at di semua tabel tenant-owned.
- Scan ShouldQueue: hanya 1 file (ProcessAutoReversalJob.php) dengan driver sync (tidak benar-benar queue).
- Scan config: config/cache.php, config/session.php, config/queue.php sudah punya opsi redis (tinggal ganti .env).

#### Kesimpulan

- Fase 1 Section 24 belum dieksekusi sama sekali di codebase.
- Rencana Section 24 sudah lengkap (Redis, Horizon, Index, OPcache/JIT, Monitoring), tinggal implementasi.
- Prioritas: task #1-5 di Section 24.8 (redis, cache, horizon, index, PHP config).

### 17.35 Rencana Laravel Reverb + Async Integration (2026-09-29)

#### Keputusan Teknologi (Selesai)

- Diskusi: apakah perlu Node.js microservice untuk WebSocket real-time dan SATUSEHAT/BPJS async?
- Keputusan: **Tidak perlu Node.js/Go untuk saat ini**. Laravel 12 punya solusi native:
  - **Laravel Reverb** (gratis, first-party, open source) untuk WebSocket server
  - **Laravel Horizon** untuk queue monitoring
  - **Laravel Queues (Redis)** untuk async SATUSEHAT/BPJS processing
- Reverb cukup untuk 500-1000 koneksi WS konkuren (satu server). Ganti ke Node.js (Socket.io) hanya jika > 1000 koneksi atau sub-50ms latency.
- Rencana lengkap ditulis di _plan.md Section 25.

#### Ringkasan Rencana Section 25

- Arsitektur: Vue 3 SPA -- Laravel API -- Redis (pub/sub + queue) -- Reverb + Horizon
- 5 channel design: company, branch, user, satusehat-sync, dashboard
- 6 broadcastable events: StockLevelChanged, PatientRegistered, EncounterUpdated, SatusehatSyncProgress, DashboardMetricsUpdated, NotificationSent
- SATUSEHAT async flow: webhook receiver -> dispatch job -> broadcast progress
- BPJS async flow: scheduler polling -> dispatch job -> broadcast antrian update
- Estimasi implementasi: ~12.5 hari kerja (7 task)
- Testing: unit (event structure), feature (webhook + channel auth), E2E (Echo mock), load test (k6 100 WS)

### 17.36 Multi-org Langkah 6 -- Drop Kolom Legacy (2026-09-29)

#### Drop Kolom Legacy users.company_uuid / users.branch_uuid (Selesai)

- Refactor ResolveCompanyContext.php: hapus fallback ?: (->company_uuid ?? '') -- resolve hanya dari pivot is_default.
- Refactor CompanyScopeResolver.php: hapus 4 titik fallback legacy (resolveCompanyUuid, resolveBranchUuid, userHasAccessToCompany, userHasAccessToBranch). Akses hanya dari pivot.
- Hapus (empty stub) UserContextSyncObserver.php + hapus registrasi di AppServiceProvider.php -- observer sync pivot->legacy tidak diperlukan lagi.
- Hapus fallback $this->company_uuid / $this->branch_uuid di User.php accessor getActiveCompanyUuidAttribute() / getActiveBranchUuidAttribute().
- Fix UserImport.php: ganti pembacaan $existingUser?->company_uuid/$existingUser?->branch_uuid ke $this->companyUuid/$this->branchUuid (pivot context).
- Fix PrepareProductFormE2eData.php: ganti baca users.company_uuid/users.branch_uuid ke query join user_companies + user_branches (pivot is_default).
- Buat migration 2026_09_29_000001_drop_legacy_company_branch_from_users.php: drop index idx_users_company_deleted + idx_users_branch_deleted, drop kolom company_uuid + ranch_uuid. Down() recreate.
- Verifikasi: 
g '\->(company_uuid|branch_uuid)' non-test = 0 hasil. php -l lulus semua file.

### 17.37 Hapus Fallback Redundan di Controller (2026-09-29)

#### Cleanup Fallback current_company_uuid() ?? \->active_company_uuid (Selesai)

- Middleware ResolveCompanyContext selalu set context untuk user yang sudah login, sehingga helper current_company_uuid()/current_branch_uuid() tidak akan pernah null di HTTP. Fallback ke \->active_company_uuid di controller redundan.
- Hapus 59 fallback redundan di 11 controller:
  - Clinical\Registration\RegistrationController (10)
  - Clinical\MedicalRecord\EncounterController (10)
  - Operational\Sales\ServicePackageController (10)
  - Operational\Sales\ServiceController (6)
  - Operational\Sales\SaleController (6)
  - Finance\Journal\JournalController (6)
  - Finance\Journal\AccountController (6)
  - Operational\Procurement\PurchaseController (2)
  - Analytics\Reports\WebCashFlowController (1)
  - Analytics\Reports\ReportController (1)
  - Analytics\Reports\FinancialReportController (1)
- Scope/Trait (CompanyScope, BranchScope, HasCompanyScope, HasMultiOrgScope) **tidak diubah** -- fallback ke ctive_company_uuid dipertahankan untuk non-HTTP context (artisan/queue/seeder) di mana 
equest() tidak tersedia.
- Verifikasi: php -l lulus semua 11 file.

### 17.38 Multi-org Migration Gap -- Audit Column & Branch Scope (2026-09-30)

#### Identifikasi Gap (Selesai)

- Gap 1: Pivot tables user_companies dan user_branches kurang kolom audit (updated_by, deleted_by, deleted_at).
- Gap 2: Tabel master (employees, suppliers, payment_terms, 	axes) kurang kolom ranch_uuid untuk isolasi per-cabang.
- Rencana lengkap di _plan.md Section 26.
- Status: SELESAI (17.39) -- 9 test lulus (CompoundIndex 2 + MasterBranchScope 3 + PivotAuditColumns 3 + LookupCache 1).

### 17.39 Fase 1 Performansi -- Implementasi TDD (2026-09-30)

#### Compound Index Tenant + Master Tables (Selesai)

- TDD: CompoundIndexTest (2 test) + MasterBranchScopeTest (3 test) -- test dulu merah, lalu implementasi hijau.
- Compound index (company_uuid, branch_uuid, deleted_at) di 7 tabel tenant: items, patients, item_stok, purchase_orders, general_ledgers, sales_invoices, encounters.
- Compound index di 4 tabel master: employees, suppliers, payment_terms, 	axes.
- Single index deleted_at di semua tabel tenant.
- Semua digabung ke migration create table existing (tanpa file terpisah).

#### Cache Lookup Types & Values (Selesai)

- TDD: LookupCacheTest (4 test).
- LookupService::getTypes() pakai Cache::remember('lookup_types', 3600, ...).
- LookupService::getValues() pakai Cache::remember('lookup_values_{code}_{company}', 3600, ...).
- Cache flush terverifikasi invalidasi.

#### Pivot Audit Columns (Selesai)

- TDD: PivotAuditColumnsTest (3 test).
- user_companies + user_branches: tambah kolom updated_at, updated_by, deleted_by, deleted_at (bigInteger unsigned default 0).
- Digabung ke create migration existing.

#### Redis Driver Config (Selesai)

- TDD: PerformanceConfigTest (3 test).
- .env.example + .env: CACHE_STORE=redis, SESSION_DRIVER=redis, QUEUE_CONNECTION=redis.
- Permission cache: config Spatie permission.cache.store sudah pakai default (mengikuti CACHE_STORE).

#### Verifikasi Total

- 21 test lulus (55 assertions) di 6 file test: CompoundIndex, MasterBranchScope, PivotAuditColumns, LookupCache, PerformanceConfig, ModuleBoundary.
- Horizon: SKIP sesuai permintaan (pakai queue:work + Supervisor di production).
- PHP 8.3 + OPcache: DEPLOYMENT (config server, bukan code -- lihat ../deployment-checklist.md).

### 17.40 Reverb Task #1 -- Install + Config (2026-09-30)

#### Install laravel/reverb v1.12 (Selesai)

- composer require laravel/reverb --no-interaction (13 packages: react/event-loop, react/socket, pusher/pusher-php-server, dll).
- php artisan reverb:install --no-interaction: publish config/broadcasting.php + routes/channels.php, generate REVERB_APP_ID/KEY/SECRET di .env.

#### Update .env + .env.example (Selesai)

- .env: BROADCAST_CONNECTION=reverb, CACHE_STORE=redis, SESSION_DRIVER=redis, QUEUE_CONNECTION=redis.
- .env.example: BROADCAST_CONNECTION=reverb + REVERB_APP_ID/KEY/SECRET/HOST/PORT/SCHEME + VITE_REVERB_* forwarded.
- phpunit.xml: BROADCAST_CONNECTION null -> reverb.

#### Frontend Echo Setup (Selesai)

- npm install laravel-echo pusher-js.
- Buat resources/js/echo.ts: initEcho() dengan broadcaster reverb, wsHost/wsPort/wssPort dari VITE_REVERB_*.
- Import echo.ts di app.ts setelah bootstrap.ts.
- Graceful null jika VITE_REVERB_APP_KEY tidak ada (development tanpa Reverb server tetap jalan).

#### Testing TDD (Selesai)

- 6 test di ReverbConfigTest: broadcast_connection_uses_reverb, reverb_credentials_exist, reverb_vite_variables_forwarded, broadcasting_config_supports_reverb, echo_ts_uses_reverb, app_ts_imports_echo.
- Semua 6 test lulus (12 assertions).
- ResolveCompanyContextMiddlewareTest: 7 fail pre-existing (test masih insert company_uuid ke users yang sudah di-drop -- bukan regression Reverb).

#### Horizon

- SKIP sesuai permintaan user. Production pakai queue:work + Supervisor.

### 17.41 Reverb Task #2 -- Echo Channel Authorization (2026-09-30)

#### belongsToCompany / belongsToBranch di User Model (Selesai)

- Tambah User::belongsToCompany(string $companyUuid): bool -- query pivot user_companies.
- Tambah User::belongsToBranch(string $branchUuid): bool -- query pivot user_branches.
- TDD: 4 test method-level (belong/not-belong company/branch) merah -> implementasi -> hijau.

#### Channel Authorization routes/channels.php (Selesai)

- private-user.{uuid}: user->uuid === channel uuid (personal notification).
- private-company.{uuid}: user->belongsToCompany() (company-scoped events).
- private-branch.{uuid}: user->belongsToBranch() (branch-scoped events: stok, pasien, antrian).
- Channel name di Broadcast::channel() tanpa prefix "private-" (Pusher convention; prefix otomatis di sisi frontend).

#### Testing TDD Channel Authorization (Selesai)

- 10 test di ChannelAuthorizationTest: 4 method-level (belongsToCompany/belongsToBranch) + 6 channel-level (auth/deny user/company/branch).
- Semua 10 test lulus (10 assertions).
- Total regression: 36 test lulus (67 assertions) di 7 file test.

### 17.42 Procurement Gap -- purchase_order_items Source Link (2026-09-30)

#### Tambah purchase_requisition_uuid di purchase_order_items (Selesai)

- Kolom: uuid nullable, FK ke purchase_requisitions.uuid ON DELETE SET NULL.
- Index: idx_poi_purchase_requisition_uuid (untuk query PR -> PO trace).
- Null = direct order (tanpa PR), non-null = item dari PR tertentu.
- TDD: 4 test (column exists, nullable, FK constraint, index) merah -> implementasi -> hijau.

### 17.43 Monitoring -- Pulse + Telescope + Predis (2026-09-30)

#### Install laravel/pulse v1.8 (Selesai)

- composer require laravel/pulse --no-interaction (production monitoring: slow query, slow request, exception, server usage).
- Publish config/pulse.php + migration pulse_tables + dashboard view.

#### Install laravel/telescope v5.25 (Selesai, dev-only)

- composer require laravel/telescope --dev (dev debugging: requests, queries, jobs).
- Publish TelescopeServiceProvider + config + migration.
- bootstrap/providers.php: TelescopeServiceProvider hanya terdaftar saat APP_ENV=local (conditional spread).

#### Install predis/predis v3.6 (Selesai)

- PhpRedis extension tidak tersedia di Windows dev machine, jadi pakai predis (pure PHP).
- REDIS_CLIENT=predis di .env + .env.example.
- Production server bisa pakai phpredis (lebih cepat) dengan override env.

#### Testing TDD (Selesai)

- 6 test di MonitoringConfigTest: pulse_installed, telescope_installed, telescope_local_only, pulse_config, pulse_migration, redis_client_predis.
- Semua 6 test lulus.
- Total regression: 35 test lulus (64 assertions) di 8 file test.

### 17.44 Full Test Suite Verification (2026-09-30)

#### Regression Test (Selesai)

- 52 test lulus (98 assertions) di 11 file test:
  - MonitoringConfigTest (6): pulse, telescope, local-only, config, migration, predis
  - PurchaseOrderItemSourceTest (4): column, nullable, FK, index
  - ReverbConfigTest (6): broadcast, credentials, vite, config, echo, import
  - ChannelAuthorizationTest (10): belongsTo + 6 channel auth/deny
  - CompoundIndexTest (2): compound + deleted_at
  - PivotAuditColumnsTest (3): audit columns + bigint
  - MasterBranchScopeTest (3): branch_uuid + compound index
  - PerformanceConfigTest (3): redis drivers + spatie cache
  - LookupCacheTest (4): cache remember + flush
  - ModuleBoundaryTest (6): strict mode + entity/DTO
  - WebAuthServiceBoundaryTest (5): entity return + token
- ResolveCompanyContextMiddlewareTest: 7 fail pre-existing (test insert company_uuid ke users yang sudah di-drop) -- TODO terpisah.

#### Status Semua Todo Coding-Level (Selesai)

| # | Task | Status |
|---|------|--------|
| 1 | PR + GR migration | Sudah ada di codebase |
| 2 | Audit trail company/branch | Sudah ada (resolvers + kolom + index) |
| 3 | purchase_order_items kolom sumber | Selesai (17.42) |
| 4 | Monitoring Pulse + Telescope + predis | Selesai (17.43) |
| 5 | Endpoint new | Skip (rendah, bangun saat ada kebutuhan) |
| 6 | PHP 8.3 + OPcache | Skip (deployment config, bukan code) |

#### Section 25 Reverb Status

| # | Task | Status |
|---|------|--------|
| 3 | Event StockLevelChanged + PatientRegistered | SELESAI (17.45) |
| 4 | DashboardMetricsUpdated | SELESAI (17.45) |
| 5 | SatusehatSyncProgress + SatusehatSyncJob | SELESAI (17.45) |
| 6 | AntrianUpdated | SELESAI (17.45) |
| 7 | Testing E2E Echo | SELESAI (17.47) |

### 17.45 Fix Broken Test + Reverb Events #3-#6 TDD (2026-09-30)

#### Fix ResolveCompanyContextMiddlewareTest 7 Fail (Selesai)

- User::factory()->create() tanpa company_uuid/branch_uuid (kolom sudah di-drop).
- CompanyScopeResolver::userHasAccessToCompany() + userHasAccessToBranch(): tambah return false yang hilang.
- Test legacy_user_without_pivot diganti: user tanpa pivot 403 walau kirim header (legacy columns tidak ada lagi).
- Test changing_default_company_pivot: assert context berganti ke company B (bukan assert DB users.company_uuid).
- Semua 7 test lulus (9 assertions).

#### Purchase Order Warehouse Audit (Selesai)

- purchase_orders sudah pakai location_uuid (bukan warehouse_uuid) -- sudah sesuai keputusan Section 15.
- goods_receipts sudah punya location_uuid untuk multi-warehouse penerimaan.
- 4 test lulus di PurchaseOrderWarehouseTest.

#### Cleanup KanbanCard.php Legacy (Selesai)

- Pindah src/app/Models/KanbanCard.php ke src/modules/Core/Platform/Models/KanbanCard.php.
- Update namespace + 2 referensi (PublicKanbanBoard.php + PublicKanbanBoardTest.php).
- rg 'namespace App.Models' di src/modules/ = 0 hasil.

#### Reverb Events #3-#6 TDD (Selesai)

- Event: StockLevelChanged (Inventory), PatientRegistered + AntrianUpdated (Registration), DashboardMetricsUpdated (Analytics), SatusehatSyncProgress (Integration).
- Semua implement ShouldBroadcast + PrivateChannel (branch.{uuid} atau company.{uuid}) + broadcastWith() payload.
- SatusehatSyncJob (ShouldQueue, tries=3, backoff=10s): broadcast progress per patient + final success.
- 15 test lulus di BroadcastEventTest (41 assertions).
- Channel: stock/patient/antrian/dashboard -> private-branch.{uuid}, satusehat -> private-company.{uuid}.

#### Total Regression

- 78 test lulus (152 assertions) di 14 file test.
- Test suite 100% hijau (0 fail).

### 17.46 Progress Sync + PHP 8.3 Pin (2026-09-30)

#### Sinkronisasi _progress.md (Selesai)

- Multi-org langkah 4: BELUM -> SELESAI (17.36, 131 titik di 22 file).
- Multi-org langkah 6: BELUM -> SELESAI (17.36, drop kolom legacy + hapus 59 fallback).
- Section 26 2 gap: BELUM -> SELESAI (17.39, pivot audit + master branch scope).
- Section 25 Reverb #3-#6: SELESAI (17.45).
- Reverb #7: SELESAI (17.47). PHP 8.3: deployment config (bukan code).

#### PHP 8.3 Pin (SKIP -- deployment config)

- composer.json tetap "php": "^8.2" -- local PHP 8.2.12, pin ^8.3 akan break composer install.
- PHP 8.3 + OPcache/JIT adalah config deployment server (bukan code). Wajib dipin saat deploy production.

### 17.47 Reverb Task #7 -- E2E Echo Testing (2026-09-30)

#### E2E Echo Broadcast Tests (Selesai)

- Playwright E2E: echo-broadcast.view.spec.ts -- 3 test (stock level, antrian, dashboard metric).
- Verifikasi UI tidak refresh saat menerima broadcast event via window.Echo mock.
- Semua 3 test lulus (chromium).

#### k6 WebSocket Load Test (Selesai)

- loadtest/websocket-load-test.js -- 100 VUs, 30s, threshold ws_connecting p(95)<2000ms.
- loadtest/README.md -- instruksi install + run k6.
- k6 CLI perlu diinstall terpisah (k6.io).

#### Laravel Server E2E Prerequisites (Selesai)

- Migration telescope_entries + pulse_tables dijalankan.
- .env: CACHE_STORE/SESSION_DRIVER/QUEUE_CONNECTION kembali ke database (local dev tanpa Redis server).

#### Section 25 Reverb: 7/7 task selesai.

### 17.48 Final Status -- Semua Todo Coding Selesai (2026-09-30)

#### Ringkasan Akhir

| Kategori | Status |
|----------|--------|
| Reverb task #1-#7 | SELESAI (17.40-17.47) |
| Multi-org langkah 4 + 6 | SELESAI (17.36) |
| Section 26 (pivot audit + branch scope) | SELESAI (17.39) |
| Boundary violations | SELESAI |
| Monitoring (Pulse + Telescope) | SELESAI (17.43) |
| purchase_order_items kolom sumber | SELESAI (17.42) |
| purchase_orders warehouse audit | SELESAI (17.45) |
| Cleanup KanbanCard legacy | SELESAI (17.45) |
| Fix broken test (7 fail) | SELESAI (17.45) |
| E2E + k6 load test | SELESAI (17.47) |
| _progress.md + _plan.md sync | SELESAI (17.46) |

#### Test Suite Final

- 78 PHPUnit test lulus (152 assertions) di 14 file -- 0 fail.
- 3 Playwright E2E test lulus (chromium) -- broadcast UI update tanpa refresh.
- k6 load test script siap di loadtest/websocket-load-test.js (100 VU, 30s).

#### Sisa Deployment (Bukan Code)

- PHP 8.3 + OPcache/JIT -- lihat ../deployment-checklist.md.
- Redis server install -- lihat ../deployment-checklist.md.
- Supervisor + Reverb daemon -- lihat ../deployment-checklist.md.
- Read replica + CDN -- Fase 2 (500+ user).
- Endpoint new (Quotation) -- trigger-based.

Semua todo coding-level: 100% selesai.

### 17.49 Accounting Index -- Tab Menu Page (2026-09-30)

#### AccountingIndex.vue (Selesai)

- Buat Finance/Journal/presentations/views/AccountingIndex.vue -- tab menu page untuk modul Akuntansi.
- 3 tab: Akuntansi (9 menu), Jurnal (2 menu), Laporan (4 menu).
- Pattern sama dengan InventoryIndex.vue (state + VM + view).

#### accounting_index.state.ts + accounting_index.vm.ts (Selesai)

- state: ACCOUNTING_TABS (3 tab, 15 menu), ACCOUNTING_VALID_KEYS, ACCOUNTING_DEFAULT_TAB.
- VM: useAccountingIndexVM (activeTab, searchQuery, expandedKeys, searchResults, isSearching).
- Menu tersedia:
  - Tab Akuntansi: Periode, Akun Induk, Sub Akun, COA, Saldo Awal, Transaksi, Kas & Bank, Penyesuaian, Balance Check
  - Tab Jurnal: Daftar Jurnal, Buku Besar
  - Tab Laporan: Neraca Saldo, Neraca, Laba Rugi, Menu Laporan Keuangan

#### Route + Controller (Selesai)

- Route: GET /accounting -> WebAccountingController::showIndex -> AccountingIndex.vue.
- Permission: akuntansi.view (sudah ada di RolePermissionSeeder).

### 17.50 Sidebar Active State Fix (2026-09-30)

#### Fix Sidebar Inventory Active saat Akses Sub Menu (Selesai)

- SidebarNavigationMenuService::matchesCurrentPath() sekarang support array of patterns (bukan cuma string).
- config/menus.php: inventory active_segment jadi array (inventory*, stock/*, master/products*, master/units*, master/warehouses*, master/payment-terms*, master/taxes*, master/customers*, master/supplier*, master/companies*, master/branches*).
- Tambah sidebar menu "Akuntansi" (accounting*, finance/menu*) + permission akuntansi.view.
- Laporan active_segment jadi array (laporan*, analytics/*, reports/*).
- Sekarang saat akses /master/products (dari menu Inventory), sidebar Inventory tetap active.

### 17.51 Dark Mode UI System (2026-09-30)

#### Theme + Layout (Selesai)

- Tambah dark palette `html.dark` di `theme.css`.
- Tambah `dark-theme.css` untuk Tailwind, input, scrollbar, shadow, dan legacy inline style.
- Tambah toggle theme di header sebelah kiri.
- Simpan preferensi di `localStorage` dan fallback ke `prefers-color-scheme`.
- Tambah anti-flash inline script sebelum Vite.
- Hapus class `bg-gray-50 text-gray-900` dari body.

#### Master Products + Shared Table (Selesai)

- Konversi hardcoded color di `DataTable.vue` ke token tema.
- Konversi hardcoded color di `ProductIndex.vue` dan `ProductForm.vue`.
- Perbaiki kontras tombol/gradient dengan `--on-primary`.
- Override glass dashboard agar tidak tetap putih di dark mode.

#### Validasi

- `npm run build` lulus.

### 17.54 DataTable VM Architecture (2026-09-30)

#### Logic ke VM + State (Selesai)

- Gabungkan server-side dan client-side table logic ke satu Pinia VM:
  `Core/shared/Components/common/DataTable.vm.ts`.
- `DataTable.vue` sekarang hanya render data VM dan meneruskan aksi user; tidak ada transformasi, filter, sorting, pagination, debounce, atau fetch.
- Pisahkan initial state ke `DataTable.state.ts`; lengkapi kontrak tipe di `DataTable-types.ts`.
- Hapus composable logic `DataTable.client.ts` karena sudah digantikan VM.
- Tetap kompatibel dengan pemakaian `ref` lewat `defineExpose({ vm, reload })`.
- Pisahkan scoped style `DataTable.vue` ke `DataTable.css` dengan `<style scoped src>`.

#### Testing (Selesai)

- Tambah `Core/shared/tests/common/DataTable.vm.spec.ts`.
- Cover debounce search, client-side filter, sorting toggle, pagination, dan non-sortable column.
- Hasil: 4/4 test lulus.

#### Validasi

- `npm run build` lulus.
- `npm run typecheck` masih gagal oleh pre-existing error `resources/js/echo.ts` (generic `Echo<T>`), bukan regresi DataTable.

### 17.52 Controller Import Hygiene (2026-09-30)

#### Namespace di Atas File (Selesai)

- Pindahkan penggunaan namespace langsung di method ke `use` statement.
- File diperbaiki:
  - `WebAccountingController` -- `RedirectResponse`.
  - `Sales/DashboardController` -- `JsonResponse`.
  - `Master/ProductController` -- `JsonResponse`.
  - `Master/LocationController` -- `JsonResponse`.
  - `Platform/SettingsController` -- `RedirectResponse`.
- Audit ulang controller: tidak ada FQCN langsung di isi method (string mapping di base controller bukan import).

#### Validasi

- `php -l` lulus untuk 5 controller.

### 17.53 Accounting CSS Module Path (2026-09-30)

#### CSS Scope Diperbaiki (Selesai)

- Pindahkan pemakaian CSS akuntansi dari global `resources/css/accounting.css` ke module terbaru:
  `Finance/Journal/presentations/styles/accounting.css`.
- `AccountingIndex.vue` langsung meng-import CSS module tersebut.
- Hapus import stale `@import './accounting.css'` dari `resources/css/app.css`.
- Audit class `acc-*`: hanya dipakai oleh `AccountingIndex.vue`.

#### Validasi

- `npm run build` lulus.

### 17.55 Accounting Menu Merge ke Inventory (2026-09-30)

#### Halaman Akuntansi Jadi Tab Inventory (Selesai)

- Semua menu akuntansi dipindahkan ke tab `akuntansi` pada `INVENTORY_TABS`
  (`Operational/Inventory/presentations/viewmodels/inventory_index.state.ts`).
- `GET /accounting` sekarang redirect ke `/inventory?tab=akuntansi` sehingga
  link lama tetap berfungsi; sub-page `/accounting/*` tidak berubah.
- Entry sidebar standalone "Akuntansi" dihapus dari `config/menus.php`;
  segment `accounting*` dan `finance/menu*` dipindah ke active_segment menu
  Inventory agar sidebar tetap highlight saat membuka halaman akuntansi.
- Hapus file hub yang tidak terpakai: `AccountingIndex.vue`,
  `accounting_index.state.ts`, `accounting_index.vm.ts`, dan
  `Finance/Journal/presentations/styles/accounting.css`.

### 17.56 Unified Select2 Architecture (2026-09-30)

#### Satu Komponen Select2 (Selesai)

- Tambah `Select2.vue` sebagai satu komponen dropdown untuk client-side dan server-side.
- Tambah `select2.vm.ts`, `select2.state.ts`, dan `select2.types.ts` mengikuti pola DataTable VM + State.
- Samakan lokasi shared komponen data: `Select2` dan seluruh file pendukungnya dipindah ke `Core/shared/Components/common` bersama `DataTable`.
- Pindahkan filter client-side, debounce server-side, guard race request, dan sinkronisasi opsi terpilih ke VM.
- `Select2ClientSide.vue` dan `AsyncSelect2.vue` dijadikan wrapper backward compatibility sehingga pemakaian lama tidak berubah.
- Rencana arsitektur ditambahkan pada `Section 20.16`.

#### Testing & Validasi

- Tambah `Select2.vm.spec.ts` untuk client-side filtering, server-side request, dan race request.
- Hasil: `Select2.vm.spec.ts` 4/4 test lulus.
- Hasil: seluruh frontend test 76 file / 433 test lulus.
- Validasi: `npm run typecheck` lulus.
- Validasi: `npm run build` lulus.

### 17.57 Vue Feature Architecture Cleanup (2026-09-30)

#### Feature Views (Selesai)

- Pindahkan sisa inline menu, opsi tabel, dan status ke file state masing-masing.
- Pindahkan formatting role, mapping opsi, dan mount chart ke VM.
- Pindahkan shortcut Ctrl+S/Ctrl+F/Esc/F1 dari 22 form ke shared `form_shortcuts.vm.ts`.
- Pindahkan UI refs DataTable dan form ke file state per feature.

#### Guard & Testing (Selesai)

- Tambah `vue_architecture.spec.ts` untuk mencegah logic, API, state, dan helper function kembali masuk ke feature `.vue`.
- Tambah `form_shortcuts.vm.spec.ts` untuk shortcut submit, cancel, dan help drawer.
- Hasil: typecheck lulus.
- Hasil: target arsitektur 5/5 test lulus.
- Hasil: seluruh frontend test 79 file / 442 test lulus.
- Validasi: `npm run build` lulus.

### 17.58 Stock Adjustment Form Cleanup (2026-09-30)

#### Logic & State (Selesai)

- Pindahkan state awal, opsi status/per halaman, mode form, dan dropdown ref
  `FarmacyStockAdjustmentForm` ke `farmacy_stock_adjustment.state.ts`.
- Pindahkan judul form, label mode, subtotal item, dan click-outside dropdown
  ke `farmacy_stock_adjustment.vm.ts`.
- View form hanya render state VM dan meneruskan aksi user.

#### Testing (Selesai)

- Tambah `farmacy_stock_adjustment.vm.spec.ts`.
- Cover state awal, tambah/hapus item, subtotal/total, dan mode edit.
- Hasil: 4/4 test lulus.

### 17.59 Product Submit & API Context (2026-09-30)

#### Submit Tanpa Reload (Selesai)

- Hilangkan reload otomatis halaman setelah produk disimpan; status submit
  dikelola di VM (product.vm.ts).
- Rapikan Select2 agar opsi dropdown stabil pada mode client dan server.

#### Konteks Dari User Login (Selesai)

- Konteks company_uuid/branch_uuid diambil dari user login, bukan dikirim
  berulang dari frontend.
- Tambah fallback konteks dari user login bila middleware belum menyiapkan
  context, dengan unit test fallback.
- Hapus trigger reload options saat tab aplikasi kembali aktif agar overlay
  tidak muncul.
- Rapikan posisi tombol kembali dan ikon bantuan pada header form produk.

#### Seeder & Testing (Selesai)

- Bersihkan seeder satuan dari nama tidak konsisten dan duplikasi.
- Konsistenkan seeder superadmin dengan branch sandbox dan pivot akses.
- Perkuat test CRUD produk pada UI dan backend, termasuk soft delete.

### 17.60 Account Menu Submenu (2026-09-30)

#### Struktur Menu Baru (Selesai)

- Ubah menu Account menjadi menu induk dengan empat submenu sesuai urutan
  referensi: Asset, Account, Saldo Awal COA, dan Setting COA.
- Submenu COA dan saldo awal diarahkan ke halaman akuntansi yang tersedia;
  Asset sementara memakai tautan kosong.

### 17.61 Chart of Accounts Management (2026-09-30)

#### Backend (Selesai)

- Lengkapi CRUD akun pada AccountController/AccountService dengan
  perbaikan konteks dan otorisasi akun (AccountRepository).
- Update ChartOfAccountsSeeder dan AccountEntity.

#### Frontend (Selesai)

- Tambah form partial ChartOfAccountsForm.vue dan lengkapi
  ChartOfAccountsIndex.vue.
- Tambah chart_of_accounts.vm.ts dan update chart_of_accounts.state.ts.
- Bersihkan import .ts pada file .vue lintas modul agar kompatibel
  konfigurasi TypeScript.

#### Testing (Selesai)

- Tambah chart_of_accounts_index.spec.ts (frontend) dan perluas
  AccountControllerTest (backend).

### 17.62 Identity, Master, Inventory & QA Infrastructure (2026-10-06)

#### Core\Identity (Selesai)

- Tambah is_active, permissions, serta helper hasRole()/can() pada
  UserEntity.
- Perbarui User, UserRepository, dan UserController (termasuk
  change password).
- Tambah UserPivotRepositoryTest dan change_password.view.spec.ts.

#### Operational\Master (Selesai)

- Refactor ProductRepository dengan ekstraksi filter ke
  Support/ProductFilter.php dan Support/ProductFilterBuilder.php.
- Tambah ProductExportService dan ProductImportService (import/export
  Excel produk).
- Perbarui ProductEntity, ProductController, dan WebProductController.
- Tambah test ProductImportExportServiceTest,
  ProductPermissionE2ETest, product.transfer.view.spec.ts, dan
  product.url.spec.ts.

#### QA Infrastructure (Selesai)

- Tambah script coverage gate: scripts/check-clover-coverage.php,
  scripts/check-playwright-coverage.mjs, dan
  scripts/check-vitest-coverage.mjs.
- Tambah phpstan.neon (level 1 untuk Master Support/Services dan Identity)
  dan src/deptrac.yaml (enforcement boundary arsitektur).
- Sederhanakan CI menjadi "Application CI" fokus src/ dengan job:
  php-pint, php-static, php-tests, frontend-typecheck, frontend-vitest,
  dan playwright.
- Tambah tests/E2E/auth.setup.ts untuk sesi autentikasi Playwright.
- Update playwright.config.ts, vitest.config.ts, phpunit.xml,
  composer.json, dan DatabaseSchemaParityTest.

### 17.63 FarmasiService Enum Refactor (2026-10-06)

- Tambah enum StockMovementType dengan nilai in/out/adjust.
- Ganti literal type dan reference type pada FarmasiService dengan enum
  StockMovementType dan StockMovementReferenceType.

### 17.64 Master Data Company/Branch Scope Hardening (2026-10-07)

#### Model Scope Trait (Selesai)
- Tambah HasCompanyScope + HasBranchScope pada model Location, Unit, Tax, PaymentTerm.
- Tambah HasCompanyScope pada model Supplier (HasBranchScope sudah ada).

#### Repository Scope Filter (Selesai)
- LookupSetRepository: tambah filter company_uuid pada getSetByUuid,
  getSetByName, getSetByProgramCode, destroySet, destroyValue,
  dan getValuesBySetName (lookup_sets.company_uuid = X or '').
- LookupRepository: perbaiki storeValue agar menyimpan company_uuid dari
  entity (sebelumnya selalu null).
- TaxRepository: tambah filter branch_uuid pada getPageData, findByUuid,
  dan count active_taxes.
- PaymentTermRepository: tambah filter branch_uuid pada getPageData,
  findByUuid, dan count active_terms.

#### Testing (Selesai)
- Tambah MasterCompanyScopeIsolationTest (6 test) untuk verifikasi
  isolasi data antar perusahaan pada Location, Unit, Tax, PaymentTerm,
  Supplier, dan soft delete tidak bocor.
- Hasil: 9 passed (23 assertions) termasuk MasterBranchScopeTest.
- Pint: 10 file formatting clean.

### 17.65 Audit Model Multi-org Scope Lengkap (2026-10-07)

#### Hasil Audit (Selesai)

- Pindai seluruh 129 model di `src/modules/**/Models/*.php` untuk
  penggunaan trait `HasBranchScope` dan `HasCompanyScope`.
- Sudah menerapkan keduanya (9): Location, Unit, Tax, PaymentTerm,
  Product, Service, ServicePackage, Supplier, StockAdjustment.
- Hanya HasBranchScope (15): Complaint, Encounter, EncounterItem,
  Patient, Registration, AccountBalance (Journal & Ledger),
  RecurringJournal, ClosingPeriod, Payable, Receivable, Employee,
  ServicePackageItem, Sale, SaleItem.
- Belum menerapkan keduanya (104): daftar lengkap ada di Section 30.4
  `_plan.md` -- mencakup Journal/Transaction/SalesInvoice (Finance),
  Stock/StockMovement (Inventory), Purchase/PurchaseOrder
  (Procurement), SalesOrder/DeliveryOrder (Sales), Bom/Assembly
  (Manufacturing), serta model master & person di Operational\Master.
- Model global by design (Icd10Code, Icd9Code, BpjsSetting,
  SatusehatSetting, Branch, Company, User, Role, Permission, dst) tidak
  memerlukan multi-org scope -- dicatat sebagai pengecualian.

#### Klasifikasi Prioritas (Selesai)

| Prioritas | Kategori | Aksi |
|---|---|---|
| Tinggi | Transaksi/bisnis per cabang | Tambah kedua scope trait bila kolom sudah ada |
| Menengah | Detail/child model ber-scope | Ikut scope parent atau tambah trait sendiri |
| Rendah | Master global per-company (Coa, Account, LookupSet) | Cukup HasCompanyScope |
| Tidak perlu | Entitas infrastruktur & global sistem | Pengecualian eksplisit |

#### Rencana Lanjutan

- Eksekusi penambahan trait per prioritas (tinggi -> menengah ->
  rendah), satu modul per iterasi, disertai test isolasi antar
  company/branch.
- Setiap modul harus cek skema tabel dulu (kolom company_uuid/
  branch_uuid) sebelum menambah trait, mengacu Section 26.2.

### 17.66 Instalasi Trait Multi-org Scope Iterasi 1-3 via TDD (2026-10-08)

Eksekusi Section 30.6 `_plan.md` dengan TDD (test merah -> pasang
trait -> hijau) pada SQLite :memory:. Audit kolom dilakukan per tabel
(nama model tidak diandalkan): beberapa "master" ternyata punya
`branch_uuid` di skema aktual sehingga wajib dua trait.

#### Iterasi 1 -- Model Transaksi (Selesai)
- 14 model dapat `HasCompanyScope + HasBranchScope`: Journal, JournalLine,
  Transaction, SalesInvoice, GeneralLedger (Finance), Stock, StockMovement
  (Inventory), Purchase, PurchaseOrder (Procurement), SalesOrder,
  DeliveryOrder (Sales), Dispensing (Pharmacy), LabOrder, RadiologyOrder
  (Ancillary).
- Test: `TransactionModelScopeTest` -- 28 pass (56 asersi).

#### Iterasi 2 -- Model Child/Detail (Selesai)
- 31 model dapat kedua trait (JournalAttachment, OpeningBalanceHeader,
  RecurringJournalLine, AccountMapping, Asset, SalesInvoiceAging,
  SalesInvoiceDetail, TransactionDetail, ItemDetail, ItemOpeningBalance,
  ItemStok, ItemBalanceRecord, StockTransfer, StockTransferItem,
  StockAdjustmentItem, Assembly, AssemblyItem, Ingredient, GoodsReceipt,
  GoodsReceiptItem, PurchaseItem, PurchaseOrderItem, PurchaseRequisition,
  PurchaseRequisitionItem, PurchaseReturn, PurchaseReturnItem,
  PurchaseReturnPayment, SalesOrderDetail, DeliveryOrderDetail,
  DispensingItem, LabOrderItem).
- Test: `ChildModelScopeTest` -- 60 pass (120 asersi).
- Perbaikan terkait: 5 model ber-`$fillable` eksplisit ditambah
  company_uuid/branch_uuid agar mass assignment tidak membuang kolom scope
  (JournalAttachment, LabOrderItem, DispensingItem, PurchaseReturn,
  PurchaseReturnItem); kolom audit created_by/updated_by/deleted_by
  ditambahkan langsung ke migrasi create `recurring_journal_lines`
  (awalnya migrasi additive terpisah, 2026-10-09 dilebur ke migrasi
  create karena aplikasi masih tahap pengembangan).

#### Iterasi 3 -- Model Master (Selesai)
- DUAL (kedua trait, 13): Coa, CoaSetup, PriceLevel, ItemConversion,
  ItemUom, ProductAccount, ProductDiscountRule, ProductImage,
  ProductLocation, ProductOpeningBalance, ProductUnit, Shift, UserWarehouse
  -- koreksi terhadap rencana awal "master cukup company", karena skema
  aktual memiliki kolom branch_uuid.
- COMPANY-ONLY (3): LookupType, LookupValue, LookupValueDetail.
- Test: `MasterCompanyScopeTest` -- 32 pass (61 asersi).

#### Regresi & Dokumentasi (Selesai)
- Ketiga suite bersama: 120 pass (237 asersi).
- Pengecualian eksplisit didokumentasikan di Section 30.8 `_plan.md`
  (TaxSetting & item_prices tanpa kolom scope; detail product_purchases/
  product_sales/product_quotation, bom_costs/bom_details, item_suppliers,
  temp_lovs terfilter transitif via parent; LookupSet company_uuid
  nullable "null untuk lookup global" tidak boleh di-scope).
- Regresi penuh (php artisan test) TIDAK dijalankan ulang pada akhir
  iterasi 4 sesuai arahan user (suite penuh lambat, +-4-5 menit).
  Baseline yang tercatat saat iterasi 1: 528 failed / 2085 passed --
  identik sebelum dan sesudah pemasangan trait pada sampel test
  (InteractsWithSandboxRuntimeTest gagal tanpa perubahan juga), jadi
  kegagalan tersebut pre-existing, bukan akibat global scope.
  Verifikasi lanjutan cukup test terarah per file/direktori.

### 17.67 Penyeragaman PK Legacy id -> uuid + Perbaikan Relasi (2026-10-09)

Detail teknis ada di Section 30.10 `_plan.md`. Ringkasan:

- 4 tabel legacy (transactions, transaction_details, shifts, stocks)
  direname PK char('id',36) -> char('uuid',36) langsung di migrasi
  create (tahap pengembangan, belum ada data produksi).
- 4 model: primaryKey 'id' -> 'uuid'.
- 7 relasi diperbaiki (2 sah menunjuk PK lama, 5 rusak menunjuk 'id'
  padahal parent ber-PK uuid).
- 3 import kelas relasi yang unresolved ditambahkan (Shift, Sale,
  Purchase).
- Test baru: LegacyPrimaryKeyUuidTest -- 11 pass (29 asersi).
- Regresi terarah: 3 suite scope (120 pass / 237 asersi) +
  ShipRepositoryTest (2 pass) -- semua model yang tersentuh tetap hijau.
- Temusan lanjutan (terpisah): ~160 referensi kelas relasi unresolved
  di 40+ model (pola sama dengan bug Shift/Sale/Purchase).

### 17.68 Perbaikan Import Kelas Relasi Unresolved di Models (2026-10-09)

Tindak lanjut temusan 17.67, dikerjakan di sesi terpisah dan
diverifikasi mandiri oleh sesi utama (re-scan + run test, hasil
identik). Detail di Section 30.11 `_plan.md`. Ringkasan:

- 151 referensi unresolved di 59 file Models diperbaiki (~150 import
  baru).
- 3 bug import salah-namespace diperbaiki (Encounter di Patient &
  Registration, StockMovement di Product) -- ditangkap oleh test
  refleksi baru.
- Test baru: ModelRelationClassResolutionTest -- 397 pass (1191
  asersi), memindai semua method relasi via reflection.
- 4 suite scope/PK terarah: 131 pass (266 asersi), tetap hijau.
- Re-scan final Models: 0 unresolved.
- Sisa pekerjaan terpisah: 17 import salah-namespace di
  Repositories/Services/Controllers (sebagian menunjuk kelas yang
  belum ada).

### 17.69 Import Salah-Namespace Non-Models + Kelas Hilang (2026-10-09)

Tindak lanjut sisa pekerjaan 17.68, dikerjakan langsung. Detail di
Section 30.12 `_plan.md`. Ringkasan:

- Audit kategori: (A) 15 import salah-namespace di 10 file diroute
  ulang (total 18 import di 11 file termasuk AccountingService);
  (B) 6 kelas hilang dibuat: FileRequestEntity (dipulihkan dari git
  f6ba103c9^), CustomerSupplierFilterEntity, 4 Exports Excel
  Asset/Coa di Finance/Journal; (C) import App\ legacy yang sah
  (ExceptionLogger, dll) diverifikasi dan tidak diubah.
- Bug readonly-assignment di CoaExport/AssetExport diperbaiki
  (tertangkap smoke test).
- Verifikasi: scan final 0 unresolved di non-Models; php -l 17 file
  OK; smoke test 6 kelas baru OK; test terarah 424 passed
  (ModelRelation 397 + File/Image/Accounting 21 + UserService 2 +
  EmployeeService 4).
- Tidak di-commit -- menunggu review.

#### 17.69.1 Test Perilaku Kelas Baru (2026-10-09)

Lanjutan 17.69. 4 file test perilaku nyata (37 test / 129 asersi,
hijau) untuk FileRequestEntity, CustomerSupplierFilterEntity, 4
Exports, FileService (flow upload/duplikat/delete/restore/integrity),
ImageService, dan CustomerSupplierService+Repository end-to-end.
Detail di Section 30.12.1 `_plan.md`.

BUG tertangkap: FileService::buildVariants men-type-hint File tanpa
use (resolve ke namespace salah, fatal saat upload nyata) -- diperbaiki.
Regression: 22 stub lama hijau, ModelRelation 397 hijau.

### 17.70 Triage 528 Kegagalan Test + Ekspansi PHPStan (2026-10-09)

#### Triage 528 Kegagalan Test (Selesai -- 17.66/17.67 lanjutan)

Baseline 528 failed / 2085 passed dari iterasi scope trait ditriage
per kategori, semua diperbaiki (kecuali yang pre-existing eksternal):

1. **Test asersi kolom legacy `id`** (4 file): parity/migration test
   masih ekspek kolom `id` yang sudah jadi `uuid` (Section 30.10) --
   `CreateIngredientsTableTest`, `CreatePosLegacyTablesTest` (3 edit),
   `DatabaseSchemaParityTest` (4 array kolom) diupdate -> 273 + 124
   passed.
2. **FK stock_movements.user_uuid** (2 flow test): PurchaseService
   memfabrikasi `actor_uuid` acak; test kini membuat User nyata via
   factory + PurchaseRecordEntity ditambah field `location_uuid`
   (dibaca StockMovementEntity) -> ProcurementFlowTest +
   ProcurementToAccountingFlowTest hijau.
3. **Relasi `branch` tidak ada di User**: CompanyScopeResolverTest
   `load('branch')` -> `load('branches')` (pivot) -> 4 passed.
4. **Boundary strict (R1) FileService**: import Model `File` dihapus,
   type-hint diganti `object` + docblock FQCN -> strict 0 pelanggaran,
   29 passed.
5. **Flaky interferensi antar-test** (SQLite :memory:, PDO nesting):
   GeneralLedgerRepositoryTest & 1 test Accounting gagal HANYA dalam
   batch tertentu, hijau saat solo/rerun -- pola sama dengan catatan
   17.19 AuthControllerTest. Tidak diubah.

Hasil akhir seluruh direktori Feature + Unit: hijau (batch besar
2020 passed/37 skipped; Unit penuh 605 passed/16 skipped).
Insiden dev-DB: satu `migrate:fresh` tanpa `--env=testing` sempat
men-drop MySQL dev `inventory` -- dibangun ulang dari seeder; belajar:
operasi DB artisan selalu `--env=testing`.

#### Ekspansi PHPStan Analytics + Finance (Selesai -- 29.3.1)

- Scope phpstan.neon ditambah `modules/Analytics` + `modules/Finance`
  dan `databaseMigrationsPath: [modules]`.
- 54 error -> 0: kelas duplikat dihapus (AccountingEntity.php,
  Journal AccountBalance.php), typed relation return type pada 11
  model, import same-namespace, perbaikan bug nyata
  (InitialBalanceRepository eager-load 'unit' hantu, GeneralLedger
  $dateFrom/$dateTo undefined, ExportService phantom
  ExportRequestEntity -> array $data).
- Regresi terarah setelah edit model: repositori Finance/Accounting/
  MasterData/Operational 412 passed; Accounting penuh 344 passed/6
  skipped; controller ServicePackage/Encounter/Sale + dashboard 21
  passed; Clinical repositori 35 passed; LegacyPrimaryKeyUuid 11
  passed; boundary strict OK.
- `vendor/bin/phpstan analyse` = [OK] No errors.
- Trial `modules/Clinical` (43 error) tercatat sebagai iterasi
  berikutnya.

#### Test Task Terkait (Selesai)

- TransactionSequenceScopeTest + UserFactoryLegacyScopeTest:
  9 passed (32 asersi) -- Section 30.13.

Tidak di-commit -- menunggu review user.

### 17.71 Evaluasi Bun vs Laravel + Arsitektur QZ Tray (2026-10-12)

#### Diskusi Keputusan (Selesai)

- Pertanyaan user: pindah ke Bun atau tetap Laravel? Alasan yang
  diusulkan: kebutuhan socket dan kebutuhan QZ Tray printer ("perlu
  pakai socket"). App masih dev, belum ada user -- momen termurah
  untuk pindah stack jika memang sepadan.
- Analisa (detail di Section 31 _plan.md):
  - Socket: sudah terjawab -- Reverb terinstall dan 7/7 task
    selesai (17.40-17.47), threshold >1000 koneksi WS.
  - QZ Tray: miskonsepsi teridentifikasi -- WebSocket-nya adalah
    browser -> QZ Tray di localhost:8181 (PC kasir), BUKAN backend.
    Peran backend hanya endpoint signing RSA SHA512 (openssl_sign,
    contoh resmi QZ memang PHP).
  - Bun ditolak: biaya rewrite (129 model, 78 repo, tenant
    isolation ~120 model, domain akuntansi, 2000+ test, QA infra)
    tidak sepadan; tidak ada kebutuhan teknis yang terjawab Bun.
- Keputusan: TETAP Laravel 12. Escape hatch performansi tanpa
  rewrite: Laravel Octane (trigger tetap metrik Section 24).
- Rencana integrasi QZ Tray (5 task) tercatat di Section 31.5
  _plan.md -- BELUM dieksekusi, menunggu prioritas user.

#### Dokumentasi (Selesai)

- _plan.md: tambah Section 31 (31.1 konteks, 31.2 analisa, 31.3
  temuan, 31.4 keputusan, 31.5 rencana QZ, 31.6 testing, 31.7
  checklist, 31.8 kesimpulan).
- File yang mendasari analisa (bacaan): Section 24/25 _plan.md;
  17.33-17.35, 17.39-17.43, 17.47 _progress.md; arsitektur QZ dari
  qz.io/docs (print-server, Signing, signing-examples,
  github.com/qzind/tray/wiki/Architecture).

#### 17.71.1 Performance Gate k6 HTTP + Budget Deployment (2026-10-12)

Eksekusi rekomendasi 17.71: penyesalan realistis adalah launch tanpa
load test -- gate-nya dibuat.

- `loadtest/http-load-test.js` (baru):
  - Profil `K6_STAGES`: full (ramp 50 -> 200 hold 2m -> 500 hold 2m),
    v200 (smoke ~4m), v500 (~4m).
  - Skenario nyata ERP: login JWT (setup + re-login berkala),
    listing produk (search + paginasi 2 halaman), listing jurnal,
    cash-flow (agregat terberat). Print/QZ menyusul setelah 31.5.
  - Threshold Section 24.1: p(95) < 200ms, p(99) < 500ms,
    fail < 1%, listing_error_rate < 1%. Metrik custom
    login_duration/listing_duration.
  - Autentikasi: POST /api/v1/auth/login -> access_token (JWT
    php-open-source-saver) -> Bearer; kredensial via env
    K6_EMAIL/K6_PASSWORD (default seeder superadmin@klinik.local).
- `loadtest/README.md`: section Performance Gate -- cara run, tabel
  profil, interpretasi hasil (lolos -> catat baseline; 200 lolos
  tapi 500 P95>300ms -> trigger Octane; gagal 200 -> profil N+1/
  laporan sync/index).
- `../deployment-checklist.md`: section "Performance Gate (Pre-Launch
  -- WAJIB)" -- tabel budget, 7 langkah gate (deploy production-like,
  seed volume realistis, smoke v200, full, WS test, cek Pulse,
  catat baseline), tabel hasil baseline (kosong, diisi saat run),
  arahan jika tidak lolos (perbaikan -> Octane, bukan ganti bahasa).
- _plan.md: 31.6 diupdate (gate SELESAI DIBUAT, eksekusi menyusul);
  31.7 checklist: k6 gate dicentang + item baru "eksekusi gate di
  server production-like".

Verifikasi: sintaks script tervalidasi via node ESM parse (k6 CLI
belum terinstall di mesin dev -- eksekusi gate saat deploy/
production-like). Sintaks PHP tidak terubah; tidak ada test Laravel
yang tersentuh.

### 17.72 Monitoring Performa -- Laravel Pulse Diproteksi & Dialibrasi (2026-10-12)

**Kebutuhan user**: bisa lihat kapan/di mana server dipakai berat,
query/method mana yang makan resource, user siapa penyebabnya --
tabel + detail + filter range tanggal + deteksi lonjakan.

**Temuan**: Laravel Pulse (`laravel/pulse ^1.8`) SUDAH ter-install
dan AKTIF merekam (30k+ entri di DB dev, termasuk lonjakan k6 run
17.71.1: 4.708 slow_request). Atribusi user bekerja via
`key_hash = md5(uuid)` + resolver default `Laravel\Pulse\Users`
(PK uuid). Dashboard `/pulse` lengkap (Usage, Slow Requests,
Slow Queries, Exceptions, Servers) dengan filter waktu.

**Masalah ditemukan & diperbaiki**:
- Dashboard terbuka publik (gate default vendor: semua orang saat
  APP_ENV=local) -> sekarang `Gate::define('viewPulse', can
  'system.monitor.view')` di AppServiceProvider; anonim = 403.
- Permission baru `system.monitor.view` di RolePermissionSeeder
  (group Monitoring & Observability; admin/owner/superadmin dapat
  via $allPerms). Re-seed OK, relasi admin = 99 permission utuh.
- Threshold default 1000ms terlalu longgar vs budget P95<200ms ->
  .env: PULSE_SLOW_QUERIES_THRESHOLD=200,
  PULSE_SLOW_REQUESTS_THRESHOLD=300. Terverifikasi: request
  products 429-459ms -> 9 slow_request terekam, slow_query 0
  (tidak ada query tunggal >200ms; latency dari akumulasi).

**Dokumentasi**: _plan.md Section 32 (kebutuhan, solusi vs
alternatif, temuan file/baris, perubahan, cara pakai dashboard,
catatan produksi cron pulse:check + ingest redis, testing,
checklist, kesimpulan).

Verifikasi: curl /pulse tanpa login = 403; tinker can('viewPulse')
admin = true; entries user_request 5 menit terakhir ter-atribusi
ke admin@klinik.local. Tidak ada perubahan sintaks PHP yang
berisiko (gate closure + array seeder); tidak menjalankan test
penuh sesuai aturan.

### 17.73 Konsolidasi Todo/Checklist/Progress dari _plan.md (2026-10-12)

Keputusan user: semua todo list, checklist, dan konten berkaitan dengan
progress dipindah dari _plan.md ke file ini. _plan.md hanya menyimpan
keputusan, arsitektur, dan rencana stabil. Blok di bawah dipindah apa
adanya; nomor section asli dipertahankan agar referensi silang tetap
bisa dilacak. Di _plan.md, tiap section yang dipindah kini hanya
menyisakan pointer ke bagian ini.

#### 13. Migration Checklist

Checklist ini wajib selesai sebelum Fase 1 dimulai agar modularisasi tidak membawa debt schema/legacy ke struktur baru:

1. Freeze keputusan modul top-level: hanya Core, Clinical, Operational, Finance, Analytics; tidak ada Master atau Management top-level.
2. Finalisasi mapping namespace lama ke modul baru: bagian Klinik dari Operational lama masuk Clinical; bagian ERP masuk Operational.
3. Tetapkan owner tiap tabel legacy: Core/Clinical/Operational/Finance/Analytics, termasuk sub-modul pemiliknya.
4. Audit migration lama untuk UUID PK, audit columns, soft delete `deleted_at`, index company/branch, dan FK implisit lintas modul.
5. Fixasi nama tabel/kolom legacy yang belum konsisten sebelum dipindah ke folder migration modul.
6. Tentukan Service publik minimal lintas modul sebelum file dipindah, terutama Inventory, Item Catalog, Supplier, Employee, Account, dan Journal posting; semua input/output wajib Entity/DTO.
7. Tetapkan route, permission name, dan AppMenu alias agar URL/permission lama tetap kompatibel selama transisi.
8. Siapkan test baseline per domain sebelum pindah namespace: happy path, validation, not found, unauthorized, duplicate, company/branch isolation.
9. Siapkan CI boundary check: larang import lintas modul selain Service publik berbasis Entity/DTO, larang namespace lama untuk file baru, dan validasi layer Controller -> Service -> Repository -> Model.

##### 13.1 Legacy Fixation Sebelum Fase 1

Daftar konkret yang perlu difiksasi sebelum masuk Fase 1:

| Area | File legacy terdeteksi | Tabel/entitas | Fixasi yang perlu diputuskan |
|---|---|---|---|
| Finance - Recurring Journal | `src/app/Repositories/Accounting/recurring_journal_repository.md`, `src/app/Models/accounting/RecurringJournalLine.php`, `src/database/migrations/v1/2026_03_15_000500_create_accounting_tables.php` | `recurring_journal_lines` | Pastikan owner Finance, struktur header/detail recurring journal, UUID/audit/soft-delete/index, dan Service posting jurnal periodik berbasis Entity/DTO. |
| Operational - Purchase Product | `src/app/Models/master/ProductPurchase.php`, `src/app/Services/Operational/PurchaseService.php`, `src/database/migrations/v1/2026_03_15_000400_create_operational_tables.php` | `product_purchases` | Owner final: Operational\Procurement. Normalisasi relasi item/supplier/warehouse; efek stok/jurnal dipicu lewat Service/Event terkait. |
| Operational - Quotation Product | `src/app/Models/master/ProductQuotation.php`, `src/database/migrations/v1/2026_03_15_000400_create_operational_tables.php`, `src/tests/Feature/DatabaseSchemaParityTest.php` | `product_quotation` | Putuskan apakah quotation milik Operational\Sales, relasi ke sales order/invoice, status lifecycle, dan permission alias. |
| Operational - Sales Product | `src/app/Models/master/ProductSale.php`, `src/database/migrations/v1/2026_03_15_000400_create_operational_tables.php` | `product_sales` | Tetapkan pada `Operational\Sales` domain POS, relasi ke transaction sequence, payment/aging/discount, dan event stok/finance. |
| Operational - Sales Transaction | `src/app/Services/Operational/SalesTransactionService.php`, `src/tests/Feature/Repositories/Operational/SalesTransactionRepositoryTest.php`, `src/database/migrations/v1/2026_04_06_000600_create_sales_transaction_tables.php` | sales transaction tables | Tetapkan pada `Operational\Sales` dengan domain POS dan pastikan test baseline tetap hijau sebelum namespace dipindah. |
| Test baseline legacy | `src/tests/Unit/Services/Operational/SalesTransactionServiceTest.php`, `src/tests/Feature/Repositories/Operational/SalesTransactionRepositoryTest.php`, `src/tests/Unit/Services/Operational/PurchaseServiceTest.php`, `src/tests/Feature/Controllers/Operational/PurchaseControllerTest.php`, `src/tests/Feature/Migrations/CreateSalesTransactionTablesTest.php`, `src/tests/Feature/Migrations/CreateOperationalTablesTest.php` | sales/purchase legacy | Jadikan acuan sebelum refactor; jangan pindah modul jika test legacy terkait belum punya expected behavior jelas. |

Catatan: dokumen ini hanya fokus pada folder `src/`; referensi backend legacy di luar `src/` tidak menjadi acuan. Daftar ini berbasis file yang terdeteksi saat audit `src/`; saat eksekusi Fase 0 tetap wajib `rg` ulang karena nama tabel/entitas bisa tersebar di seed, permission, route, export, dan report.

#### 14. Status & Checklist

- [x] Section 4 dengan sub-modul
- [x] Section 5 finalized (bagian Klinik dari Operational lama masuk Clinical; bagian ERP masuk Operational)
- [x] Section 12 open questions dijawab semua
- [x] Setelah Migration Checklist (section 13) siap, baru mulai Fase 1 (section 10). Keputusan: checklist dilakukan per modul saat migrasi.
- [x] Seeder modular per domain sudah dieksekusi (2026-09-23): seeder produk legacy terpisah dihapus, data seed dipindah ke modul pemilik fungsinya, dan seluruh seeder modul memakai Eloquent Model tanpa `DB::table()`.

#### 15.1-15.4 Perlu Dikonfirmasi (15.5 tetap di _plan.md)

##### 15.1 Owner Tabel Legacy

Tandai owner final untuk tiap tabel/entitas legacy sebelum migration dipindah:

| Area | Owner Usulan | Konfirmasi |
|---|---|---|
| Recurring Journal | Finance | [x] |
| Product Purchase | Operational\Procurement | [x] |
| Product Quotation | Operational\Sales | [x] |
| Product Sale | Operational\Sales (domain POS) | [x] |
| Sales Transaction | Operational\Sales (domain POS) | [x] |
| Tabel Clinical dari Operational lama | Clinical | [x] |
| Employee | Operational\Master | [x] |
| Position/Jabatan | Operational\Master | [x] |
| Department/Departemen | Operational\Master jika murni HRD; Core\Organization jika dipakai User/Branch/Otorisasi | [x] |

##### 15.2 Kontrak Service Publik

Konfirmasi signature konseptual (input/output wajib Entity/DTO) sebelum modul lain dipindah:

- [x] `Operational\Inventory` untuk stok: cek ketersediaan, reduce, return, transfer.
- [x] `Operational\Master` untuk Item Catalog: cari item, validasi UOM, harga.
- [x] `Operational\Master` untuk Supplier dan Employee.
- [x] `Finance` untuk Account dan Journal Posting.
- [x] `Core` untuk Company, Branch, User. Domain lookup/LOV dimiliki `Operational\Master`, bukan Core.

##### 15.3 Kompatibilitas Transisi

- [x] Freeze URL endpoint lama vs final per modul. Keputusan: tabel mapping per modul sudah dibuat di `Section 19`; URL final memakai prefix `/api/v1`, URL lama/NineSoft menjadi legacy/reference, dan alias/deprecated hanya dipakai selama transisi FE/client.
- [x] Freeze nama permission lama vs final. Keputusan: pola nama final memakai `create patients`, `read patients`, `update patients`, dan `delete patients`; terapkan pola yang sama ke resource lain. Mapping permission lama ? final dibuat per modul saat migrasi (tidak perlu upfront).
- [x] Freeze AppMenu alias dan urutan menu. Keputusan: alias dan urutan menu tetap selama migrasi backend; update mapping alias ke modul final dilakukan bersamaan dengan migrasi modul terkait (tidak perlu upfront).
- [x] Tentukan masa berlaku alias/deprecated namespace lama. Keputusan: tidak ada masa berlaku alias; langsung pakai namespace modular baru dan hapus namespace lama setelah pemindahan selesai.
- [x] Tentukan apakah migration lama dipindah folder saja, di-copy per modul, atau diberi marker legacy tanpa mengubah nama file. Keputusan: file migration dipindah secara fisik ke folder modul pemiliknya tanpa copy/marker/redirect; nama file dan urutan eksekusi dipertahankan.

##### 15.4 Baseline Dan CI

Catatan: baseline & CI dilakukan **per modul** saat migrasi, bukan sebagai fase terpisah. Sebelum mulai migrasi satu modul, pastikan:

- Test baseline modul tersebut sudah hijau, atau diketahui statusnya (fail/pass)
- Daftar tabel/entitas di modul tersebut sudah fix (tidak ada inkonsistensi nama)
- Gate CI per modul: test wajib hijau, schema audit konsisten, tidak ada namespace/dependency violation

Tidak perlu menjalankan baseline untuk semua modul sekaligus sebelum Fase 1 -- cukup per modul yang sedang dimigrasikan.

#### 18. Todo List untuk Multi-Org + Audit Setup (Fase 0 Deliverables)

| # | Task | Status | Owner |
|---|---|---|---|
| 1 | Buat migration user_companies pivot table | ? DONE | - |
| 2 | Buat migration user_branches pivot table | ? DONE | - |
| 3 | Update User model dengan relasi `companies()`, `branches()`, method `activeCompany()`, `activeBranch()` | ? DONE | - |
| 4 | Implementasi CompanyScope yang benar | ? DONE | - |
| 5 | Update BranchScope untuk multi-org support | ? DONE | - |
| 6 | Tambah kolom `company_uuid` & `branch_uuid` di tabel `audits` | ? DONE | - |
| 7 | Implementasi custom audit resolver untuk capture company/branch | ? DONE | - |
| 8 | Update CompanyScopeResolver untuk multi-org context | ? DONE | - |
| 9 | Deprecate kolom `company_uuid`/`branch_uuid` di `users` (Fase 2) | ✅ SELESAI -- langkah 1,2,3,5 (2026-09-22), langkah 4 helper (17.36, 2026-09-29), langkah 6 drop kolom (17.36, 2026-09-29); lihat 16.3.6 | - |

#### 22 - Temuan Gap / Perlu Perhatian (Audit 2026-09-22 & 2026-09-25)

Temuan Gap / Perlu Perhatian (audit 2026-09-22):
- [x] Finance aktual hanya punya Journal + Ledger -- Reports sudah diputuskan masuk `Analytics\Reports` (folder sudah ada, Section 4.5 & 19.2); Closing sudah terimplementasi di `Finance\Ledger` (model `ClosingPeriod` + `AccountingCycleService`), endpoint `/api/v1/accounting/closing-periods` tinggal dibangun.
- [x] `Core\shared` (lowercase) bukan backend Shared layer -- itu komponen frontend Vue milik modul Core (DataTable, Pagination, VM/state). Backend Shared layer sudah benar di `src/app/Shared` (Traits, Scopes, Support) sesuai Fase 0.
- [x] Multi-org: task #9 -- langkah 1,2,3,5 selesai 2026-09-22 (middleware `ResolveCompanyContext` + helper, rewire scope/resolver/accessor, observer sync, seeder/import pivot); langkah 4 SELESAI 2026-09-29 (131 titik dimigrasi ke helper); langkah 6 SELESAI 2026-09-29 (drop kolom + FK + index); detail + todo di Section 16.3.6 dan _progress.md 17.19.
- [x] Tabel `purchase_requisitions` (+ items) dan `goods_receipts` (+ items) sudah ada di migration `Operational\Procurement` (2026-09-22: migration + model + test migration lulus 8/8); lanjutan: Service/Repository/Controller + kolom `source_type`/`source_uuid` di `purchase_order_items`.
- [x] Boundary check script sudah ada (`src/scripts/check-module-boundaries.php`, 33 pelanggaran lama di baseline ratchet) -- status selesai, bukan gap.
- [x] Path modul: composer memetakan `App\Modules\` -> `modules/` (root `src/`); catatan klarifikasi ditambahkan di Section 6 agar penulisan `app/Modules/` vs `modules/` tidak membingungkan.

Temuan Gap / Perlu Perhatian (audit 2026-09-25):
- [x] Product export/import API sudah tersedia (`/api/v1/products/export`, `/api/v1/products/import`, `/api/v1/products/import/template`) -- gap fungsional prioritas tertinggi selesai (sebelumnya skipped di test).
- [x] Validasi duplikasi baris dinamis (satuan, akun, gudang/periode, level harga, diskon) dan kombinasi diskon persen/nominal sudah diimplementasi di backend dan frontend -- gap validasi lanjutan baris tab selesai.
- [x] E2E upload file produk (PDF, reorder, batas jumlah, oversize) sudah ditambahkan -- gap pengujian upload file selesai.
- [x] Refresh opsi dropdown tanpa reload penuh sudah diimplementasi via `router.reload({ only: ['options'] })` + watcher di `product_form.vm.ts` -- gap refresh opsi dropdown selesai.
- [x] Vue arsitektur: konstanta bisnis produk dipindah dari VM inline ke `product.state.ts`; `isPdfFile()` dan `columns` dipindah dari `ProductIndex.vue` ke VM/state -- screens kini purely presentational.
- [x] BMR seeder (`BmrDataSeeder.go`, `bmr_products.json`, test) dihapus bersama semua referensinya untuk menghilangkan dependensi lintas folder.
- [x] Semua seeder Laravel kini tidak lagi referensi folder di luar `src/` -- file JSON data (`operational_purchases.json`, `operational_invoice_items.json`, `excel_akutansi_saldo.json`) dicopy ke `src/database/data/`; fallback `../be/database/data/` dihapus dari 3 seeder; `Icd10Seeder` path diubah ke `database_path('data/')`.
- [x] ICD-9 sudah punya dedicated table `icd9_codes` -- model, migration, dan seeder dibuat mengikuti pola ICD-10; data master e-klaim sudah tersedia di `src/database/data/`.
- [x] Icd10Seeder sudah diarahkan ke `database/data/ICD-10 e-klaim.xlsx`; file belum tersedia di repo, jadi seeder tetap skip gracefully sampai data disediakan.

#### 23.6 Prioritas Eksekusi (23.1-23.5 tetap di _plan.md)

| # | Todo | Urgensi | Alasan |
|---|---|---|---|
| 1 | Fix 2 boundary violations | Tinggi | Menjaga integritas arsitektur yang sudah dibangun |
| 2 | Multi-org langkah 4 | Tinggi (SELESAI 2026-09-29) | 131 titik dimigrasi ke helper di 22 file |
| 3 | Multi-org langkah 6 (drop kolom) | Setelah #2 | Dependensi |
| 4 | purchase_order_items kolom | ✅ Selesai (17.42) | purchase_requisition_uuid nullable + FK + index |
| 5 | Endpoint "new" | Rendah | Bangun saat ada kebutuhan bisnis |

#### 24.8 Status Implementasi vs Rencana (Audit 2026-09-29)

Audit aktual codebase terhadap rencana Section 24.2 (Fase 1).

| Item Section 24 | Status Codebase | Gap |
|---|---|---|
| Redis cache | CACHE_STORE=file, SESSION_DRIVER=file, QUEUE_CONNECTION=sync | Redis tidak terinstall/konfigurasi |
| Horizon queue | Tidak ada composer package, hanya 1 Job (ProcessAutoReversalJob) dengan sync driver | Worker = queue:listen dev-mode saja |
| Compound index | 0 compound index company_uuid+branch_uuid+deleted_at di semua migration | Perlu audit + migration baru |
| PHP 8.3 + OPcache/JIT | composer.json "php": "^8.2" (local PHP 8.2) | Deployment config -- pin ^8.3 saat deploy production (bukan code) |
| Monitoring | ✅ Pulse v1.8 (production) + Telescope v5.25 (dev-only) + predis | Selesai (17.43) |
| Read replica | Tidak ada config DB_READ_HOST | Fase 2 (sesuai plan, belum waktunya) |
| CDN | Tidak ada config | Fase 2 (sesuai plan) |

Yang sudah sesuai plan:
- config/cache.php, config/session.php, config/queue.php sudah punya redis driver option (tinggal ganti .env)
- Arsitektur modular monolith sudah benar
- ProcessAutoReversalJob menunjukkan pola ShouldQueue sudah mulai dipakai

##### Task Eksekusi Fase 1 -- Status

| # | Task | Status | Detail Gap |
|---|---|---|---|
| 1 | Setup Redis + ganti .env driver | SELESAI \(code level -- .env.example redis, production butuh install Redis server\) | Install redis server + update .env |
| 2 | Implementasi cache di lookup/options/permission | SELESAI \(LookupService Cache::remember + test\) | Perlu coding di Repository/Service |
| 3 | Setup Horizon + migrasi job ke queue | SKIP \(permintaan user -- pakai queue:work + Supervisor\) | Install laravel/horizon, buat Job class |
| 4 | Audit + tambah compound index | SELESAI \(11 tabel, TDD 21 test\) | Buat migration baru |
| 5 | PHP 8.3 + OPcache config di server | DEPLOYMENT -- pin ^8.3 + OPcache/JIT saat deploy | Config deployment, bukan code |

#### 25.7 Fase Implementasi + 25.9 Production Checklist

##### 25.7 Fase Implementasi

| # | Task | Estimasi | Prioritas |
|---|---|---|---|
| 1 | Install Reverb + config .env | 0.5 hari | ✅ Selesai (17.40) |
| 2 | Setup Echo di Vue + channel authorization | 1 hari | ✅ Selesai (17.41) | Tinggi |
| 3 | Implementasi StockLevelChanged + PatientRegistered | 2 hari | ✅ Selesai (17.45) |
| 4 | Implementasi DashboardMetricsUpdated (live counter) | 2 hari | ✅ Selesai (17.45) |
| 5 | Webhook receiver SATUSEHAT + SatusehatSyncJob | 3 hari | ✅ Event + Job selesai (17.45) |
| 6 | BPJS polling + AntrianUpdated broadcast | 2 hari | ✅ AntrianUpdated selesai (17.45) |
| 7 | Testing (unit + feature + E2E Echo listen) | 2 hari | ✅ Selesai (17.46) |

Total: 7/7 task SELESAI (17.40-17.47). Deployment config di ../deployment-checklist.md.

##### 25.9 Production Checklist

- [x] Redis server running di production
- [ ] Supervisor config untuk php artisan reverb:start --production
- [x] Supervisor config untuk php artisan queue:work (pengganti Horizon)
- [ ] Nginx reverse proxy WebSocket (Upgrade headers)
- [ ] SSL/TLS untuk WSS (wss:// -- wajib di production)
- [ ] Monitor Horizon dashboard untuk failed jobs
- [ ] Monitor Reverb connection count

#### 30.7 Hasil Eksekusi (2026-10-08) -- 4 Iterasi TDD Selesai

Eksekusi mengikuti 30.6 dengan metode TDD (test merah -> pasang trait ->
test hijau), SQLite :memory: + RefreshDatabase. Test:

- `tests/Feature/Infrastructure/TransactionModelScopeTest.php` (iterasi 1)
- `tests/Feature/Infrastructure/ChildModelScopeTest.php` (iterasi 2)
- `tests/Feature/Infrastructure/MasterCompanyScopeTest.php` (iterasi 3)

Total 120 test pass (237 asersi): 14 model transaksi + 31 model
child/detail + 16 model master, masing-masing diuji 2 konsep (isolasi
query antar company + auto-fill company_uuid/branch_uuid dari konteks
login). Kolom scope kini terisi otomatis oleh trait; tidak lagi bergantung
pada filter manual di Repository.

**Koreksi terhadap 30.5:** tabel-tabel "master" ternyata mayoritas sudah
punya kolom `branch_uuid` pada skema aktual (audit kolom per tabel,
bukan per nama model). Sesuai aturan 30.5 "tambah kedua trait bila kolom
sudah ada", model berikut dipasang KEDUA trait: Coa, CoaSetup, PriceLevel,
ItemConversion, ItemUom, ProductAccount, ProductDiscountRule, ProductImage,
ProductLocation, ProductOpeningBalance, ProductUnit, Shift, UserWarehouse.
Hanya LookupType, LookupValue, LookupValueDetail yang murni company-only
(skema tidak punya kolom branch).

#### 31.7 Checklist

- [x] Keputusan bahasa/runtime diputuskan dan tercatat (section 31 _plan.md)
- [x] Kebutuhan socket dipetakan ke Reverb (selesai 17.40-17.47)
- [x] Arsitektur QZ Tray dianalisa (browser-WS, bukan backend-WS)
- [x] k6 HTTP performance gate 200/500 VU -- dibuat loadtest/http-load-test.js + budget di ../deployment-checklist.md (17.71.1)
- [ ] Eksekusi gate di server production-like + catat baseline

#### 32.8 Checklist

- [x] Dashboard dilindungi gate + permission
- [x] Atribusi user terverifikasi end-to-end
- [x] Threshold disesuaikan budget performa
- [ ] Tambah menu sidebar "Monitoring" (opsional, frontend)
- [ ] Set cron `pulse:check` di production (../deployment-checklist.md)
- [ ] Pertimbangkan PULSE_INGEST_DRIVER=redis saat beban tinggi

### 17.74 Eksekusi Sisa Todo (2026-10-12)

Batch pengerjaan todo terbuka (tanpa QZ Tray -- dibatalkan 17.73):

- **Pulse scheduler**: `pulse:check --once` everyMinute + withoutOverlapping
  di `routes/console.php` (pulse:check tanpa argumen adalah daemon loop;
  alternatif daemon tersedia via Supervisor). Menu sidebar "Monitoring"
  ditambah di `config/menus.php` (permission `system.monitor.view`,
  url `/pulse`) -- sebelumnya item 32.8.
- **Deployment artifact**: `deploy/supervisor/klinik-{reverb,worker,pulse}.conf`
  + `deploy/nginx/klinik-websocket.conf` siap pakai; deployment-checklist.md
  diupdate (referensi config, cron `schedule:run` wajib, catatan
  `PULSE_INGEST_DRIVER=redis`).
- **PHPStan modul Clinical** (29.3.1): scope masuk `phpstan.neon`.
  35 error -> 0. Penyebab utama: relasi Eloquent tanpa return type
  (Larastan hanya mengenali relasi bertipe) -- ditambahkan `: BelongsTo`
  / `: HasMany` di LabOrder, RadiologyOrder, Registration, Encounter,
  Dispensing, DispensingItem. Bug nyata ikut tertangkap:
  `SatusehatMenuRepository` memakai key config `org_uuid`/`client_uuid`
  yang tidak ada di services.php (yang benar `org_id`/`client_id`),
  dan 6 pemanggilan `env()` di luar config yang gagal saat
  `config:cache` -- dibersihkan ke `config()` murni.
- **Fase 7 (partial)**: `src/app/Models` (folder kosong) dan sisa
  namespace legacy layer-first di `app/` dibersihkan. Catatan:
  `app/Livewire/PublicKanbanBoard.php` masih tracked & tidak direferensikan
  route mana pun -- kandidat hapus, menunggu keputusan user.
- **Boundary baseline**: audit ulang menunjukkan 33 entri baseline semuanya
  stale (strict mode aktual = 0 pelanggaran; kode sudah diperbaiki di
  iterasi sebelumnya). Baseline di-regenerate ke 0 -- ratchet kini ketat.
- **Docs**: baris task #9 Section 18 (17.73) dikoreksi SELESAI (17.36).

Verifikasi: `phpstan analyse` penuh OK (0 error); test Satusehat/AppMenu/
Sidebar 53 pass; test Clinical terkait 284 pass (4 skipped); full suite
dijalankan (lihat hasil). Item yang memang butuh server production tetap
terbuka: install Redis + PHP 8.3/OPcache, SSL/WSS + Nginx proxy pasang,
eksekusi k6 gate production-like.

### 17.75 Iterasi Scope Lanjutan (2026-10-12)

- Audit 128 model terhadap kolom `company_uuid`/`branch_uuid` pada skema
  aktual menemukan 34 mismatch. Dari itu, 8 model adalah pengecualian
  eksplisit (`UserBranch`, `UserCompany`, `Branch`, `File`, `FileAudit`,
  `FileVariant`, `AuditLog`, `LookupSet`), sisanya 26 model dipasang
  trait scope sesuai kolom.
- Tambah `HasCompanyScope` + `HasBranchScope` pada `Account`,
  `MasterProduct`, `Person`, `MasterCustomer`, `SupplierRecord`,
  `PersonBalance`, `PersonSaldo`, `ProductPrice`, `ItemCustomField`,
  `ItemDiscount`, `ItemDiscountRule`, `Bom`.
- Tambah `HasCompanyScope` pada model transaksi/klinis yang sudah
  ber-branch: `Complaint`, `Encounter`, `EncounterItem`, `Patient`,
  `Registration`, `Sale`, `SaleItem`, `Employee`, `ServicePackageItem`,
  `RecurringJournal`, `AccountBalance`, `ClosingPeriod`, `Payable`,
  `Receivable`.
- `ProductPrice` dikeluarkan dari pengecualian lama karena `item_prices`
  kini punya kedua kolom scope; catatan pembaruan ditambahkan ke 30.8.
- `HasBranchScope` kini mengisi `company_uuid` dari branch saat creating
  bila masih kosong, menjaga konsistensi scope pada data yang dibuat dari
  branch context.
- Bersihkan sisa fallback `auth()->user()->company_uuid/branch_uuid` di
  middleware/controller/service ke helper `current_company_uuid()` /
  `current_branch_uuid()`.
- Seeder demo memasang kolom scope pada Account, AccountBalance, dan
  ClosingPeriod agar data demo tidak jatuh ke scope kosong.
- Lanjutkan audit raw query ke laporan keuangan:
  `FinancialReportRepository` kini memfilter company pada income statement,
  balance sheet, cash flow indirect, adjusted trial balance, equity statement,
  AR/AP aging, inventory, fixed asset, dan tax report. Company untuk laporan
  berbasis cabang diturunkan dari `branch_uuid` sehingga jalur consolidated/
  cross-branch tetap konsisten.
- `GeneralLedgerRepository::getOpeningBalance()` kini memfilter
  `account_balances` dan journal mutations dengan `company_uuid` milik akun.
- `ClosingPeriodFactory` mengisi ulang `company_uuid` dari branch final lewat
  `afterCreating()`. Fixture terkait dikoreksi: periode journal tax, periode
  cabang B pada segment report, periode Feb/Mar KDM, dan akun modal KDM.
- Tambah `SuperadminMultiOrgScopeTest` untuk memastikan model dual-scope
  dapat diakses superadmin lintas company/cabang tanpa filter, tetap bisa
  difilter lewat `company_uuid`/`branch_uuid`, sementara user biasa tidak
  bisa lewat dari scope hanya dengan query parameter.
- Tambah helper demo runtime untuk superadmin CRUD: `demoCompany()`,
  `demoBranch()` (KDM), `demoSuperadmin()`, dan `actingAsDemoSuperadmin()`.
  Helper memastikan superadmin test punya pivot company/branch aktif sehingga
  `company_uuid`/`branch_uuid` terisi otomatis. Terapkan pada test service
  journal inter-branch dan lengkapi konteks akun/periode/mapping.

Verifikasi: `RemainingModelScopeTraitTest` 27 pass (60 asersi),
`SuperadminMultiOrgScopeTest` 4 pass (10 asersi), PHPStan file baru 0 error,
`phpstan analyse` penuh 0 error, boundary check 0 pelanggaran. Full suite
selesai hijau: 3147 passed, 55 skipped, 8802 assertions, 0 failed.
Verifikasi tambahan: `InteractsWithSandboxRuntimeTest` +
`JournalInterBranchCompleteTest` 4 pass (16 asersi); PHPStan 6 file terkait
0 error; boundary check 0 pelanggaran.

### 17.76 Perbaikan Alur Edit Produk (2026-10-12)

- Ubah navigasi edit produk dari URL `/master/products/{uuid}/edit` menjadi
  halaman `/master/products/create` dengan query `edit={uuid}`.
- `WebProductController::create()` kini memuat produk untuk mode ubah data,
  tetap memvalidasi scope company/cabang melalui service edit yang sama.
- URL legacy `/master/products/{uuid}/edit` diarahkan ke halaman create agar
  tautan lama tidak langsung putus.
- Perbarui test UI upsert produk untuk mengikuti URL baru.

Verifikasi: PHP lint controller, PHPStan file controller, dan typecheck
frontend lulus.

### 17.77 TDD Persistensi Form Produk (2026-10-12)

- Tambah test fitur upsert produk yang mengubah seluruh input utama dan
  seluruh tab, lalu memastikan setiap nilai benar-benar tersimpan ke tabel DB
  dan ter-load ulang ke payload edit.
- Test menemukan `base_unit_uuid` dari form produk tidak diterima API sehingga
  satuan dasar menjadi null saat update; validasi store/update produk kini
  menerima UUID satuan yang valid.
- Perkuat assertion database untuk lookup, satuan, akun, saldo awal, harga,
  dan diskon, termasuk kolom `location`/`storage_bin_code`.

Verifikasi: `ProductFormUpsertTest` 5 pass (72 asersi); PHP lint controller
dan file test lulus.

### 17.78 Penyelarasan Test UI Produk (2026-10-12)

- Hilangkan alur login manual dari test upload/reorder pada view upsert produk
  dan gunakan state autentikasi Playwright agar test mengikuti setup E2E.
- Pastikan seluruh test pada file `product.form.upsert.view.spec.ts` berjalan
  tanpa login form berulang.

Verifikasi: file test Playwright produk 3 pass; `ProductFormUpsertTest`
5 pass (72 asersi); PHP lint file PHP yang berubah lulus.

### 17.79 Sinkronisasi Master Produk dari Main (2026-10-12)

- Ekstrak seluruh 353 baris produk dari dump database `main` menjadi
  `src/database/data/products-main.json`.
- `ProductSeeder` kini memuat data tersebut, memetakan harga, satuan, kategori,
  merek, status aktif, dan stok awal, lalu menulis saldonya ke gudang utama.
- Daftarkan `ProductSeeder` pada `V1DatabaseSeeder` setelah warehouse agar
  fresh seed memiliki master produk lengkap dari main.
- Seeder dijalankan dan menghasilkan 353/353 kode produk dari sumber main;
  total item lokal 358 karena lima produk demo transaksi tetap dipertahankan.

Verifikasi: `ProductSeeder` lulus; `ProductFormUpsertTest` 5 pass
(72 asersi); PHP lint seeder lulus.

### 17.80 Penyempurnaan Mapping Produk Main (2026-10-12)

- Seeder produk kini memetakan field yang benar-benar tersedia di dump main:
  kelompok, kategori/tipe, merek, jenis barang, satuan, harga, stok, dan status.
- Nilai lookup produk dibuat bila belum ada agar select pada form produk langsung
  terisi sesuai data asal.
- HPP awal dan standard cost mengikuti harga beli dari main sebagai nilai awal
  yang masih bisa dikoreksi user.
- Field seperti barcode, deskripsi, kemasan, lokasi bin, COA, dan harga/diskon
  bertingkat tidak dipaksa diisi karena tidak tersedia pada dump main.

Verifikasi: `ProductSeeder` update 353/353 produk; `ProductFormUpsertTest`
5 pass (72 asersi); PHP lint seeder lulus.

### 17.81 Suite E2E TesterArmy Lintas Modul (2026-10-12)

- Tambah setup TesterArmy (`src/e2e.config.ts`) beserta skill/reference lokal dan
  konfigurasi MCP yang dipakai runner.
- Tambah helper E2E API untuk login JWT, request Bearer, ekstraksi UUID,
  resource pertama, unique code, dan cleanup yang toleran gagal.
- Tambah suite API per modul: `auth`, `core`, `platform`, `clinical`,
  `operational`, `finance`, `analytics`, `security`, `tenant`, dan
  `contracts`.
- Tambah smoke UI untuk 140 halaman lintas Core, Clinical, Finance,
  Operational, dan Analytics; plus UI fokus untuk login dan form produk.
- Suite tenant memverifikasi isolasi data melalui header company/branch,
  superadmin bypass eksplisit, akses non-member 403, dan data lintas scope
  404 untuk user biasa.
- Suite security memastikan endpoint representatif tiap domain menolak akses
  anonim dan mengembalikan 404 untuk UUID yang tidak ada.

Temuan QA (belum diperbaiki pada iterasi ini):

- `POST /api/v1/registrations` gagal 500 ketika `registered_at` tidak dikirim,
  sementara timestamp epoch ditolak 422 dan kolom DB bertipe integer.
- `GET /api/v1/dashboard/tab-data` gagal 500 karena method controller tidak ada.
- `GET /api/v1/settings/roles`, `GET /api/v1/master/icd-codes`,
  `GET /api/v1/clinic/lab-orders`, dan
  `GET /api/v1/clinic/radiology-orders` gagal 500.
- `POST`/`GET /api/v1/settings/users/{uuid}` untuk lifecycle user gagal 500.
- `PUT`/`DELETE /api/v1/masters/treatments/{uuid}` gagal 500 karena method
  controller belum tersedia; scenario lifecycle ini ditandai skip.
- Beberapa endpoint store mengembalikan 200, bukan 201; test mengikuti kontrak
  aktual untuk mencegah false negative sampai keputusan kontrak dieksekusi.

Verifikasi: `npm run typecheck` lulus; `npx e2e list` menemukan 190 test
(termasuk setup dan 1 skip known-gap); full smoke 140/140 pass; seluruh suite
API/UI fokus pass saat dijalankan per file; suite security/tenant/contracts
baru pass (5 test, 1 skip). Tidak ada commit/push.

### 18.191 CI: Verifikasi opening balance General Ledger

#### Status
- Selesai (verifikasi perbaikan existing) - 2026-10-23.

#### Temuan
- `GeneralLedgerRepository::getOpeningBalance()` menjumlahkan saldo periode
  `Opening` dan mutasi jurnal posted sebelum tanggal target.
- Mutasi debit/kredit dikonversi sesuai arah normal akun, sehingga prior
  mutation tidak lagi terabaikan.
- Perbaikan existing sudah terbukti oleh test
  `GeneralLedgerRepositoryTest::test_get_opening_balance_includes_opening_period_and_prior_mutations`.

#### Validasi
- PHPUnit fokus: 1 test, 1 assertion lulus.
- Pint fokus: pass.
- PHPStan fokus: no errors.
- Bukti perbaikan public:
  https://github.com/haidi20/inventory-erp/commit/9de3ab476.

### 18.566 CI: Coverage Gate Playwright

#### Status
- Kartu `[Bugfix][P1] CI: Coverage gate Playwright lulus 95%` dikerjakan
  Codex CLI.
- Suite SDK `e2e` dipisahkan dari loader Playwright sehingga konflik
  `@vitest/expect` tidak lagi menggagalkan job.
- Workflow CI memakai `e2e:ci` sebagai smoke suite Playwright untuk gate.

#### Hasil
- Project chromium memakai `testIgnore: ['tests/E2E/**']` sehingga file
  `tests/E2E/*.e2e.ts` milik SDK `e2e` tidak dimuat oleh Playwright.
- Script `e2e:ci` menjalankan smoke test Inventory real-data dengan
  reporter JUnit CI dan mengecualikan dua scenario yang masih bergantung
  pada sinkronisasi branch/fixture terpisah.
- Workflow Playwright memakai `npm run e2e:ci` sebelum coverage gate.
- Utang test lama dan fixture POS/Financial tetap teridentifikasi sebagai
  pekerjaan terpisah, tidak disembunyikan sebagai lulus.

#### Validasi
- Reproduksi merah sebelum patch: Playwright gagal pada
  `TypeError: Cannot redefine property: Symbol($$jest-matchers-object)`.
- Validasi hijau: `npm run typecheck` lulus.
- Validasi hijau public lintas-branch: `npm run e2e:ci` lulus 5 test dan
  gate 100% (5/5 scenarios).
- Reproduksi merah CI public `5377a5d1`: PHP CLI server mengalami
  `Segmentation fault` saat request `/inventory`.
- Perbaikan public `864265a2`: server Playwright dikelola script satu
  worker, runtime PHP Playwright dipin `8.2.28`, dan log E2E disimpan
  sebagai artifact.
- Validasi guard runtime merah sebelum patch, lalu hijau setelah patch.
- CI public `40624009447` hijau penuh; Playwright lulus 5 test dan
  coverage gate mencapai 100% (5/5 scenarios).
- Validasi hijau private setelah fresh seed: `npm run e2e:ci` lulus 6 test
  dan gate 100% (6/6 scenarios).
