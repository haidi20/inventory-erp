# path
- D:\projects\klinik-git\src\guides\modularization\_plan.md
- D:\projects\klinik-git\src\guides\modularization\_progress.md

# Rencana Modularisasi src/ (Klinik + ERP)

> File ini sumber keputusan & rencana stabil. Status eksekusi, todo list,
> checklist, dan log eksekusi dipindah ke `_progress.md`.

## 1. Tujuan

- Memecah app/ yang saat ini disusun layer-first (Entities/, Services/, Repositories/, dst berisi semua domain campur) menjadi module-first, supaya satu modul bisa dibaca, diuji, dan dikembangkan tanpa perlu memahami seluruh sistem.
- Menjaga batas jelas antara domain Klinik (pelayanan medis) dan domain ERP (inventori, penjualan, pembelian, akuntansi) yang saat ini bercampur di satu app/.
- Tidak mengubah pola arsitektur yang sudah baku di proyek (Controller -> Service -> Repository -> Model, dengan Entity sebagai DTO kontrak request/response antar layer; UUID, audit columns, company/branch scope, TDD) -- modularisasi ini soal penataan folder dan namespace, bukan mengganti arsitektur.

## 2. Kondisi Saat Ini (Sebelum Modularisasi)

app/ disusun layer-first dengan 13 sub-namespace domain campur di setiap layer:


Masalah: domain klinis (Patient, Encounter, Lab, Radiology) dan domain ERP (Item, Sales, Purchase, Accounting) tersebar di banyak folder yang sama, tidak ada batas fisik antar bounded context.

## 3. Keputusan Modul

Setelah diskusi, disepakati 5 modul bisnis + 1 lapisan shared/framework. Tidak ada modul top-level Master -- master data diletakkan sebagai sub-modul di dalam modul pemilik domainnya.

| Modul | Cakupan Singkat |
|---|---|
| Core | Identitas dan platform: Company, Branch, User, Auth, RBAC (Role/Permission/AppMenu), Settings sistem |
| Clinical | Pelayanan medis: Pendaftaran, rekam medis, farmasi, lab, radiologi, klasifikasi penyakit (ICD-9/ICD-10), integrasi kesehatan resmi |
| Operational | ERP inti: master data barang/mitra, inventori, penjualan/POS, pembelian |
| Finance | Akuntansi dan keuangan: jurnal, ledger, laporan keuangan, kas/bank, budget |
| Analytics | Laporan dan dashboard lintas modul |

Plus:

| Layer | Isi |
|---|---|
| Shared (bukan modul bisnis) | Traits (Auditable, Blameable, HasBranchScope, dst), global Scopes (CompanyScope, BranchScope), base class Entity/Repository/Service abstrak, Support/Helpers lintas modul, Providers global, Middleware global |

Catatan penamaan: nama modul tetap Bahasa Inggris (konsisten dengan namespace lama Accounting, MasterData, Operational), Bahasa Indonesia hanya untuk label UI. Nama Clinical dipakai (bukan Clinic) supaya tidak tumpang tindih dengan konsep institusi klinik yang sudah dipegang Core (Company, Branch, ClinicProfile); Clinical merujuk ke aktivitas medis.

### 3.1 Freeze URL Endpoint Lama vs Final

Status endpoint API sudah di-freeze dengan format tabel mapping per modul. Dokumen detail ada di `Section 19` dan menjadi acuan saat modularisasi route/controller/service agar URL final tidak berubah saat folder/namespace dipindahkan.

| Area Modul | Status Mapping | Acuan Final | Catatan |
|---|---|---|---|
| Auth & Akses | final | `/api/v1/auth/*`, `/api/v1/users`, `/api/v1/roles`, `/api/v1/permissions`, `/api/v1/app-menu/sidebar` | Login/logout, user, RBAC, dan menu sidebar dibekukan sebagai Core |
| Master Data | final + beberapa konsolidasi | `/api/v1/master/*`, `/api/v1/lov/*` | Master bukan top-level module; secara ownership masuk Core/Operational/Clinical sesuai domain |
| Saldo Awal & Akuntansi | final | `/api/v1/accounting/*`, `/api/v1/master/opening-balances*`, `/api/v1/master/initial-balances*` | Finance menjadi pemilik COA, mapping akun, aset, closing, dan laporan keuangan |
| Pembelian | final | `/api/v1/purchase-orders`, `/api/v1/purchases`, `/api/v1/purchase-returns` | Masuk Operational/Procurement |
| Penjualan | final + POS perlu konsolidasi | `/api/v1/sales-orders`, `/api/v1/delivery-orders`, `/api/v1/sales/invoices`, `/api/v1/transactions` | Sales/POS masuk Operational/Sales |
| Inventory | final | `/api/v1/inventory/*`, `/api/v1/ingredients` | Stock balance, adjustment, transfer, assembly, ingredients masuk Operational/Inventory |
| Integrasi & Settings | final | `/api/v1/settings*`, `/api/v1/satusehat/*` | Settings umum masuk Core/Platform; SATUSEHAT/BPJS masuk Clinical/Integration |

Keputusan freeze yang perlu dijaga saat modularisasi:
- `/api/v1/master/products` adalah endpoint final master product; `/api/v1/products` hanya alias/kompatibilitas jika masih dipakai FE.
- `POST /api/v1/transaction` dipertahankan sementara untuk kompatibilitas POS; target final ideal adalah `POST /api/v1/transactions` jika FE siap migrasi.
- `/api/v1/warehouses` dipakai khusus lookup active; CRUD warehouse tetap di `/api/v1/master/warehouses`.
- Legacy NineSoft seperti `/site/master/formula` dipetakan ke API final `/api/v1/master/boms`; label UI boleh tetap Formula.
- Perubahan endpoint setelah freeze wajib update `Section 19` lebih dulu sebelum mengubah route/FE datasource.

## 4. Batas Tanggung Jawab per Modul (dengan Sub-modul)

Setiap sub-modul di bawah ini BUKAN modul top-level, melainkan grouping di dalam satu modul: tetap satu route/service-provider/scope migration per modul. Hierarki struktural maksimal hanya dua level: Module -> SubModule. Sub-module tidak boleh punya sub-module lagi; entitas/domain di bawahnya cukup sebagai folder domain, Service, Controller, Model, atau Repository di dalam SubModule.

### 4.1 Core (modul top-level)
Grouping internal di bawah namespace `App\Modules\Core` -- 4 sub-modul, tetap 1 ServiceProvider dan 1 scope migration per modul (bukan modul top-level terpisah):

- **Organization**: Company, Branch, ClinicProfile.
- **Identity**: User, UserBranch, UserWarehouse, Auth (login/JWT, PasswordReset).
- **Authorization**: Role, Permission, RolePermission, AppMenu (+ pivot model_has_roles / model_has_permissions / role_has_permissions).
- **Platform**: DatabaseBackup, PrintTemplate, WhatsappTemplate / WhatsappLog, CalendarEvent.

Aturan internal Core: `Platform -> Organization`; `Identity -> Organization`; `Authorization -> Identity`; tidak boleh sebaliknya (Identity tidak import Authorization, Platform tidak di-import balik). Arah ini 1:1 dengan dependency yang sudah ada.

### 4.2 Clinical (modul top-level)
Grouping internal di bawah namespace App\Modules\Clinical:
- Registration: Patient, Registration (+antrian).
- MedicalRecord: Encounter (+Vital, +Physical, +Diagnosis), Complaint, DoctorTask, Prescription, ClinicProfile.
- Pharmacy: Dispensing (+Item) -- farmasi; kurangi stok dengan memanggil Service Inventory milik Operational yang menerima/mengembalikan Entity/DTO, bukan query tabel stok langsung.
- Ancillary: LabOrder (+Item), RadiologyOrder.
- Classification: Icd9Code, Icd10Code.
- Integration: SatusehatSettings, BpjsSettings, service integrasi ke portal SATUSEHAT/BPJS.

### 4.3 Operational (modul top-level)
Grouping internal di bawah namespace App\Modules\Operational (4 sub-modul):
- Master: Item/Product, Supplier, Location (ex-Warehouse, sudah di-rename), Unit, Tax, PaymentTerm, PriceLevel, Person, Employee, ItemConversion, ItemUom, ItemSupplierRelation, ItemRelationalPrice, ItemStok, ItemOpeningBalance. Employee masuk ke Master (dipakai Sales/Procurement kasir + HRD). Modul Clinical yang butuh data dokter (mis. untuk Encounter/Prescription/DoctorTask) membaca lewat Service Operational\Master\EmployeeService yang menerima/mengembalikan EmployeeEntity, bukan import Model Employee langsung.
- Inventory: Stock, StockMovement, StockTransfer (+Item), StockAdjustment (+Item), BOM (+Detail+Cost), Assembly (+Item), Ingredient.
- Sales (termasuk POS): Sale, SalesOrder (+Detail), SalesTransaction (Header/Detail/Payment/Aging/Discount), DeliveryOrder (+Detail), Shift, Transaction (+Detail) legacy POS.
- Procurement: PurchaseOrder (+Item), Purchase (+Item), PurchaseReturn (+Item+Payment), ImportJob (+Detail).
- Master (Lookup): LookupSet, LookupValue, LookupValueDetail, LookupType, TempLookup, TempLookupDetail. Domain Lookup/LOV dimiliki Operational\Master, bukan Core\Platform. Scope perusahaan: LookupValue (parent) TIDAK company-scoped (global); LookupValueDetail (detail) yang company-scoped.

Aturan anti-salah-paham master: master data TIDAK dijadikan modul top-level Master. Kelas Item dsb dimiliki Operational\Master. Modul lain yang butuh data itu mengakses lewat Service publik yang di-expose Operational dan berbasis Entity/DTO, bukan lewat query lintas-modul ke model atau memindahkan tabel ke modul lain.

### 4.4 Finance (modul top-level)
Tanpa sub-modul di fase awal; jika membesar bisa dipecah nanti (misal GeneralLedger vs CashBank vs Budget). Isi: Account, AccountMapping, Coa, CoaSetup, Asset, ProductAccount, TaxSetting, ClosingPeriod, Journal (+Line+Attachment), AccountBalance, RecurringJournal (+Line), GeneralLedger, TransactionSequence, Currency (+Rate), Cheque, BankReconciliation (+Detail), Budget (+Line), ApprovalLog. Service perhitungan FinancialReport* dan CashFlow* tetap milik Finance sebagai service publik laporan (dipanggil Analytics\Reports); endpoint dan presentasi laporan ada di `Analytics\Reports`, bukan sub-modul Finance.

### 4.5 Analytics (modul top-level)
Sub-modul: Dashboard, Reports. Isi: Dashboard, laporan lintas modul, dan laporan keuangan (cash-flow, income-statement, balance-sheet, trial-balance, financial-reports) yang menarik data dari Clinical + Operational + Finance tanpa memiliki tabel domainnya sendiri (kecuali tabel snapshot/cache report bila perlu performa). Read-only terhadap modul lain; perhitungan laporan keuangan memakai service publik Finance (FinancialReport*/CashFlow*), tidak duplikat logic.

## 5. Pemetaan Namespace Lama -> Modul Baru

| Namespace Lama (Entities/Services/Repositories/*) | Modul Baru (sub-modul) |
|---|---|
| Auth | Core |
| AppMenu | Core |
| Settings | Core (Backup/Print/WhatsApp/Calendar), Clinical (SatusehatSettings/BpjsSettings) |
| Infrastructure | Core, atau Shared untuk item murni teknis lintas modul |
| Integration | Clinical (sub-modul Integration, SatuSehat/BPJS) |
| Icd10 (master lama) | Clinical (sub-modul Classification) |
| MasterData -> Patient | Clinical (sub-modul Registration) |
| MasterData -> Icd10Codes | Clinical (sub-modul Classification) |
| MasterData -> Item/Supplier/PaymentTerm/Tax/Uom/Person/PriceLevel | Operational (sub-modul Master) |
| MasterData -> Employee | Operational (sub-modul Master) - sudah diputuskan |
| Operational lama (bagian Klinik) -> Registration/Encounter/Complaint/DoctorTask/Dispensing/Lab/Radiology | Clinical (sub-modul Registration/MedicalRecord/Pharmacy/Ancillary) |
| Operational lama/baru (bagian ERP) -> Sale/Purchase/Stock/Bom/Assembly/DeliveryOrder/Shift/Transaction | Operational (sub-modul Sales/Inventory/Procurement) |
| Inventory | Operational (sub-modul Inventory) |
| Procurement | Operational (sub-modul Procurement) |
| Accounting | Finance |
| Reporting + Analytics | Analytics |
| Management | tidak dibuat modul terpisah; isi lama dipetakan langsung ke Core atau Operational sesuai domain |

Catatan: namespace Operational lama wajib audit file-per-file saat eksekusi karena isinya campur klinik + ERP; bagian Klinik dipetakan ke Clinical dan bagian ERP tetap dipetakan ke Operational. Jangan pindah folder secara mentah.

## 6. Struktur Folder Modular (Usulan)

Arah: module-first di dalam `modules/{Module}/{SubModule?}/...` (folder `modules/` di root `src/`; composer memetakan PSR-4 `App\Modules\` -> `modules/`, sehingga penulisan `app/Modules/` pada tabel roadmap merujuk lokasi yang sama), dengan namespace `App\Modules\{Module}`.


Rule penamaan: nama modul (Core/Clinical/Operational/Finance/Analytics) dan sub-modul (Master/Inventory/Sales/Procurement, Registration/MedicalRecord/Pharmacy/Ancillary/Classification/Integration) harus konsisten dipakai di namespace + folder, tidak boleh disingkat. Struktur namespace maksimal dua level: App\Modules\{Module}\{SubModule}. Folder tambahan di bawahnya hanya organisasi domain/teknis dalam SubModule, bukan sub-module baru; contoh yang benar adalah Operational\Sales dengan domain POS di dalamnya, bukan Operational\Sales\POS sebagai sub-module.

### 6.1 Konvensi Path Setup Module (Wajib Dideklarasikan di Sini, Bukan Hardcode di Code)

Setiap modul wajib mendaftarkan path-nya (views, routes, migrations, factories) lewat `ModuleServiceProvider` -- bukan disebar di controller atau provider lain. Konvensi namespace path:

| Resource | Lokasi Folder | Cara Registrasi | Namespace / Prefix |
| --- | --- | --- | --- |
| Views | `modules/{Module}/{SubModule}/resources/views/` | `loadViewsFrom($path, $namespace)` di ModuleServiceProvider | `{module}.{submodule}` (lowercase, titik) -- contoh: `core.platform::settings.database` |
| Routes (web) | `modules/{Module}/{SubModule}/routes/web.php` | Load di ModuleServiceProvider `boot()` | Prefix sesuai domain (`/master`, `/satusehat`, dll) |
| Routes (api) | `modules/{Module}/{SubModule}/routes/api.php` | Load di ModuleServiceProvider `boot()` | Prefix `/api/v1/` + domain |
| Migrations | `modules/{Module}/{SubModule}/database/migrations/` | `loadMigrationsFrom($path)` di ModuleServiceProvider | -- |
| Factories | `modules/{Module}/{SubModule}/database/factories/` | Auto-discovery via `HasFactory` + namespace match | `Database\Factories\Modules\{Module}\{SubModule}\Models\` |
| Seeders | `modules/{Module}/{SubModule}/database/seeders/` | Daftar di `DatabaseSeeder` atau load per-module | `Database\Seeders\V1\` |

Aturan:
- View namespace WAJIB konsisten: `{module}.{submodule}::{view}` -- jangan pakai path blade global tanpa namespace (contoh salah: `view('settings.database')`, contoh benar: `view('core.platform::settings.database')`).
- Route prefix WAJIB konsisten dengan mapping endpoint di Section 19.
- Binding Interface -> Repository WAJIB dideklarasikan di `AppServiceProvider::register()`, bukan di controller.
- Path setup ini menjadi sumber kebenaran tunggal -- developer cukup baca tabel ini, tidak perlu bongkar code untuk tahu namespace view atau prefix route suatu modul.

## 7. Aturan Dependency Antar Modul

- Arah satu arah wajib: Clinical -> Core; Operational -> Core; Clinical <-> Operational lewat Service publik modul pemilik domain dengan Entity/DTO, bukan langsung ke model. Tidak boleh sebaliknya (Core tidak import dari 4 modul lain).
- Clinical dan Finance bisa membaca/menulis data milik Operational (mis. Inventory stok berkurang saat Dispensing di Clinical) hanya lewat Service publik yang disediakan oleh Operational, bukan query ke Model di modul lain.
- Event/Listener untuk kasus "tulis ke modul lain" supaya longgar: contoh DispensingCompleted -> listener Finance/Inventory update jurnal stok.
- Analytics: read-only, tidak boleh tulis ke tabel domain modul lain.
- Tidak boleh ada cross-module import tanpa lewat Service publik berbasis Entity/DTO -- bisa ditegakkan dengan helper CI (deteksi use dari nama modul lain di file lain).

## 8. Cakupan Migration & Database

- 1 database tetap (sesuai keputusan multi-company). Yang dipecah hanya lokasi file migration per modul di app/Modules/{Module}/database/migrations/{submodule?}.
- Loader migration: masing-masing ModuleServiceProvider memanggil loadMigrationsFrom(...) untuk folder migration miliknya (Laravel 11/12 sudah support discovery dari provider), atau via bootstrap/providers.php.
- Urutan: Core -> Finance -> Clinical -> Operational -> Analytics (Core karena companies/branches/users jadi dependency FK-implisit; Analytics paling akhir karena bergantung ke semuanya).
- Penamaan file tetap pakai tanggal standard + prefix supaya mudah sorting, contoh: 0001_01_00_000000_create_branches_table.php di folder Core/database/migrations.

## 9. Cakupan Test

- tests/Feature/* dan tests/Unit/* yang sekarang pusat perlu dipetakan struktur baru: tests/Feature/Modules/{Module}/{SubModule?}/{ControllerName}/{Scenario}Test.php.
- Standar TDD tidak berubah: happy path, validation error, data not found, unauthorized, duplicate case.
- Test wajib verifikasi isolation company/branch yang default ke sandbox.

## 10. Fase Migrasi (Non-Destruktif, Bertahap)

1. Fase 0 -- Persiapan: finalisasi pemetaan domain (section 5), keputusan Employee, dan struktur folder yang dipilih (section 6).
2. Fase 1 -- Core: pindahkan Company, Branch, User, Auth, RBAC, Settings (Lookup sudah dipindahkan ke Operational\Master). Sediakan pola ModuleServiceProvider + loadMigrationsFrom sebagai acuan modul lain. Tambah helper CI cek arah dependency.
3. Fase 2 -- Finance: domain terisolasi (sudah "global/shared by design"); pindahkan tanpa banyak kontrak publik.
4. Fase 3 -- Clinical (bagian 1): pindahkan Registrasi + MedicalRecord. Pisahkan dari Operational lama.
5. Fase 4 -- Operational (bagian 1 + Master): pindahkan Master (Item, Supplier, Warehouse) + Sales (termasuk POS & TransactionSequence), sambil tetapkan Service publik untuk stok/pembacaan item.
6. Fase 5 -- Clinical (bagian 2: Pharmacy, Ancillary, Classification, Integration) + Operational (bagian 2: Inventory, Procurement). Pharmacy/Ancillary memanggil Inventory/Operational via Service publik berbasis Entity/DTO.
7. Fase 6 -- Analytics. Pindahkan reporting & dashboard.
8. Fase 7 -- Bersihkan namespace lama Entities/Services/Repositories/Http/Models/*; semua pemakaian wajib langsung memakai namespace modular App\Modules\... tanpa alias/wrapper deprecated.

Setiap fase: pindah file -> update namespace + use statement -> update alias route/permission -> jalankan test spesifik modul & test cross-module integration terkait -> baru lanjut fase berikutnya. Tidak boleh pindah banyak modul sekaligus tanpa verifikasi tiap step.

## 11. Standard yang Tetap Wajib Dipertahankan

Tidak berubah walau modularisasi (berlaku di semua modul):

- TDD di semua skenario.
- Migration wajib: UUID PK, audit columns created_by/updated_by/deleted_by, deleted_at bigInteger unsigned default(0), indexing.
- **Semua field waktu (created_at, updated_at, deleted_at, synced_at, dan field datetime domain lainnya) wajib memakai bigInteger Unix timestamp dalam satuan MIKRODETIK (Âµs).** Alasan:
  - Multi-timezone: timestamp mikrodetik disimpan dalam UTC, konversi timezone dilakukan di layer presentasi (FE/API response), bukan di database.
  - Go parity: Go backend dan PHP backend pakai format yang sama tanpa konversi.
  - Presisi: mikrodetik cukup untuk audit trail, event ordering, dan concurrency detection.
  - Model Laravel wajib set `protected $dateFormat = 'U';` -- ini sudah di-enforce oleh CI test `UnixTimeComplianceTest`.
  - deleted_at tetap memakai bigInteger unsigned default(0) (bukan softDeletes()) -- nilai 0 = tidak dihapus, nilai > 0 = timestamp mikrodetik penghapusan.
  - Kolom waktu domain (misal: `scheduled_at`, `paid_at`, `verified_at`, `expired_at`) ikut aturan yang sama: `bigInteger nullable`.
- Server-side listing (search, sort, pagination, per-page). Search per-kolom / semua field.
- Import/export dengan template 2 sheet (Data + Penjelasan), upsert memakai unique key yang disengaja.
- Scope company (company_uuid) dan branch (branch_uuid) langsung di tabel tenant-owned, bukan jalur created_by -> user -> branch -> company.
- Setiap kali menjalankan perintah/command, wajib aktifkan skill adhd (`$i-have-adhd`) supaya output action-first, terstruktur, dan mudah ditindaklanjuti.

- Pipeline: Controller -> Service -> Repository -> Model; Entity dipakai sebagai kontrak request/response antar layer (strong-typed DTO).
  - Controller mengirim Entity/Request-DTO ke Service; Service menerima/mengembalikan Entity/Response-DTO.
  - Repository menerima Entity/Request-DTO, memakai Eloquent Model untuk akses storage, dan mengembalikan Entity (mapping Model <-> Entity di dalam Repository).
  - Eloquent Model tidak pernah bocor keluar dari Repository -- tidak di-return ke Service/Controller atau lintas modul (lintas modul lewat Service publik yang berbasis Entity/DTO).
  - Return type eksplisit: Entity (bukan array mentah), Collection<Entity>, atau LengthAwarePaginator<Entity>.
- **Wajib pakai Eloquent Model untuk akses data, bukan `DB::table()` atau query builder mentah.** Alasan: Model memberikan scope otomatis (CompanyScope/BranchScope), audit trail (Auditable/Blameable traits), soft-delete unique, dan type safety. Pengecualian: `DB::table()` hanya boleh dipakai jika ada instruksi eksplisit untuk optimasi query (mis. raw query kompleks, bulk update massal, atau stored procedure) -- dan harus didokumentasikan alasannya.
- **Setiap method write di Repository wajib menggunakan try-catch + DB transaction eksplisit** (`beginTransaction` / `commit` / `rollBack`). Method write = `save`, `store`, `create`, `update`, `delete`, `destroy`, `upsert`, dan turunannya yang melakukan INSERT/UPDATE/DELETE ke storage. **Dilarang** pakai closure `DB::transaction(function () { ... })` -- seluruh codebase sudah dimigrasi ke pola eksplisit. Pola: `DB::beginTransaction()` + `try { ... DB::commit(); return $result; } catch (\Throwable $e) { DB::rollBack(); throw $e; }` untuk method non-void, dan `DB::beginTransaction()` + `try { ... DB::commit(); } catch (\Throwable $e) { DB::rollBack(); throw $e; }` untuk method `: void`. Method read-only (getPageData, findByUuid, listForExport, dll.) tidak perlu di-wrap. Pengecualian: method yang mix operasi shell/file I/O + DB (mis. backup mysqldump) hanya wrap bagian DB-nya, bukan seluruh method, agar transaksi tidak ditahan selama operasi lambat. **Status: 66 file Repository, 155 method write -- semua sudah compliant (audit 2026-09-19).**


## 11b. Kebijakan Data Antar Layer & Antar Modul

Untuk menghindari kebocoran Eloquent Model dan menguatkan type safety, berlaku aturan berikut:

### 11b.1 Batas layer
- **Controller** -> **Service**: hanya Entity/DTO (request dan response), bukan array mentah bukan Model.
- **Service** -> **Repository**: hanya Entity/Request-DTO; Service tidak menyentuh Model.
- **Repository** -> **Storage**: Repository adalah satu-satunya yang menyentuh Eloquent Model (serta query scope company/branch/soft-delete). Mapping Model <-> Entity eksplisit di lapisan Repository, bukan di Service/Controller.
- **Validation** (FormRequest): menghasilkan Entity/DTO lalu passing ke Service/Repository; tidak membuat array mentah sebagai kontrak.

### 11b.2 Strong-typed Entity
- Entity adalah DTO immutable/semifinal; field typed (mis. `readonly string $uuid; readonly Money $price;`). Tidak ada `mixed`/`array` tanpa bentuk.
- Source of truth ketikanya ada di Entity/DTO, bukan di request body/array atau Eloquent attribute cast. Cast Eloquent cuma cek kompatibilitas Model.
- Antar modul (lintas bounded context) hanya lewat Service publik yang menerima/mengembalikan Entity/DTO (mis. EmployeeService::findEntity(string $uuid): ?EmployeeEntity). Tidak boleh import Model modul lain.
- Domain yang butuh versi baca-tulis berbeda boleh punya dua DTO: {Entity}Request dan {Entity}Response (mis. ItemRequestEntity vs ItemEntity).
- Gunakan collection<K,V>/paginator generic typehint (Collection<int, Entity>, LengthAwarePaginator<Entity>) supaya consumer tetap strong-typed.

### 11b.3 Verifikasi otomatis (CI/Lint)
Aturan batas layer di atas ditegakkan lewat audit/CI, bukan hanya disiplin manual:
- Service di forbidden-namespace: tidak boleh `use App\Modules\{AnyModule}\Models\*` maupun `use App\Models\*`. Scan `app/Modules/*/Services/**` + grep -> fail.
- Controller tidak boleh `use App\Models\*` atau memanggil Repository langsung (`Repository::`/`new *Repository`). Akses model wajib lewat Service.
- Repository tidak boleh di-import oleh modul lain (mis. `use App\Modules\Operational\Repositories\...` dari modul Clinical). Akses lintas modul hanya lewat Service publik modul pemilik domain dan Entity/DTO.
- Keputusan final: tidak membuat alias/deprecated namespace lama (Entities/Services/Repositories/Http/Models). CI menolak `use` namespace lama untuk semua file; setelah modul dipindah, namespace lama langsung dihapus.
- Helper CI reuse: satukan dengan pengecekan arah dependency (Fase 1) jadi satu job `ci:module-boundaries`.
- Status implementasi (2026-09-18): job sudah dibuat di `scripts/check-module-boundaries.php`, didaftarkan sebagai composer script `ci:module-boundaries`. Mendukung baseline ratchet:
  - `php scripts/check-module-boundaries.php` -- cek normal (toleransi pelanggaran baseline, fail hanya pada pelanggaran BARU).
  - `--generate-baseline` -- tulis baseline dari status kini (raxket gelondongan kasus-kasus lama).
  - `--strict` -- audit penuh, tolak semua pelanggaran termasuk yang terdaftar baseline.
  - Jalankan `composer ci:module-boundaries` di pipeline. 33 pelanggaran lama saat ini sudah masuk baseline; ratchet diturunkan saat tiap pelanggaran diperbaiki.


### 11b.4 `Business/code logic hanya di Service`
Seluruh `code logic` dan aturan bisnis (business rules) berada di layer `Service`. Pembagian tanggung jawab antar layer:
- `Controller` (thin): hanya menerima Request-DTO/validated data, memanggil `1` method Service, dan mengembalikan response (Resource/Entity). Tidak memuat cabang keputusan bisnis, `if` filter/status, perhitungan, loop antar entitas, query, atau pemanggilan Repository/Model.
- `Service` (satu-satunya pemilik logic bisnis): validasi semantik/domain (mis. stok cukup, status boleh diubah, approval berjenjang), perhitungan (harga, total, diskon, pajak, saldo, jurnal balance), orkestrasi transaksi ( `DB::transaction` ), pemanggilan antar-`Service` modul lain berbasis Entity/DTO, dispatch Event untuk efek lintas modul. Semua `if`/`switch`/algoritma/aturan domain tinggal di sini.
- `Repository` (pure persistence): hanya bangun query Eloquent Model, `where`/`join`/`orderBy`/`paginate`, `withTrashed`, mapping `Model <-> Entity`. Tidak boleh ada `business rule` di Repository (mis. tidak memutuskan `status default` atau `qty minimum` di dalam query); repository hanya ambil/simpan data murni.
- `Model (Eloquent)`: hanya relasi, cast, scope bawaan company/branch/soft-delete, accessor/mutator murni `persistensi`. Tidak berisi metode alur bisnis (mis. tidak ada `Model->approve()` yang menulis jurnal; itu tanggung jawab Service).
- `Entity/DTO`: murni pembawa data + konstruksi/helper trivial (mis. `Money->add`). Tidak memanggil Repository/Service/Model.
- `FormRequest`: validasi input `bentuk` (required, tipe, max, format, `unique` awal). Validasi `semantik/domain` tetap di Service (mis. cek stok, cek periode closing) supaya aturan bisnis tidak tersebar.
Prinsip pendamping:
- Anti-`fat controller` & anti-`anemic service`: bila Controller >15 baris proses atau Service berisi query `Model::where` => pindahkan ke Service/Repository yang benar.
- Service dapat didekomposisi per sub-modul (`EncounterService`, `PrescriptionService`); untuk proses panjang pakai `Action`/`DomainService` di dalam folder Service modul tersebut, tetap `bukan` di Controller.
- Enforcement CI (lengkapi 11b.3): (a) Controller hanya boleh panggil `Service` (grep metode non-Service di Controller); (b) Service wajib `declare return Entity/DTO/void` bukan `Model`; (c) Repository/Model tidak boleh `throw ValidationException` domain (indikasinya logic bisnis salah tempat).


## 12. Keputusan Yang Sudah / Perlu Diputuskan

SUDAH DIPUTUSKAN:
- Modul = 5 top-level: Core, Clinical, Operational, Finance, Analytics. Tidak ada modul Master top-level.
- Core juga pakai sub-modul: 4 grouping internal (Organization, Identity, Authorization, Platform) dengan arah dependensi satu arah (Lihat 4.1).
- Master data sebagai sub-modul di dalam modul pemilik domain (bukan modul sendiri).
- Dispensing/farmasi dimiliki Clinical sebagai sub-modul Pharmacy; akses ke stok memakai Service Operational\Inventory berbasis Entity/DTO.
- Icd9Code & Icd10Code masuk Clinical sebagai sub-modul Classification, bukan Core.
- Employee masuk Operational\Master (final); modul lain membaca lewat EmployeeService berbasis EmployeeEntity. Jika ke depan Clinical butuh struktur khusus dokter yang tidak tertampung EmployeeEntity/EmployeeService, evaluasi ulang sebagai sub-modul Clinical\Hr sebagai perubahan terpisah -- bukan alasan menunda keputusan penempatan sekarang.
- Position/Jabatan masuk Operational\Master sebagai master HRD. Department/Departemen masuk Operational\Master hanya jika murni HRD; jika dipakai lintas sistem untuk User/Branch/Otorisasi, owner-nya Core\Organization. Management tidak dibuat modul terpisah; isi legacy yang terkait user/branch/otorisasi masuk Core, sedangkan isi proses operasional masuk Operational.
- Nama modul Clinical final (bukan Clinic) supaya tidak bentrok dengan konsep institusi klinik yang sudah dimiliki Core.
- Sales menggunakan POS sebagai implementasi utama; semua legacy sales/transaction dipetakan ke `Operational\Sales` pada domain POS bila konteksnya kasir/POS. POS bukan sub-module terpisah.
- Routing final menggunakan route per modul (`app/Modules/{Module}/routes/api.php`) yang di-register oleh ModuleServiceProvider; middleware global auth/permission tetap dipakai lintas file route modul.

PERLU DIBAHAS / MASIH TERBUKA:
- Tidak ada; Section 12 finalized.

## 13. Migration Checklist

> Dipindah ke `_progress.md` (entri 17.73, blok "13. Migration Checklist").

## 14. Status & Checklist

> Dipindah ke `_progress.md` (entri 17.73, blok "14. Status & Checklist").

## 15. Perlu Dikonfirmasi

> Sub-section 15.1-15.4 (owner tabel legacy, kontrak service publik,
> kompatibilitas transisi, baseline & CI) dipindah ke `_progress.md`
> (entri 17.73). Sub-section 15.5 di bawah tetap di sini karena isinya
> keputusan final.
### 15.5 Konfirmasi Berikutnya

Keputusan prioritas yang masih perlu difinalisasi sebelum eksekusi Fase 0/Fase 1:

1. **Format URL modular final**
   - Keputusan: URL final mengikuti hierarki sidebar menu, bukan nama namespace backend. Sidebar top-level final: `dashboard`, `master`, `inventory`, `accounting`, `clinic`, dan `setting`. Setiap top-level dapat memiliki submenu/list menu di dalamnya.
   - Pola API route (BE): `/api/{menu}/{submenu?}/{resource}`. Contoh: `/api/master/items`, `/api/master/employees`, `/api/inventory/stocks`, `/api/accounting/journals`, `/api/clinic/patients`, `/api/setting/users`. Didefinisikan di Laravel seeder, bukan di `AppMenu`.
   - Pola frontend path (Vue): `/{menu}/{submenu?}` tanpa prefix `/api`. Contoh: `/master/products`, `/clinic/patients`, `/dashboard`. Disimpan di `AppMenu` sebagai kolom `route`.
   - Vue routing bersifat static, didefinisikan di bagian list routing Vue Router secara terpisah; tidak perlu sinkron real-time dengan seeder BE.
   - Breadcrumb aktif dan susunan route mengikuti pilihan menu: menu aktif menentukan segmen URL, sedangkan nama modul backend (`Core`, `Clinical`, `Operational`, `Finance`, `Analytics`) tetap hanya dipakai untuk namespace/struktur internal.
   - Pemisahan layer yang sudah diputuskan:
     - `AppMenu` (DB/seeder) ? menyimpan alias + frontend path (Vue). Contoh: `master.products` ? `/master/products`.
     - Laravel route/seeder ? mendefinisikan API path BE. Contoh: `/api/master/products`.
     - Vue Router (static) ? mendaftarkan komponen per path frontend.
   - Hasilkan tabel mapping URL existing -> URL final per modul, dengan kolom terpisah untuk frontend path dan API path.
   - Catatan menu Master/Product: satu konsep `Product` dapat muncul di beberapa menu produk sesuai jenisnya, misalnya obat/farmasi, BHP, dan alkes. Backend tetap memakai satu master Product/Item dengan atribut/klasifikasi/type, sedangkan sidebar dan breadcrumb boleh menampilkan menu terpisah per jenis/kategori product.

2. **Daftar permission final per resource**
   - Pola sudah diputuskan: `create {resource}`, `read {resource}`, `update {resource}`, `delete {resource}`.
   - Keputusan: semua resource memakai bentuk plural dan pemisah spasi, contoh: `create patients`, `read patients`, `update patients`, `delete patients`.
   - Untuk aksi non-CRUD gunakan pola `action {resource}`, contoh: `approve journals`, `post journals`, `export products`, `import products`, `print invoices`.
   - **Permission list didefinisikan di Laravel seeder** -- bukan hardcode di middleware, controller, atau route. Seeder adalah satu-satunya source of truth; middleware, controller, route, dan test wajib merujuk nama yang sama dari seeder.
   - Hasilkan daftar resource lengkap per modul dan mapping permission lama -> permission final.
   - Deliverable tabel minimal memuat: module backend, sidebar/submenu, resource plural, CRUD permission, action tambahan, permission lama, permission final, dan status (`kept`, `alias`, `renamed`, `deprecated`).
   - Mapping wajib menyimpan permission lama sampai role/user dan seeder diperbarui; HTTP method dipetakan ke permission final, contoh `GET -> read`, `POST -> create`, `PUT/PATCH -> update`, `DELETE -> delete`.
   - Audit `rg` route/controller/seed sebelum Fase 1 untuk memastikan tidak ada permission yang masih hardcode di luar seeder.

3. **Mapping `AppMenu` alias ke modul final**
   - Status: SETUJU / diputuskan.
   - `AppMenu` menyimpan **frontend path (Vue)** saja ? bukan API path. Kolom `route` berisi path seperti `/dashboard`, `/master/products`, bukan `/api/...`.
   - API route BE didefinisikan di **Laravel seeder/route** secara terpisah dan mengikuti pola `/api/{menu}/{submenu?}/{resource}`.
   - Hasilkan tabel konkret: alias existing -> modul/submodule final -> permission -> urutan menu saat ini.
   - Alias dan urutan tetap dipakai selama migrasi backend.
   - Format tabel mapping memakai kolom: namespace backend final, sidebar top-level, submenu, `AppMenu` alias existing, frontend path (Vue), API path (BE), permission, urutan menu saat ini, dan status.
   - Status menu: `kept` (tetap), `renamed` (label/alias berubah di fase final), `merged` (beberapa menu lama digabung), atau `hidden` (disembunyikan tetapi data/kode tetap ada).
   - Aturan alias: satu alias hanya boleh dipakai satu menu/submenu, tidak boleh duplikat, dan mengikuti menu/sidebar bukan namespace backend.
   - Contoh format (kolom: namespace | top-level | submenu | alias | frontend path | API path | permission | urutan | status):
     `Analytics | dashboard | - | dashboard | /dashboard | /api/dashboard | read dashboards | 1 | kept`
     `Operational\Master | master | Products | master.products | /master/products | /api/master/products | read products | 2 | kept`
     `Clinical\Registration | clinic | Patients | clinic.patients | /clinic/patients | /api/clinic/patients | read patients | 5 | kept`

4. **Detail Quotation** (dipisah jadi dua entitas)

   **4a. Sales Quotation**
   - Owner final: `Operational\Sales`.
   - Fungsi: penawaran harga ke customer, bersifat opsional -- flow bisa lewat Quotation atau langsung SO.
   - Flow opsional: `SalesQuotation ? SO ? DO ? Sales Invoice` atau langsung `SO ? DO ? Sales Invoice`.
   - Permission final: `create sales-quotations`, `read sales-quotations`, `update sales-quotations`, `delete sales-quotations`.
     - Keputusan: **dibuat**, flow opsional -- SalesQuotation ? SO ? DO ? Sales Invoice atau langsung SO. Migrasi tabel dibuat nanti.

   **4b. Purchase Quotation (RFQ)**
   - Owner final: `Operational\Procurement`.
   - Fungsi: penawaran harga dari supplier, bersifat opsional -- flow bisa lewat RFQ/PR atau langsung PO.
   - Flow opsional: `RFQ ? PR ? PO ? Purchase Invoice`, atau `PR ? PO`, atau langsung `PO`.
   - Permission final: `create purchase-quotations`, `read purchase-quotations`, `update purchase-quotations`, `delete purchase-quotations`.
     - Keputusan: **dibuat**, flow opsional -- RFQ ? PR ? PO ? Purchase Invoice, atau PR ? PO, atau langsung PO. Migrasi tabel dibuat nanti.

5. **Detail Product Purchase** ? DIPUTUSKAN
   - Owner final: `Operational\Procurement`.
   - Relasi supplier: 1 PR ? 1 supplier; 1 PO ? bisa konsolidasi dari banyak PR, tetap 1 supplier (karena setiap PR sudah terikat ke 1 supplier).
   - Sumber item di PO detail: bisa dari PR atau direct/manual (inventory tambahan). Pola yang dipakai adalah **polymorphic** -- kolom `source_type` + `source_uuid` di `po_details`, sehingga ke depan sumber bisa bertambah (misal Material Request, dll) tanpa perubahan skema.
   - Efek stok: stok bertambah saat **GR (penerimaan barang)**, bukan saat PO di-approve.
   - Efek jurnal: menggunakan **3-way matching** (standar industri):
     - PO approved ? tidak ada jurnal (hanya commitment/encumbrance).
     - GR (terima barang) ? `Dr. Inventory / Cr. GR-IR Clearing`.
     - Purchase Invoice ? `Dr. GR-IR Clearing / Cr. Accounts Payable`.
     - Payment ? `Dr. Accounts Payable / Cr. Cash/Bank`.
   - GR-IR Clearing adalah akun perantara antara penerimaan barang dan invoice supplier.
   - Relasi warehouse: GR mendukung **multi-warehouse** -- staff bisa memilih warehouse tujuan saat penerimaan barang.

6. **Pemisahan domain Sales** ? DIPUTUSKAN
   - Struktur domain dalam `Operational\Sales`: `Transaction` (mencakup POS), `SO`, `DO`, `Invoice`, dan `Aging`.
   - `POS` adalah bagian dari domain `Transaction`, bukan domain terpisah.
   - Domain ini hanya organisasi folder/file internal; struktur namespace tetap hanya `Module ? SubModule`.

7. **Audit schema konkret** ? CONFIRMED
   - Audit sudah dijalankan. Temuan di bawah sudah distandarisasi sebelum Fase 1 dimulai.

   **Standar yang sudah konsisten (tidak perlu diubah):**
   - deleted_at: pakai bigInteger unsigned default 0 (bukan Laravel softDeletes) -- intentional untuk Go parity. ?
   - Audit columns: created_by, updated_by, deleted_by sudah ada di hampir semua tabel. ?
   - company_uuid + branch_uuid: sudah ada di semua tabel domain utama. ?

   **Standar yang sudah distandarisasi:**
   - UUID PK: semua tabel wajib pakai $table->uuid('uuid')->primary(). ? CONFIRMED
   - warehouses.address_uuid ? sudah char(36). ? FIXED
   - warehouses.jenis_uuid ? dihapus, digantikan kolom type string. ? FIXED
   - Tabel warehouses ? **diganti menjadi locations** dengan kolom type (warehouse, store, counter, custom). Semua FK warehouse_uuid diganti ke location_uuid. Seeder locations perlu dibuat. ? CONFIRMED
   - Semua model wajib pakai audit Laravel (Spatie Laravel Auditing atau paket equivalent) untuk mencatat perubahan data secara otomatis. ? CONFIRMED

   **Tabel yang perlu ditambah (belum ada di migration):**
     - purchase_requisitions + purchase_requisition_items -- migration dibuat saat migrasi Operational\Procurement. ? DIPUTUSKAN
     - goods_receipts + goods_receipt_items -- migration dibuat saat migrasi Operational\Procurement. ? DIPUTUSKAN

   **Schema yang perlu diubah sesuai keputusan modularisasi:**
     - purchase_order_items: tambah kolom source_type + source_uuid (polymorphic) -- dilakukan saat migrasi Operational\Procurement. ? DIPUTUSKAN
     - purchase_orders: warehouse_uuid dipindah ke level GR/detail -- dilakukan saat migrasi Operational\Procurement. ? DIPUTUSKAN
   - purchase_returns.location_uuid: sudah ada location_uuid, konsisten dengan rename warehouses ? locations. ? OK

   **FK implisit lintas modul yang perlu dicatat sebelum migration dipindah:**
   -
egistrations.doctor_uuid ? employees (Clinical ? Operational\Master)
   - purchase_order_items.product_uuid ? items (Procurement ? Operational\Master)
   - stock_transfer_items.product_uuid ? items (Inventory ? Operational\Master)
   - stock_adjustment_items.item_uuid ? items (Inventory ? Operational\Master)
   - item_stok.item_uuid + location_uuid ? items + locations (Inventory ? Operational\Master)
   - Semua FK implisit lintas modul ini wajib diaudit ulang dengan
g sebelum migration dipindah ke folder modul -- dilakukan per modul saat migrasi. ? DIPUTUSKAN


8. **Signature Service publik yang exact**
   - Aturan layer yang sudah diputuskan: **Service tidak boleh import Model secara langsung** -- import Model hanya boleh ada di Repository. Pattern wajib: `Controller ? Service ? Repository ? Model`.
   - Lintas modul wajib lewat Service publik berbasis Entity/DTO; tidak boleh import Model atau Repository modul lain.
   - Deliverable: signature Service publik lintas modul ditulis per modul saat migrasi. ? DIPUTUSKAN
   - Service publik yang perlu di-detail sebelum modul dipindah:
     - `Operational\Inventory`: cek ketersediaan stok, reduce, return, transfer.
     - `Operational\Master`: cari item, validasi UOM, harga, supplier, employee, lookup lintas modul.
     - `Finance`: account lookup, journal posting.
     - `Core`: company, branch, user.



7. **Audit schema konkret** ? SELESAI -- company_uuid & branch_uuid sudah ditambah ke audits table, custom resolver sudah dibuat.
   - Audit sudah dijalankan. Temuan di bawah perlu diselesaikan sebelum atau selama Fase 1.

   **Standar yang sudah konsisten (tidak perlu diubah):**
   - UUID PK: mayoritas tabel sudah pakai uuid()->primary() atau char('uuid',36)->primary(). ?
   - `deleted_at`: pakai `bigInteger` unsigned default 0 (bukan Laravel softDeletes) ? intentional untuk Go parity. ?
   - Audit columns: created_by, updated_by, deleted_by sudah ada di hampir semua tabel. ?
   - `company_uuid` + `branch_uuid`: sudah ada di semua tabel domain utama. ?

   **Inkonsistensi yang perlu distandarisasi:**
   - Sebagian tabel pakai char('uuid', 36), sebagian pakai uuid('uuid') ? perlu distandarisasi ke satu pola (pilih salah satu sebelum Fase 1).
   - ~~warehouses.address_uuid bertipe unsignedBbigInteger~~ -> sudah difix ke `char(36)`. ?
   - ~~warehouses.jenis_uuid bertipe integer~~ -> dihapus, digantikan kolom `type` string. ?
   - Tabel `warehouses` -> **diganti menjadi `locations`** dengan kolom `type` (`warehouse`, `store`, `counter`, `custom`). Semua FK `warehouse_uuid` diganti ke `location_uuid`. File migration di-rename ke `2026_04_04_002000_create_locations_table.php`. Seeder `locations` perlu dibuat untuk data awal.
     - Status eksekusi (2026-09-19): Model `Warehouse` sudah dihapus, semua referensi class `Warehouse::class` di 26 file (modules + tests) sudah diupdate ke `Location::class`. Import `Location` ditambahkan ke 11 model lintas namespace. Update lanjutan (2026-09-19): Semua referensi kolom FK `warehouse_uuid` di kode (modules + tests) sudah di-rename ke `location_uuid`. Kategori 40 failure SQLSTATE akibat FK lama sudah diselesaikan.
   **Tabel yang perlu ditambah (belum ada di migration):**
   - purchase_requisitions + purchase_requisition_items ? PR belum ada, padahal flow PR ? PO sudah diputuskan.
   - goods_receipts + goods_receipt_items ? GR (penerimaan barang) belum ada; diperlukan untuk efek stok dan jurnal 3-way matching.

   **Schema yang perlu diubah sesuai keputusan modularisasi:**
   - purchase_order_items: tambah kolom source_type + source_uuid (polymorphic) untuk mendukung sumber item dari PR atau direct/manual.
   - purchase_orders: saat ini punya satu warehouse_uuid di header ? perlu dipindah ke level GR/detail agar mendukung multi-warehouse per keputusan item 5.
   - purchase_returns.location_uuid: klarifikasi apakah ini warehouse atau lokasi lain; normalkan ke warehouse_uuid jika memang gudang.

   **FK implisit lintas modul yang perlu dicatat sebelum migration dipindah:**
   - `registrations.doctor_uuid` ? `employees` (Clinical ? Operational\Master)
   - purchase_order_items.product_uuid ? items/products (Procurement ? Operational\Master)
   - stock_transfer_items.product_uuid ? items/products (Inventory ? Operational\Master)
   - stock_adjustment_items.item_uuid ? items/products (Inventory ? Operational\Master)
   - item_stok.item_uuid + location_uuid ? items + locations (Inventory ? Operational\Master)
   - Semua FK ini wajib diaudit ulang dengan `rg` sebelum migration dipindah ke folder modul.

8. **Signature Service publik yang exact**
   - Aturan layer yang sudah diputuskan: **Service tidak boleh import Model secara langsung** ? import Model hanya boleh ada di Repository. Pattern wajib: `Controller ? Service ? Repository ? Model`.
   - Lintas modul wajib lewat Service publik berbasis Entity/DTO; tidak boleh import Model atau Repository modul lain.
   - Deliverable: signature Service publik lintas modul ditulis per modul saat migrasi. ? DIPUTUSKAN
   - Service publik yang perlu di-detail sebelum modul dipindah:
     - `Operational\Inventory`: cek ketersediaan stok, reduce, return, transfer.
     - `Operational\Master`: cari item, validasi UOM, harga, supplier, employee, lookup lintas modul.
     - `Finance`: account lookup, journal posting.
     - `Core`: company, branch, user.

## 16. Multi-Organization & Multi-Branch Support

### 16.1 Tujuan
Mendukung:
- **1 User bisa akses MULTIPLE Companies** (misalnya: user consultant bisa akses data beberapa klinik/rumah sakit client)
- **1 Company punya MULTIPLE Branches** (struktur standar: perusahaan ? cabang faskes)
- **Data MUST always scoped by company_uuid + branch_uuid** (tidak ada data tanpa scope)

### 16.2 Kondisi Saat Ini (Before Change) ? AUDITED

| Aspek | Status | Detail |
|---|---|---|
| User ? Company | ? Single | Kolom company_uuid langsung di tabel users -- 1 user = 1 company |
| User ? Branch | ? Single | Kolom branch_uuid langsung di tabel users -- 1 user = 1 branch |
| User ? Companies (pivot) | ? Tidak ada | Tidak ada tabel user_companies |
| User ? Branches (pivot) | ? Tidak ada | Tidak ada tabel user_branches |
| BranchScope | ? Ada | App\Scopes\BranchScope sudah filter data berdasarkan branch_uuid user |
| CompanyScope | ? Done | App\Scopes\CompanyScope sudah diimplementasi |
| company_uuid di tabel | ? Ada | Hampir semua tabel domain utama sudah punya kolom ini |
| branch_uuid di tabel | ? Ada | Hampir semua tabel domain utama sudah punya kolom ini |
| Audit trail per company/branch | ? Kurang | OwenIt Auditing belum capture company_uuid/branch_uuid di record audit |

### 16.3 Keputusan Desain ? DIPUTUSKAN

#### 16.3.1 Struktur Relasi User-Company-Branch

`
User
  +-- belongsToMany Company (via user_companies pivot)
  +-- belongsToMany Branch  (via user_branches pivot, scoped by company)

Branch
  +-- belongsTo Company

UserCompany (pivot)
  +-- user_uuid (FK)
  +-- company_uuid (FK)
  +-- is_default BOOLEAN -- company aktif saat login

UserBranch (pivot)
  +-- user_uuid (FK)
  +-- company_uuid (FK)
  +-- branch_uuid (FK)
  +-- is_default BOOLEAN -- branch aktif saat login
`

**Catatan:** user_branches.company_uuid ada di pivot karena 1 user bisa punya branch di company A dan company B yang berbeda.

#### 16.3.2 Tabel Pivot ? DONE

**Migration: user_companies**
`php
});
`

**Migration: user_branches**
`php
});
`

#### 16.3.3 Kolom company_uuid & branch_uuid di Tabel users (DEPRECATE)

- Kolom company_uuid dan branch_uuid di tabel users **tetap dipertahankan** untuk backward compatibility
- Isi kolom tersebut dari pivot user_companies dan user_branches saat is_default = true
- **Tidak ada kolom nullable** untuk company_uuid dan branch_uuid di tabel domain -- semua data transaksi HARUS punya kedua kolom ini
- Tabel yang memang hanya master tunggal tanpa company scope (misalnya tabel companies sendiri, tabel permissions) exempt dari aturan ini

#### 16.3.4 Scope Implementation

**CompanyScope** (diimplementasi):

**BranchScope** (update):
- Cek apakah user punya branch aktif di session
- Fallback ke branch_uuid dari pivot is_default
- Jika user punya multi-branch access, harus pilih salah satu

#### 16.3.5 Session/Context Resolution

- User login ? load user_companies + user_branches
- Jika is_default tidak ada, ambil yang pertama
- User bisa switch company/branch via header (X-Klinik-Company-Uuid, X-Klinik-Branch-Uuid) atau UI
- Context resolver (App\Support\CompanyScopeResolver) sudah ada, perlu update untuk multi-org

#### 16.3.6 Deprecate kolom lama di User model -- DIPUTUSKAN (2026-09-22)

Keputusan final: soft deprecate, kolom di-drop di akhir, resolusi konteks via middleware.
- Kolom `company_uuid`/`branch_uuid` di tabel `users` dipertahankan selama transisi tetapi berhenti menjadi source of truth. Source of truth konteks aktif: header/session (divalidasi membership) -> pivot `user_companies`/`user_branches` (`is_default`).
- Kolom legacy TIDAK dijadikan cache denormalized permanen: konteks aktif bersifat per-request (bisa diganti via header/session), tidak bisa diwakili satu kolom statis. Kolom di-drop di milestone akhir setelah 0 pemakaian langsung tersisa (drop kolom + FK + index `idx_users_company_deleted`/`idx_users_branch_deleted`).
- Resolusi konteks pindah ke middleware `ResolveCompanyContext` (global, setelah auth): resolve + validasi header terhadap pivot membership (`userHasAccessToCompany`) SEKALI per request, simpan di `$request->attributes` + helper `current_company_uuid()`/`current_branch_uuid()`. Superadmin exempt sesuai Section 20.12.
- Accessor `active_company_uuid`/`active_branch_uuid` menjadi wrapper tipis ke context yang sama selama migrasi, lalu deprecated.

Temuan audit (2026-09-22):
- 230 titik akses langsung `$user->company_uuid`/`$user->branch_uuid` di non-test PHP (terkonsentrasi: EncounterController 22, RegistrationController 18, Sales/Service 14, Finance Journal 12).
- Celah keamanan: header `X-Klinik-Company-Uuid` hanya divalidasi `companyExists()`, tidak divalidasi membership pivot; `userHasAccessToCompany()` tidak pernah dipanggil di jalur resolusi -> risiko tenant isolation bypass via header spoofing.
- Performa: accessor menjalankan 2 query pivot per pemanggilan; EncounterController memanggil konteks 22x/request (~44 query redundan). Middleware memangkasnya menjadi 1-2 query per request.

Langkah eksekusi:
1. Middleware `ResolveCompanyContext` + helper (menambal celah keamanan).
2. Rewire `CompanyScope`, `BranchScope`, `CompanyScopeResolver` membaca dari context; level legacy resolver ditandai deprecated (tetap dipakai untuk non-HTTP: artisan/queue).
3. Observer sync pivot -> kolom legacy selama transisi (cegah drift); dihapus bersamaan dengan drop.
4. Migrasi titik akses per modul (1 PR per modul): ganti akses langsung ke helper. Encounter (22 titik) selesai 2026-09-22; scan global tersisa 238 kemunculan pada 34 file.
5. Seeder/Import/UserEntity: tulis pivot dulu, legacy lewat sync.
6. Milestone akhir: hapus fallback accessor + level legacy resolver + migration drop kolom/FK/index.

---

## 17. Audit Schema & Multi-Org Audit Trail

### 17.1 Kondisi Audit Saat Ini ? AUDITED

| Aspek | Status | Detail |
|---|---|---|
| Package | ? OwenIt Auditing | owen-it/laravel-auditing: ^14.0 sudah terinstall |
| Custom Audit model | ? Ada | App\Models\Audit override untuk UUID support |
| Audit config | ? Ada | config/auditing.php sudah terkonfigurasi |
| Model yang diaudit | ? 112 model | Semua model utama sudah pakai trait Auditable |
| Audit UUID PK | ? Ada | App\Models\Audit sudah pakai UUID |
| Capture user_id | ? Ada | UserResolver dari OwenIt |
| Capture company_uuid di audit | ? Kurang | OwenIt tidak auto-capture company_uuid |
branch_uuid |

### 17.2 Keputusan Audit untuk Multi-Org ? DIPUTUSKAN

#### 17.2.1 audits Table -- Tambah Kolom Scope
**Migration update untuk `audits` table:**
**Migration update untuk udits table:**

`php
});
`

**Razionale:** Setiap record audit HARUS bisa di-filter berdasarkan company & branch. Ini penting untuk:
- Compliance & regulatory audit (klinik harus bisa generate audit trail per faskes)
- Multi-tenant isolation (jika satu database untuk multiple company)
- Debugging & forensic analysis

#### 17.2.2 Custom Audit Implementation untuk Capture Context

**Option A: Custom UserResolver (Recommended)**
`php
#### 17.2.3 Audit Threshold & Retention

- **Threshold:** Default 0 (simpan semua perubahan)
- **Queue:** AUDITING_QUEUE env flag -- jika true, audit write ke queue async
- **Retention:** Tabel audit tidak di-soft delete (audit record bersifat permanen untuk compliance)

---

## 18. Todo List untuk Multi-Org + Audit Setup (Fase 0 Deliverables)

> Dipindah ke `_progress.md` (entri 17.73, blok "18. Todo List untuk Multi-Org + Audit Setup").

## 19. Freeze URL Endpoint -- Mapping Lama vs Final ? DIPUTUSKAN

### 19.1 Pola URL Final

**API Backend (BE)**
- Pattern: `/api/v1/{top-level}/{submenu?}/{resource}`
- Top-level sidebar: `dashboard`, `master`, `inventory`, `accounting`, `clinic`, `setting`

**Frontend (Vue)**
- Pattern: `/{top-level}/{submenu?}`
- Disimpan di tabel `app_menus` kolom `route`

### 19.2 Tabel Mapping URL

| # | Modul Final | Top-Level | Submenu | Resource | URL Lama (API) | URL Final (API) | Status |
|---|---|---|---|---|---|---|---|
| **Core -- Identity & Auth** |||||||
| 1 | Core\Identity | setting | users | users | `/api/v1/settings/users` | `/api/v1/setting/users` | renamed |
| 2 | Core\Identity | setting | roles | roles | `/api/v1/settings/roles` | `/api/v1/setting/roles` | renamed |
| 3 | Core\Identity | setting | backup | backups | `/api/v1/settings/backups` | `/api/v1/setting/backups` | kept |
| **Core -- Organization** |||||||
| 4 | Core\Organization | master | companies | companies | -- | `/api/v1/master/companies` | new |
| 5 | Core\Organization | master | branches | branches | -- | `/api/v1/master/branches` | new |
| **Core -- Authorization** |||||||
| 6 | Core\Authorization | setting | app-menu | app-menus | -- | `/api/v1/setting/app-menus` | new |
| 7 | Core\Authorization | setting | permissions | permissions | -- | `/api/v1/setting/permissions` | new |
| **Core -- Platform** |||||||
| 8 | Operational\Master | master | lookups | lookup-sets | `/api/v1/masters/lookups` | `/api/v1/master/lookups` | renamed |
| 9 | Core\Platform | master | payment-terms | payment-terms | `/api/v1/masters/payment-terms` | `/api/v1/master/payment-terms` | renamed |
| 10 | Core\Platform | master | price-levels | price-levels | `/api/v1/masters/price-levels` | `/api/v1/master/price-levels` | renamed |
| 11 | Core\Platform | master | taxes | taxes | `/api/v1/masters/taxes` | `/api/v1/master/taxes` | renamed |
| 12 | Core\Platform | master | units | units | `/api/v1/masters/units` | `/api/v1/master/units` | renamed |
| **Clinical -- Registration** |||||||
| 13 | Clinical\Registration | clinic | patients | patients | `/api/v1/patients` | `/api/v1/clinic/patients` | renamed |
| 14 | Clinical\Registration | clinic | registrations | registrations | `/api/v1/registrations` | `/api/v1/clinic/registrations` | renamed |
| **Clinical -- Medical Record** |||||||
| 15 | Clinical\MedicalRecord | clinic | encounters | encounters | `/api/v1/encounters` | `/api/v1/clinic/encounters` | renamed |
| 16 | Clinical\MedicalRecord | clinic | complaints | complaints | `/api/v1/operational/complaints` | `/api/v1/clinic/complaints` | renamed |
| **Clinical -- Pharmacy** |||||||
| 17 | Clinical\Pharmacy | inventory | dispensing | dispensing | -- | `/api/v1/inventory/dispensing` | new |
| **Clinical -- Ancillary** |||||||
| 18 | Clinical\Ancillary | clinic | lab-orders | lab-orders | -- | `/api/v1/clinic/lab-orders` | new |
| 19 | Clinical\Ancillary | clinic | radiology-orders | radiology-orders | -- | `/api/v1/clinic/radiology-orders` | new |
| **Clinical -- Classification** |||||||
| 20 | Clinical\Classification | master | icd-codes | icd-codes | -- | `/api/v1/master/icd-codes` | new |
| **Clinical -- Integration** |||||||
| 21 | Clinical\Integration | setting | satusehat-settings | satusehat-settings | -- | `/api/v1/setting/satusehat-settings` | new |
| 22 | Clinical\Integration | setting | bpjs-settings | bpjs-settings | -- | `/api/v1/setting/bpjs-settings` | new |
| **Operational -- Master Data** |||||||
| 23 | Operational\Master | master | employees | employees | `/api/v1/employees` | `/api/v1/master/employees` | renamed |
| 24 | Operational\Master | master | suppliers | suppliers | `/api/v1/suppliers` | `/api/v1/master/suppliers` | renamed |
| 25 | Operational\Master | master | products | products | `/api/v1/products` | `/api/v1/master/products` | renamed |
| 26 | Operational\Master | master | services | services | `/api/v1/services` | `/api/v1/master/services` | renamed |
| 27 | Operational\Master | master | service-packages | service-packages | `/api/v1/service-packages` | `/api/v1/master/service-packages` | renamed |
| 28 | Operational\Master | master | item-conversions | item-conversions | `/api/v1/masters/item-conversions` | `/api/v1/master/item-conversions` | renamed |
| **Operational -- Sales** |||||||
| 29 | Operational\Sales | master | sales | sales | `/api/v1/sales` | `/api/v1/master/sales` | renamed |
| 30 | Operational\Sales | inventory | sales-quotations | sales-quotations | -- | `/api/v1/inventory/sales-quotations` | new |
| **Operational -- Procurement** |||||||
| 31 | Operational\Procurement | inventory | purchases | purchases | `/api/v1/purchases` | `/api/v1/inventory/purchases` | renamed |
| 32 | Operational\Procurement | inventory | purchase-orders | purchase-orders | `/api/v1/operational/purchase-orders` | `/api/v1/inventory/purchase-orders` | renamed |
| 33 | Operational\Procurement | inventory | purchase-quotations | purchase-quotations | -- | `/api/v1/inventory/purchase-quotations` | new |
| 34 | Operational\Procurement | inventory | purchase-requisitions | purchase-requisitions | -- | `/api/v1/inventory/purchase-requisitions` | new |
| **Operational -- Inventory** |||||||
| 35 | Operational\Inventory | inventory | stocks | stocks | -- | `/api/v1/inventory/stocks` | new |
| 36 | Operational\Inventory | inventory | stock-transfers | stock-transfers | -- | `/api/v1/inventory/stock-transfers` | new |
| 37 | Operational\Inventory | inventory | stock-adjustments | stock-adjustments | -- | `/api/v1/inventory/stock-adjustments` | new |
| 38 | Operational\Inventory | inventory | locations | locations | -- | `/api/v1/inventory/locations` | new |
| **Operational -- Manufacturing** |||||||
| 39 | Operational\Manufacturing | inventory | boms | boms | `/api/v1/operational/boms` | `/api/v1/inventory/boms` | renamed |
| 40 | Operational\Manufacturing | inventory | assemblies | assemblies | `/api/v1/operational/assemblies` | `/api/v1/inventory/assemblies` | renamed |
| **Finance -- Accounting** |||||||
| 41 | Finance\Journal | accounting | accounts | accounts | `/api/v1/accounts` | `/api/v1/accounting/accounts` | renamed |
| 42 | Finance\Journal | accounting | journals | journals | `/api/v1/journals` | `/api/v1/accounting/journals` | renamed |
| 43 | Finance\Ledger | accounting | general-ledger | general-ledger | `/api/v1/general-ledger` | `/api/v1/accounting/general-ledger` | renamed |
| **Analytics -- Reports** |||||||
| 44 | Analytics\Reports | accounting | cash-flow | cash-flow | `/api/v1/accounting/cash-flow` | `/api/v1/accounting/cash-flow` | kept |
| 45 | Analytics\Reports | accounting | income-statement | income-statement | `/api/v1/reports/income-statement` | `/api/v1/accounting/income-statement` | renamed |
| 46 | Analytics\Reports | accounting | balance-sheet | balance-sheet | `/api/v1/reports/balance-sheet` | `/api/v1/accounting/balance-sheet` | renamed |
| 47 | Analytics\Reports | accounting | trial-balance | trial-balance | `/api/v1/reports/trial-balance` | `/api/v1/accounting/trial-balance` | renamed |
| **Finance -- Closing** |||||||
| 48 | Finance\Closing | accounting | closing-periods | closing-periods | -- | `/api/v1/accounting/closing-periods` | new |
| **Analytics** |||||||
| 49 | Analytics\Dashboard | dashboard | -- | dashboard | -- | `/api/v1/dashboard` | new |
| 50 | Analytics\Reports | accounting | financial-reports | financial-reports | -- | `/api/v1/accounting/financial-reports` | new |

### 19.3 Status Definitions

| Status | Arti |
|---|---|
| `kept` | URL lama = URL final, tidak berubah |
| `renamed` | URL berubah, perlu redirect alias dari lama ke final |
| `new` | Endpoint baru, belum ada di codebase lama |
| `deprecated` | Endpoint lama yang akan dihapus, ada replacement |

### 19.4 Redirect Strategy

Untuk endpoint `renamed`, implementasi redirect di Laravel routes:


### 19.5 Route File Structure (Target)


### 19.6 Migration Notes

- File `routes/api.php` lama dipindahkan ke `routes/legacy/api.php` setelah refactor selesai
- Route definitions baru mengikuti folder structure modular
- Pattern file route baru: `routes/api/v1/{top-level}/{resource}.php`
- Setiap perubahan URL wajib update tabel ini lebih dulu

---

## 20. Rencana Tahap Pengerjaan (Implementation Roadmap)

Status: **SIAP EKSEKUSI** -- semua keputusan sudah terkonfirmasi di Section 1-16.

### 20.1 Kondisi Awal (Sebelum Modularisasi)

Struktur layer-first saat ini:

### 20.2 Target Akhir


Setiap modul punya struktur internal:

---

### 20.3 Fase 0 -- Persiapan & Shared Layer

**Tujuan:** Setup struktur folder Modules/, pindahkan Shared layer, tidak ada business logic yang dipindah.

| # | Step | File/Folder Target | Catatan |
|---|------|--------------------|---------|
| 0.1 | Buat folder `app/Modules/` | `app/Modules/` | Folder kosong |
| 0.2 | Buat folder `app/Shared/` | `app/Shared/` | Folder kosong |
| 0.3 | Pindah `app/Traits/` ? `app/Shared/Traits/` | `Auditable, Blameable, HasBranchScope, HasCompanyScope, HasMultiOrgScope, HasSoftDeleteUnique` | Update namespace |
| 0.4 | Pindah `app/Scopes/` ? `app/Shared/Scopes/` | `CompanyScope, BranchScope` | Update namespace |
| 0.5 | Pindah `app/Support/` ? `app/Shared/Support/` | `CompanyScopeResolver` | Update namespace |
| 0.6 | Update `composer.json` autoload untuk `App\Shared\` | `composer.json` | Tambah PSR-4 mapping |
| 0.7 | Update semua `use App\Traits\...` ? `use App\Shared\Traits\...` | 112 model | Bisa pakai `rg` + sed |
| 0.8 | Update semua `use App\Scopes\...` ? `use App\Shared\Scopes\...` | Semua model yang pakai scope | Bisa pakai `rg` + sed |
| 0.9 | Jalankan `php artisan test` -- pastikan hijau | - | Gate sebelum lanjut |

---

### 20.4 Fase 1 -- Core Module

**Tujuan:** Pindah domain Company, Branch, User, Auth, RBAC, Settings ke `App\Modules\Core`.
**Dependency:** Fase 0 selesai.

#### Fase 1a -- Core\Organization

| # | Step | File Target | Catatan |
|---|------|-------------|---------|
| 1a.1 | Buat folder + ServiceProvider `App\Modules\Core` | `app/Modules/Core/CoreServiceProvider.php` | Register semua sub-modul |
| 1a.2 | Pindah Model `Company` | `app/Modules/Core/Organization/Models/Company.php` | Update namespace |
| 1a.3 | Pindah Model `Branch` | `app/Modules/Core/Organization/Models/Branch.php` | Update namespace |
| 1a.4 | Pindah Entity/DTO Company & Branch | `app/Modules/Core/Organization/Entities/` | Update namespace |
| 1a.5 | Pindah Repository Company & Branch | `app/Modules/Core/Organization/Repositories/` | Update namespace |
| 1a.6 | Pindah Service Company & Branch | `app/Modules/Core/Organization/Services/` | Update namespace |
| 1a.7 | Pindah Controller Company & Branch | `app/Modules/Core/Organization/Http/Controllers/` | Update namespace |
| 1a.8 | Pindah Migration Company & Branch | `app/Modules/Core/database/migrations/` | Update loadMigrationsFrom |
| 1a.9 | Jalankan test baseline Core\Organization | - | Gate sebelum lanjut |

#### Fase 1b -- Core\Identity (User, Auth)

| # | Step | File Target | Catatan |
|---|------|-------------|---------|
| 1b.1 | Pindah Model `User, UserCompany, UserBranch` | `app/Modules/Core/Identity/Models/` | Update namespace |
| 1b.2 | Pindah Entity/DTO User & Auth | `app/Modules/Core/Identity/Entities/` | - |
| 1b.3 | Pindah Repository & Service Auth | `app/Modules/Core/Identity/` | - |
| 1b.4 | Pindah Controller Auth | `app/Modules/Core/Identity/Http/Controllers/` | - |
| 1b.5 | Update JWT config, guards, auth.php | `config/auth.php` | Point ke namespace baru |
| 1b.6 | Jalankan test baseline Core\Identity | - | Gate |

#### Fase 1c -- Core\Authorization (Role, Permission, AppMenu)

| # | Step | File Target | Catatan |
|---|------|-------------|---------|
| 1c.1 | Pindah Model `Role, Permission, AppMenu` | `app/Modules/Core/Authorization/Models/` | - |
| 1c.2 | Pindah Repository & Service RBAC | `app/Modules/Core/Authorization/` | - |
| 1c.3 | Update Spatie Permission config | `config/permission.php` | Point ke model baru |
| 1c.4 | Jalankan test baseline Core\Authorization | - | Gate |

#### Fase 1d -- Core\Platform (Settings, Backup)

> Catatan: domain Lookup (LookupSet, LookupValue, LookupValueDetail, LookupType, TempLookup) dipindahkan kepemilikannya dari Core\Platform ke Operational\Master. Lihat Fase 4 (Operational\Master) dan Section 19.2 entry 8.

| # | Step | File Target | Catatan |
|---|------|-------------|---------|
| 1d.1 | Pindah Settings, Backup | `app/Modules/Core/Platform/Models/` | - |
| 1d.2 | Pindah Repository & Service Platform | `app/Modules/Core/Platform/` | - |
| 1d.3 | Jalankan test baseline Core\Platform | - | Gate |

#### Fase 1 -- Final

| # | Step | Catatan |
|---|------|---------|
| 1e.1 | Update `AppServiceProvider` -- hapus binding lama, register `CoreServiceProvider` | - |
| 1e.2 | Update `routes/api.php` -- URL Core ke pola `/api/v1/setting/*` dan `/api/v1/master/companies` | Sesuai Section 19 |
| 1e.3 | Hapus namespace lama: `App\Services\Infrastructure`, `App\Repositories\Infrastructure`, `App\Services\Settings`, dll | - |
| 1e.4 | Jalankan full test suite | Gate sebelum Fase 2 |

---

### 20.5 Fase 2 -- Finance Module

**Tujuan:** Pindah Accounting (Journal, Ledger, Closing) ke `App\Modules\Finance`. Endpoint dan presentasi laporan keuangan tidak termasuk di sini -- masuk `Analytics\Reports` pada Fase 6; service perhitungan FinancialReport*/CashFlow* tetap jadi service publik Finance.
**Dependency:** Fase 1 (Core\Organization untuk company/branch scope).

| # | Step | File Target | Catatan |
|---|------|-------------|---------|
| 2.1 | Buat `App\Modules\Finance\FinanceServiceProvider` | - | - |
| 2.2 | Pindah Models: `Account, Journal, JournalLine, GeneralLedger, AccountBalance, ClosingPeriod, Payable, Receivable` | `app/Modules/Finance/Journal/Models/` dst | Per sub-modul |
| 2.3 | Pindah Entities, Repositories, Services Finance | `app/Modules/Finance/` | Per sub-modul |
| 2.4 | Pindah Controllers Finance | `app/Modules/Finance/*/Http/Controllers/` | - |
| 2.5 | Update route URL ke `/api/v1/accounting/*` | Sesuai Section 19 | - |
| 2.6 | Hapus namespace lama `App\Services\Accounting`, `App\Repositories\Accounting` | - | - |
| 2.7 | Jalankan test baseline Finance | Gate sebelum Fase 3 | - |

---

### 20.6 Fase 3 -- Clinical Module (Bagian 1: Registration + MedicalRecord)

**Tujuan:** Pindah domain klinis utama ke `App\Modules\Clinical`.
**Dependency:** Fase 1 (Core\Identity untuk User/Patient), Fase 2 (Finance untuk billing).

| # | Step | File Target | Catatan |
|---|------|-------------|---------|
| 3.1 | Buat `App\Modules\Clinical\ClinicalServiceProvider` | - | - |
| 3.2 | Pindah Models: `Patient, Registration` | `app/Modules/Clinical/Registration/Models/` | - |
| 3.3 | Pindah Models: `Encounter, Complaint, Prescription` | `app/Modules/Clinical/MedicalRecord/Models/` | - |
| 3.4 | Pindah Entities, Repositories, Services | Per sub-modul | - |
| 3.5 | Update route URL ke `/api/v1/clinic/*` | Sesuai Section 19 | - |
| 3.6 | Hapus namespace lama dari Operational | - | - |
| 3.7 | Jalankan test baseline Clinical\Registration + MedicalRecord | Gate | - |

---

### 20.7 Fase 4 -- Operational Module (Bagian 1: Master + Sales)

**Tujuan:** Pindah master data barang/mitra dan domain penjualan ke `App\Modules\Operational`.
**Dependency:** Fase 1 (Core), Fase 2 (Finance untuk jurnal).

| # | Step | File Target | Catatan |
|---|------|-------------|---------|
| 4.1 | Buat `App\Modules\Operational\OperationalServiceProvider` | - | - |
| 4.2 | Pindah Models Master: `Product, Supplier, Employee, Unit, Tax, Location (ex-Warehouse), PriceLevel, PaymentTerm` | `app/Modules/Operational/Master/Models/` | - |
| 4.2b | Pindah Models Lookup: `LookupSet, LookupValue, LookupValueDetail, LookupType, TempLookup, TempLookupDetail` | `modules/Operational/Master/Models/` | Dipindahkan dari Core\Platform; LookupValue tidak company-scoped, LookupValueDetail company-scoped |
| 4.2c | Pindah Entities, Repositories, Services Lookup: `LookupService, LookupSetService, LookupRepository, LookupSetRepository` + 10 Entity/DTO | `modules/Operational/Master/{Entities,Repositories,Services}/` | Binding dipindah dari CoreServiceProvider ke OperationalServiceProvider |
| 4.3 | Tulis Service publik `Operational\Master`: `findItem, findEmployee, findSupplier, validateUOM, getLookupSet, getLookupValues` | Berbasis Entity/DTO | Wajib sebelum modul lain pakai |
| 4.4 | Pindah Models Sales: `Sale, SaleItem, SalesOrder, DeliveryOrder, SalesQuotation` | `app/Modules/Operational/Sales/Models/` | - |
| 4.5 | Pindah Entities, Repositories, Services | Per sub-modul | - |
| 4.6 | Update route URL ke `/api/v1/master/*` dan `/api/v1/...` | Sesuai Section 19 | - |
| 4.7 | Jalankan test baseline Operational\Master + Sales | Gate | - |

---

### 20.8 Fase 5 -- Clinical (Bagian 2) + Operational (Bagian 2: Inventory + Procurement)

**Tujuan:** Pindah Pharmacy/Ancillary/Integration dan Inventory/Procurement.
**Dependency:** Fase 3 (Clinical Bag.1), Fase 4 (Operational Master & service publik stok).

| # | Step | Catatan |
|---|------|---------|
| 5.1 | Pindah Clinical\Pharmacy (Dispensing) | Pakai `Operational\Inventory` service publik untuk kurangi stok |
| 5.2 | Pindah Clinical\Ancillary (LabOrder, RadiologyOrder) | - |
| 5.3 | Pindah Clinical\Classification (Icd9Code, Icd10Code) | - |
| 5.4 | Pindah Clinical\Integration (Satusehat, Bpjs) | - |
| 5.5 | Pindah Operational\Inventory (Stock, StockTransfer, StockAdjustment) | Tulis Service publik: cek stok, reduce, return, transfer |
| 5.6 | Pindah Operational\Procurement (Purchase, PO, PR, RFQ, GoodsReceipt) | Pakai Service publik Inventory |
| 5.7 | Pindah Operational\Manufacturing (BOM, Assembly) | - |
| 5.8 | Jalankan test baseline semua sub-modul + cross-module integration | Gate |

---

### 20.9 Fase 6 -- Analytics Module

**Tujuan:** Pindah reporting & dashboard ke `App\Modules\Analytics`.
**Dependency:** Semua modul selesai (Analytics hanya baca, tidak tulis ke domain lain).

| # | Step | Catatan |
|---|------|---------|
| 6.1 | Pindah Analytics Services & Controllers termasuk laporan keuangan (cash-flow, income-statement, balance-sheet, trial-balance, financial-reports) ke `Analytics\Reports` | Baca dari semua modul via Service publik; perhitungan laporan pakai service publik Finance (FinancialReport*/CashFlow*) |
| 6.2 | Update route URL ke `/api/v1/dashboard`, `/api/v1/accounting/cash-flow`, `/api/v1/accounting/income-statement`, `/api/v1/accounting/balance-sheet`, `/api/v1/accounting/trial-balance`, dan `/api/v1/accounting/financial-reports` | Sesuai Section 19 |
| 6.3 | Jalankan test baseline Analytics | Gate |

---

### 20.10 Fase 7 -- Cleanup Namespace Lama

**Tujuan:** Hapus semua namespace lama, pastikan tidak ada sisa `App\Services\*` / `App\Repositories\*` / `App\Entities\*`.

| # | Step | Catatan |
|---|------|---------|
| 7.1 | Audit `rg "use App\\Services\|use App\\Repositories\|use App\\Entities"` | Harus 0 hasil |
| 7.2 | Hapus folder `app/Services/`, `app/Repositories/`, `app/Entities/` | - |
| 7.3 | Hapus folder `app/Models/` (model lama) | Sudah dipindah semua ke Modules |
| 7.4 | Update `AppServiceProvider` -- hapus semua binding lama | - |
| 7.5 | Jalankan full test suite -- semua hijau | Gate final |
| 7.6 | Update `composer.json` -- hapus autoload lama yang tidak terpakai | - |

---

### 20.11 Aturan Selama Pengerjaan

1. **1 modul = 1 PR** -- jangan gabung migrasi beberapa modul dalam 1 PR.
2. **Gate wajib** -- test baseline modul harus hijau sebelum lanjut ke modul berikutnya.
3. **Service publik dulu** -- sebelum modul lain bisa depend ke suatu modul, Service publik modul tersebut harus selesai dan return Entity/DTO.
4. **URL freeze** -- jangan ubah URL endpoint di luar yang sudah dipetakan di Section 19.
5. **Permission per modul** -- mapping permission lama ? final ditulis saat migrasi modul terkait.
6. **Tidak ada alias namespace lama** -- setelah dipindah, namespace lama langsung dihapus.
7. **AppMenu alias** -- update alias AppMenu dilakukan bersamaan dengan migrasi modul terkait.
8. **Fokus pengerjaan** -- semua pekerjaan utama dilakukan di `src/modules/`; perubahan di luar modules hanya diperbolehkan di lingkup `src/` (bukan di luar folder `src/`).
9. **Vue wajib VM + State** -- setiap halaman/komponen Vue wajib menggunakan ViewModel (VM) dan state via Pinia store; logic bisnis (fetch, transform, validasi, side effect) tidak boleh ada langsung di `<script setup>` komponen, semua harus ada di Pinia store. Koneksi ke Laravel dilakukan via Inertia (`usePage`, `router`, `useForm`) yang dipanggil dari dalam store, bukan dari komponen.
outer, useForm) yang dipanggil dari dalam store, bukan dari komponen.

---

### 20.12 Keputusan Arsitektur: Company Scope per Entitas

Keputusan ini bersifat final dan menjadi acuan implementasi seluruh modul.

#### Entitas Global (tanpa `company_id`)

| Entitas | Alasan |
|---|---|
| Satuan (Unit) | Universal -- kg, liter, pcs tidak pernah beda antar company |
| Perusahaan (Company) | Root entity, tidak mungkin scope ke dirinya sendiri |

#### Entitas Scoped ke Company (wajib `company_id`)

| Entitas | Alasan |
|---|---|
| Pajak | NPWP dan tarif PPN beda per badan hukum |
| Payment Term | Kebijakan piutang bisa beda per company |
| Gudang | Aset fisik milik entity tertentu, relasi via branch |
| Cabang | Relasi parent langsung ke company |

#### Entitas Hybrid: Lookup / Daftar Nilai

Owner final: `Operational\Master` (bukan Core\Platform). Lihat Section 4.3 dan Section 19.2 entry 8.

Scope perusahaan (keputusan final):
- LookupValue (parent) TIDAK company-scoped -- bersifat global lintas perusahaan.
- LookupValueDetail (detail/atribut tambahan) yang company-scoped -- override/atribut per perusahaan.

Query rule: ambil detail company-scoped dulu, fallback ke nilai global LookupValue jika tidak ada detail untuk company tersebut.

#### Superadmin -- Tidak Diikat ke Company

- Kolom `company_id` di tabel users nullable; superadmin diisi `null`
- Flag `is_super_admin = true` di tabel users
- `CompanyScope::apply()` bypass total jika `is_super_admin`
- Filter per company via `?company_id=xxx` sebagai explicit override di request
- UI: dropdown "Konteks Perusahaan" di navbar -- disimpan di session, bukan di DB; opsi "Semua" atau pilih satu company

```php
// CompanyScope::apply()
if (auth()->user()?->is_super_admin) {
    if ($companyId = request('company_id')) {
        $builder->where('company_id', $companyId);
    }
    return;
}
$builder->where('company_id', auth()->user()->company_id);
```


### 20.13 Aturan Vue VM + State -- Pilihan Pola

Dua pola yang diizinkan, pilih sesuai kebutuhan:

| Pola | Kapan Dipakai | Contoh |
|---|---|---|
| **Simpel (State + VM langsung)** | Fitur CRUD standar tanpa logika domain kompleks. VM memanggil piService langsung. | 	ax.vm.ts, warehouse.vm.ts, supplier.vm.ts |
| **Lengkap (State + Domain Layer + VM)** | Fitur dengan banyak usecase, transformasi domain, atau perlu unit test mendalam. VM inject usecase via DI. | unit.vm.ts, lookup.vm.ts |

**Aturan wajib untuk kedua pola:**
- State/interface ditulis di file *.state.ts terpisah.
- Logic bisnis, fetch, validasi ada di Pinia store (VM) -- **bukan** di <script setup> komponen.
- Koneksi ke Laravel (usePage,
outer, useForm, piService) dipanggil dari dalam store.
- Komponen Vue hanya boleh: import vm, m.method(), dan template binding.
- Pola lengkap (usecase+repo TS) **tidak wajib** untuk fitur CRUD sederhana -- cukup pola simpel.
- VM **dilarang import atau memakai Repository/RepoImpl secara langsung**. Pada pola simpel VM boleh memanggil `apiService`; pada pola lengkap VM hanya boleh memanggil usecase via DI. Repository hanya boleh diakses oleh usecase atau DI yang membentuk usecase.

**Aturan penamaan file VM + State:**
- Nama file VM dan State wajib menggunakan snake_case: {feature_name}.vm.ts dan {feature_name}.state.ts
- Benar: item_conversion.vm.ts, price_level.vm.ts, payment_term.vm.ts
- Salah: item-conversion.vm.ts (kebab-case tidak diizinkan untuk file VM/State)
- Nama fitur di VM harus sama persis dengan nama .vue (tanpa sufiks Index), dikonversi ke snake_case.
- Mapping: ItemConversionIndex.vue -> item_conversion.vm.ts + item_conversion.state.ts

### 20.14 Aturan Konsistensi Nama .vue, VM, dan State

Setiap fitur harus memiliki nama yang sinkron antara file .vue, vm.ts, dan state.ts agar mudah diidentifikasi dan tidak tertukar.

**Aturan wajib:**
- Nama file .vue (tanpa sufiks Index/Form/Detail), vm.ts, dan state.ts harus mengacu pada nama fitur yang sama.
- Format: `{FeatureName}Index.vue` <-> `{feature_name}.vm.ts` <-> `{feature_name}.state.ts`
- Contoh benar:
  - `PriceLevelIndex.vue` <-> `price_level.vm.ts` <-> `price_level.state.ts`
  - `ItemConversionIndex.vue` <-> `item_conversion.vm.ts` <-> `item_conversion.state.ts`
  - `PaymentTermIndex.vue` <-> `payment_term.vm.ts` <-> `payment_term.state.ts`
- File .vue **dilarang** import VM dari fitur lain -- setiap .vue hanya boleh import VM milik fiturnya sendiri.
- Pengecualian satu-satunya: views yang memang berbagi VM karena satu entitas (contoh: BrandIndex, ProductCategoryIndex, dll yang semuanya adalah LOV set -- boleh import `lov_set.vm`), asalkan backend-nya juga satu endpoint (`/lov/sets`).
- Jika suatu view punya controller + endpoint API sendiri, wajib punya VM sendiri -- tidak boleh numpang VM lain.

**Checklist sebelum membuat .vue baru:**
1. Sudah ada `{feature_name}.vm.ts` dan `{feature_name}.state.ts` di folder `viewmodels/`?
2. Nama fitur di .vue, vm, dan state sudah sinkron (snake_case untuk file, PascalCase untuk komponen)?
3. Import di .vue hanya mengacu ke VM fiturnya sendiri?


### 20.15 Aturan Wajib: DB Transaction di Repository

Setiap method repository yang melakukan **operasi tulis** (INSERT, UPDATE, DELETE) ke database wajib menggunakan pola **try-catch + DB transaction eksplisit** (`beginTransaction` / `commit` / `rollBack`). Pola closure `DB::transaction(function () { ... })` **dilarang** -- diganti dengan pola eksplisit untuk kontrol rollback yang jelas dan stack trace yang akurat. Tujuan: konsistensi, atomicitas, dan antisipasi penambahan operasi tulis di masa depan. Jika salah satu langkah gagal, semua perubahan di-rollback secara otomatis.

**Aturan wajib:**
- **Semua** method write (single maupun multi) wajib menggunakan try-catch + `beginTransaction/commit/rollBack` untuk konsistensi dan atomicitas.
- Method yang melakukan **dua operasi tulis atau lebih** (misal: upsert header + delete items + insert items loop) **mutlak wajib** -- tanpa transaction, partial write bisa terjadi.
- **Pola yang wajib** (berlaku untuk SEMUA repository tanpa pengecualian): `DB::beginTransaction()` + `try { ... DB::commit(); return $result; } catch (\Throwable $e) { DB::rollBack(); throw $e; }` untuk method non-void, dan `DB::beginTransaction()` + `try { ... DB::commit(); } catch (\Throwable $e) { DB::rollBack(); throw $e; }` untuk method `: void`.
- **Dilarang** menggunakan closure `DB::transaction(function () { ... })` -- seluruh codebase sudah dimigrasi ke pola eksplisit try-catch.
- **Dilarang** menggunakan `DB::beginTransaction()` / `DB::commit()` / `DB::rollBack()` tanpa `try-catch` -- jika exception terjadi di luar catch, transaksi tidak akan di-rollback.
- Transaction **di Repository**, bukan di Service atau Controller - karena repository adalah pemilik akses data.
- Jika sebuah Service memanggil dua method repository yang masing-masing atomic, Service bertanggung jawab membungkus seluruh flow dalam `DB::beginTransaction()` + try-catch di level Service.

**Contoh benar:**

```php
// Repository - multi-write wajib pakai try-catch + transaction
public function save(OrderEntity $entity): OrderEntity
{
    DB::beginTransaction();
    try {
        $order = Order::updateOrCreate(['uuid' => $entity->uuid], [...]);
        $order->items()->delete();
        foreach ($entity->items as $item) {
            $order->items()->create([...]);
        }
        DB::commit();
        return OrderEntity::fromModel($order->load('items'));
    } catch (\Throwable $e) {
        DB::rollBack();
        throw $e;
    }
}

// Repository - single-write tetap pakai try-catch + transaction
public function delete(string $uuid): void
{
    DB::beginTransaction();
    try {
        Order::where('uuid', $uuid)->firstOrFail()->delete();
        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        throw $e;
    }
}
```

**Contoh salah:**
```php
// SALAH #1 - multi-write tanpa transaction; jika insert item gagal, header sudah tersimpan
public function save(OrderEntity $entity): OrderEntity
{
    $order = Order::updateOrCreate(['uuid' => $entity->uuid], [...]);
    $order->items()->delete();
    foreach ($entity->items as $item) {
        $order->items()->create([...]);
    }
    return OrderEntity::fromModel($order->load('items'));
}

// SALAH #2 - closure DB::transaction() dilarang; gunakan try-catch eksplisit
public function save(OrderEntity $entity): OrderEntity
{
    return DB::transaction(function () use ($entity) {
        // ... logic ...
    });
}

// SALAH #3 - beginTransaction tanpa try-catch; rollBack tidak terjamin
public function save(OrderEntity $entity): OrderEntity
{
    DB::beginTransaction();
    $order = Order::create([...]);
    DB::commit(); // jika create() throw, rollBack tidak pernah dipanggil
    return $order;
}
```

**Status audit (2026-09-19) -- AUDIT LENGKAP:**

| Lingkup Audit | Jumlah | Status |
|---|---|---|
| Total file Repository di codebase | 89 file | -- |
| File dengan method write (save/store/create/update/delete/destroy) | 66 file | âœ… Semua sudah pakai try-catch + `beginTransaction/commit/rollBack` |
| File read-only (tidak ada method write) | 23 file | N/A (tidak perlu transaction) |
| Total method write yang di-wrap | 155 method | âœ… Semua sudah pakai try-catch |
| Closure `DB::transaction()` tersisa | 0 | âœ… Dilarang -- semua sudah dimigrasi |
| Pengecualian (mix shell/DB) | 2 method | `exportDatabase` + `createRestoreSafeguard` di `SettingsRepository` -- hanya bagian DB yang di-wrap |

**Catatan implementasi:**
- Method non-void: `DB::commit()` dipanggil **sebelum** `return` di dalam `try` block.
- Method `: void`: `DB::commit()` dipanggil di **akhir** `try` block.
- `catch (\Throwable $e)` menangkap semua exception (termasuk `QueryException`, `RuntimeException`, dll).
- `throw $e` setelah `rollBack()` memastikan exception propagasi ke caller (Controller/Service) untuk error handling atau HTTP response.
- Pengecualian: method yang mix operasi shell/file I/O + DB (mis. `exportDatabase` yang menjalankan `mysqldump` lalu insert) hanya membungkus bagian DB-nya dalam transaction, bukan seluruh method, agar transaksi tidak ditahan selama operasi file I/O yang lambat.

### 20.16 Aturan Komponen Data UI: Satu Komponen, Dua Mode Data

Komponen UI data seperti `DataTable` dan `Select2` wajib punya satu kontrak komponen publik, bukan duplikasi komponen untuk setiap mode.

**Aturan wajib:**

| Item | Aturan |
|---|---|
| Komponen | Satu file `.vue` untuk tampilan dan interaksi umum |
| Mode data | Client-side dipilih lewat `items`/`options`; server-side dipilih lewat `url`/`endpoint` atau `mode` eksplisit |
| Logika | Search, filter, sort, pagination, debounce, dan fetch hanya boleh ada di VM Pinia |
| State awal | Wajib file `*.state.ts` terpisah |
| Kontrak | Interface props, emit, option, dan response wajib di `*-types.ts` |
| Dilarang | Membuat `DataTableClientSide.vue`, `DataTableServerSide.vue`, `Select2ClientSide.vue`, atau `Select2ServerSide.vue` sebagai implementasi utama |
| Kompatibilitas | Nama komponen lama boleh menjadi wrapper deprecation sampai semua pemakaian dimigrasi |

**Acuan implementasi:**

1. `DataTable.vue` + `DataTable.vm.ts` adalah pola dasar untuk tabel client/server-side.
2. `Components/common/Select2.vue` + `Components/common/select2.vm.ts` adalah target final untuk dropdown client/server-side.
3. Wrapper `Select2ClientSide.vue` dan `AsyncSelect2.vue` hanya untuk backward compatibility.
4. Server-side dropdown wajib menangani debounce, race request, parameter dinamis, dan opsi terpilih yang belum ada di halaman aktif.
5. Client-side hanya untuk dataset kecil; dataset besar wajib memakai endpoint server-side.

## Progress Log

> Progress log harian dipindahkan ke file terpisah: \_progress.md\ agar \_plan.md\ tetap menjadi sumber keputusan stabil dan tidak bercampur dengan log eksekusi.

## 21. Kebijakan Upload File Produk

- Product wajib mendukung multi-file gambar/PDF melalui tabel `item_images` dengan `sort_order` sesuai urutan tampil di form.
- Setiap file wajib berupa `jpg`, `jpeg`, `png`, `webp`, atau `pdf`; ukuran maksimal 2 MB per file.
- Total file yang tersimpan per produk maksimal 10, dihitung dari gabungan file existing dan upload baru.
- File pada posisi pertama (`sort_order = 0`) adalah file utama dan wajib disimpan ke kolom `items.image` untuk tampilan menu/listing.
- Jika file pertama dihapus, file berikutnya secara otomatis menjadi file utama pada penyimpanan berikutnya.
- File existing wajib dimiliki produk yang sedang diedit; path bebas atau milik produk lain harus ditolak.
- File fisik pada storage wajib dihapus ketika file tersebut tidak lagi dipakai pada produk.

## 22. Kebijakan Form & Upsert Produk

- Produk mendukung alur create dan update dalam satu konsep upsert dengan validasi data utama maupun data per tab.
- Seluruh input form produk dan input pada setiap tab wajib dipersist melalui `ProductService` dan `ProductRepository`, bukan melalui query model langsung di controller/view.
- `ProductRepository::syncProductRelations()` menjadi titik penyimpanan relasi tab produk: `accounts`, `units`, `prices`, `opening_balances`, dan `discounts`.
- Kolom tambahan untuk tab produk wajib menjadi bagian migration utama `2026_04_05_000100_create_item_support_tables.php`; jangan menambahkan migration terpisah untuk field yang sudah dimiliki struktur tersebut.
- Testing penyimpanan form wajib mencakup PHP feature test dengan data nyata dan E2E Playwright melalui UI nyata.
- Skenario test harus memverifikasi data utama produk dan data relasi pada semua tab setelah create maupun update.
- Data testing wajib dibuat dengan prefix/fixture khusus dan dihapus otomatis di akhir sesi menggunakan `CleanupE2eData`.

> Blok "Temuan Gap / Perlu Perhatian" (audit 2026-09-22 & 2026-09-25)
> dipindah ke `_progress.md` (entri 17.73, blok "22 - Temuan Gap").

---

## 23. Analisis Kelayakan Todo Terhadap Standar Industri (2026-09-29)

Audit kelayakan seluruh todo terbuka terhadap standar industri (DDD, Clean Architecture, Laravel 12 convention).

### 23.1 Multi-org Langkah 4 -- Helper current_company_uuid()

**Relevan: ya. Standar terbaik: belum.**

Pola middleware + global helper (
equest()->attributes) berfungsi, tapi membuat testing lebih sulit dan menimbulkan hidden coupling.

| Pendekatan | Standar | Catatan |
|---|---|---|
| Global helper function | Pragmatis | Sulit di-mock test, hidden dependency |
| Laravel Context facade | Laravel 11+ | Built-in, PSR-compatible, terintegrasi log |
| Injectable CompanyContext service | DDD | Testable, explicit dependency |

**Rekomendasi:** Lanjutkan migrasi ke helper (sudah selesai 2026-09-29), tapi di akhir upgrade helper menjadi wrapper untuk Context::get() atau service injectable. Jangan ganti pendekatan di tengah jalan.

### 23.2 Multi-org Langkah 6 -- Drop Kolom Legacy

**Relevan: ya. Sesuai standar: ya.**

Setelah 0 pemakaian langsung, drop kolom + FK + index adalah praktik standar. Tidak ada alternatif lebih baik.

### 23.3 Module Boundary Violations

**Relevan: ya. Wajib diperbaiki.**

- WebAuthService -- ttemptLogin(): ?User dan issueApiToken(User ) return Eloquent Model dari Service, melanggar Section 11b. Solusi: convert ke Entity/DTO setelah Auth::attempt().
- ProductService -- import Product Model untuk $ownedProduct. Solusi: gunakan ProductEntity saja (repository sudah return Entity).

Standar industri (DDD/Clean Architecture): Service tidak pernah expose Model.

### 23.4 Endpoint "new" (Sales Quotation, Purchase Quotation, dll.)

**Relevan: kondisional. Prioritas: rendah.**

Jangan bangun karena plan bilang begitu -- bangun ketika ada kebutuhan bisnis nyata. Membangun endpoint tanpa consumer menambah maintenance burden.

### 23.5 source_type/source_uuid di purchase_order_items

**Relevan: dipertanyakan. Mungkin over-engineering.**

Polymorphic FK kehilangan referential integrity. Jika sumbernya hanya 2 (PR atau direct), cukup purchase_requisition_uuid nullable -- lebih sederhana, punya FK constraint. Polymorphic hanya masuk akal jika akan ada banyak sumber berbeda.

### 23.6 Prioritas Eksekusi

> Tabel prioritas eksekusi todo dipindah ke `_progress.md` (entri 17.73,
> blok "23.6 Prioritas Eksekusi"). Analisis 23.1-23.5 tetap di atas.

---

## 24. Rencana Performansi & Skalabilitas (Performance & Scalability Plan)

Status: DIPUTUSKAN -- Tetap Laravel, tidak ganti bahasa. Target 100 user aktif harian (sampai 500+ dengan mitigasi).

### 24.1 Kapasitas & Threshold

| Metrik | Nilai Target | Konteks |
|---|---|---|
| User aktif harian | 100 (fase 1), 500 (fase 2) | Growth projection |
| User konkuren puncak | 30 (fase 1), 150 (fase 2) | Tidak semua akses bersamaan |
| Request/second (CRUD + listing) | 50-100 (fase 1), 300-500 (fase 2) | Aplikasi ERP klinis |
| Response time P95 | < 200ms | Standar UX |
| Response time P99 | < 500ms | Threshold alerting |

### 24.2 Fase 1 -- fondasi Performansi (100 user, immediate)

#### 24.2.1 Redis Cache

- Cache lookup data: unit, tax, payment-term, price-level, lookup-set/value (TTL 15-60 menit, invalidasi on write)
- Cache permission per user: Spatie Permission cache driver Redis (TTL 60 menit, flush saat role/permission berubah)
- Cache session driver Redis
- Cache rate limiter
- Config: CACHE_STORE=redis, SESSION_DRIVER=redis, QUEUE_CONNECTION=redis
- Implementasi: helper CacheHelper::rememberEternal() dengan tag-based invalidation di Repository/Service
- Wajib cache: dropdown options (unit, tax, lookup), sidebar menu per role, dashboard summary

#### 24.2.2 Queue untuk Proses Berat

- Driver: Redis (bukan database -- performa lebih baik)
- Worker: Horizon (monitoring + auto-restart + supervisor)
- Proses yang wajib queue:
  - Export laporan (Excel/CSV > 1000 baris)
  - Import bulk (> 500 baris)
  - Backup database
  - Notification (email, WhatsApp, SATUSEHAT sync)
  - Audit trail bulk write
- Pattern: ShouldQueue di Job class, dispatch() di Service/Controller, progress via Cache::put() + polling dari FE
- Wajib: timeout + retry (3x, backoff 10s), failed_job monitoring di Horizon dashboard

#### 24.2.3 Database Indexing

- Compound index wajib di semua tabel tenant-owned:
  - idx_company_branch_deleted (company_uuid, branch_uuid, deleted_at)
  - idx_company_branch_created (company_uuid, branch_uuid, created_at)
  - idx_unique_code (company_uuid, branch_uuid, code) -- jika ada kolom code
- Foreign key index: semua kolom *_uuid yang jadi FK harus punya index
- Soft delete index: deleted_at index terpisah untuk query "belum dihapus"
- Wajib review dengan EXPLAIN sebelum merge migration baru
- Tool: php artisan db:show --counts + slow query log (threshold 200ms)

#### 24.2.4 PHP Runtime

- PHP 8.3+ wajib (dengan JIT)
- OPcache: opcache.enable=1, opcache.memory_consumption=256, opcache.max_accelerated_files=20000, opcache.validate_timestamps=0 di production
- JIT: opcache.jit_buffer_size=128M, opcache.jit=tracing
- Realpath cache:
ealpath_cache_size=4096K,
ealpath_cache_ttl=600
- Deploy: php artisan config:cache && php artisan route:cache && php artisan view:cache && composer install --no-dev --optimize-autoloader

### 24.3 Fase 2 -- Scaling (500+ user, saat dibutuhkan)

Trigger: user konkuren > 200 atau P95 > 300ms konsisten selama 1 minggu.

#### 24.3.1 Horizontal Scaling

- Load balancer (Nginx/HAProxy) + 2+ app server
- Session/cache di Redis (sudah dari Fase 1) -- memungkinkan stateless app server
- File storage: pindah dari local disk ke S3/MinIO (untuk upload produk, backup)
- Queue worker terpisah dari web server (dedicated worker node)

#### 24.3.2 Database Read Replication

- MySQL/PostgreSQL read replica untuk query laporan/dashboard
- Config: DB_READ_HOST di .env, stale_reads di Eloquent (readFromWriteWhenWriting)
- Analytics/Reports service (read-only) diarahkan ke replica

#### 24.3.3 CDN untuk Asset

- Static asset (CSS, JS, image produk) di Cloudflare/AWS CloudFront
- Build asset dengan hash -- immutable caching (Cache-Control: max-age=31536000, immutable)

#### 24.3.4 Monitoring & Alerting

- Laravel Telescope (dev) / Laravel Pulse (production)
- Alert: P95 > 300ms, error rate > 1%, queue backlog > 100, disk > 80%
- Tool: Sentry (error tracking) + Uptime Robot (availability) + Grafana/Prometheus (infrastructure)

### 24.4 Fase 3 -- Microservice untuk Fitur Tertentu (1000+ user / real-time)

Trigger: fitur real-time dense (live monitoring dashboard dengan WebSocket, stream sensor data) atau compute-heavy (ML/AI).

| Kandidat | Bahasa | Alasan |
|---|---|---|
| Real-time notification service | Node.js (Socket.io) | WebSocket native, lightweight |
| Report generator (heavy compute) | Go | Concurrent, fast, low memory |
| ML/analytics engine | Python (FastAPI) | Ecosystem ML |

- Komunikasi: REST + Redis pub/sub (bukan gRPC -- overkill untuk saat ini)
- Laravel tetap "master" -- microservice dipanggil via HTTP, auth via Sanctum token yang sama
- Jangan pecah domain ERP/Klinis menjadi microservice -- tetap monolith modular

### 24.5 Roadmap Eksekusi

| # | Task | Fase | Priority | Estimasi |
|---|---|---|---|---|
| 1 | Setup Redis server + config cache/session/queue | 1 | Tinggi | 1 hari |
| 2 | Implementasi cache di lookup/options/permission | 1 | Tinggi | 3 hari |
| 3 | Setup Horizon + migrasi job berat ke queue | 1 | Tinggi | 3 hari |
| 4 | Audit + tambah compound index (via migration) | 1 | Tinggi | 2 hari |
| 5 | Konfigurasi PHP 8.3 OPcache+JIT di production | 1 | Sedang | 0.5 hari |
| 6 | Setup monitoring (Pulse/Sentry) | 2 | Sedang | 2 hari |
| 7 | Load balancer + app server kedua | 2 | Rendah | 2 hari |
| 8 | Read replica + config stale_reads | 2 | Rendah | 1 hari |
| 9 | CDN untuk static asset | 2 | Rendah | 1 hari |
| 10 | Evaluasi microservice (jika trigger tercapai) | 3 | Rendah | TBD |

### 24.6 Gate Kriteria

- Fase 1 selesai: P95 < 200ms dengan 100 user konkuren (load test dengan k6/Artillery)
- Fase 2 dimulai: P95 > 300ms konsisten ATAU user konkuren > 200
- Fase 3 dimulai: ada fitur real-time dense ATAU user konkuren > 500

### 24.7 Anti-Pattern yang Dihindari

- Jangan premature optimization -- implementasi Fase 2/3 sebelum tercapai threshold
- Jangan microservice untuk domain bisnis (ERP/Klinis) -- tetap modular monolith
- Jangan cache data yang sering berubah (stok real-time, saldo kas) tanpa invalidasi tepat
- Jangan pakai CACHE_DRIVER=file di production (I/O bottleneck)
- Jangan jalankan queue worker di web server yang sama tanpa resource limit

### 24.8 Status Implementasi vs Rencana (Audit 2026-09-29)

> Status implementasi vs rencana + status task eksekusi Fase 1 dipindah
> ke `_progress.md` (entri 17.73, blok "24.8 Status Implementasi vs Rencana").

---

## 25. Rencana Laravel Reverb + Async Integration (Real-Time & SATUSEHAT/BPJS)

Status: DIPUTUSKAN -- Pakai Laravel Reverb (gratis, first-party) + Redis Queue + Horizon. Tidak perlu Node.js microservice untuk saat ini.

### 25.1 Arsitektur

`
Vue 3 SPA (Frontend)
  |  REST API (axios)
  |  WebSocket (Laravel Echo + Reverb)
  v
Laravel API (Backend)
  |-- Controllers (REST)
  |-- Events / Broadcasts
  |-- Webhook Receivers (SATUSEHAT, BPJS)
  |
  v
Redis (pub/sub + queue)
  |
  +-- Laravel Reverb (WebSocket server)
  +-- Horizon (Queue workers)
  +-- Laravel Scheduler (cron)
`

### 25.2 Install & Konfigurasi

#### Step 1: Install Reverb

`
composer require laravel/reverb
php artisan reverb:install
`

Config output: .env tambahan:
- REVERB_APP_ID, REVERB_APP_KEY, REVERB_APP_SECRET
- REVERB_HOST, REVERB_PORT, REVERB_SCHEME

#### Step 2: Install Horizon

`
composer require laravel/horizon
php artisan horizon:install
`

Config: config/horizon.php -- supervisor untuk queue default, satusehat, pjs.

#### Step 3: Update .env

`
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
BROADCAST_CONNECTION=reverb
`

#### Step 4: Frontend (Vue 3)

`
npm install --save laravel-echo pusher-js
`

Setup
esources/js/echo.ts:
- window.Echo = new Echo({ broadcaster: 'reverb', key: import.meta.env.VITE_REVERB_APP_KEY, wsHost: ..., wsPort: ... })

### 25.3 Channel Design

| Channel | Type | Isi Broadcast | Listener |
|---|---|---|---|
| private-company.{uuid} | Private | Semua event scoped company | Semua user di company tsb |
| private-branch.{uuid} | Private | Stok, pasien, antrian per branch | User di branch tsb |
| private-user.{uuid} | Private | Notifikasi personal, task assignment | User individu |
| satusehat-sync.{company} | Private | Status sync FHIR (progress/success/failed) | Admin/k berth |
| dashboard.{branch} | Private | Live counter: antrian, transaksi hari ini | Dashboard widget |

Wajib: Channel authorization di
outes/channels.php -- cek user->belongsToCompany() atau user->belongsToBranch().

### 25.4 Event Design (Broadcastable)

| Event | Trigger | Broadcast ke | Data |
|---|---|---|---|
| StockLevelChanged | StockAdjustment/Transfer/Dispensing saved | private-branch.{uuid} | product_uuid, location_uuid, new_balance |
| PatientRegistered | Registration created | private-branch.{uuid} | patient summary |
| EncounterUpdated | Encounter status change | private-user.{doctor_uuid} + private-branch.{uuid} | encounter status |
| SatusehatSyncProgress | Job FHIR mapping progress | satusehat-sync.{company} | percentage, status, errors |
| DashboardMetricsUpdated | Setiap create/update transaksi | dashboard.{branch} | counters (antrian, transaksi, omzet) |
| NotificationSent | Job notification selesai | private-user.{uuid} | title, message, type |

### 25.5 SATUSEHAT Async Flow

`
1. Webhook POST /api/v1/satusehat/webhook dari SATUSEHAT
2. Controller: validate signature -> dispatch Job -> return 200 (dalam < 50ms)
3. Job SatusehatSyncJob:
   a. Map data FHIR <-> Internal Entity
   b. Save ke database (transaction)
   c. Broadcast SatusehatSyncProgress (per item)
   d. Broadcast SatusehatSyncCompleted (saat selesai)
4. Horizon: retry 3x backoff 10s jika fail; dead letter ke failed_jobs
5. Frontend: listen satusehat-sync.{company} -> progress bar real-time
`

### 25.6 BPJS Async Flow

`
1. Scheduled polling (Scheduler): setiap 5 menit -> dispatch BpjsSyncJob
2. Job: fetch antrean/peserta/rujukan dari BPJS API
3. Update database (transaction)
4. Broadcast AntrianUpdated ke private-branch.{uuid}
5. Frontend dashboard antrian live update
`

### 25.7 Fase Implementasi

> Tabel status eksekusi fase implementasi Reverb dipindah ke
> `_progress.md` (entri 17.73, blok "25.7 Fase Implementasi").

### 25.8 Testing Strategy

- Unit: test Event class implements ShouldBroadcast, channel yang benar, payload struktur
- Feature: test WebhookController return 200 + job dispatched, test Channel authorization (403 jika bukan member)
- E2E: Playwright dengan Echo mock listener -- verifikasi UI update tanpa refresh
- Load test: k6 dengan 100 concurrent WS connections -- verifikasi Reverb tidak drop

### 25.9 Production Checklist

> Dipindah ke `_progress.md` (entri 17.73, blok "25.9 Production Checklist").

---

## 26. Multi-org Migration Gap -- Audit Column & Branch Scope (2026-09-30)

Status: DIPUTUSKAN -- 2 gap teridentifikasi dari audit migration multi-org.

### 26.1 Gap 1: Pivot Tables Kurang Audit Columns

Pivot user_companies dan user_branches hanya punya created_by/created_at. Standar proyek wajib created_by, updated_by, deleted_by, deleted_at (bigInteger mikrodetik).

**Tabel terdampak:**
- user_companies -- tambah updated_at, updated_by, deleted_by, deleted_at (bigInteger unsigned default 0)
- user_branches -- tambah updated_at, updated_by, deleted_by, deleted_at (bigInteger unsigned default 0)

**Deliverable:** Migration alter table + update Model pivot untuk include kolom.

### 26.2 Gap 2: Tabel Master Kurang branch_uuid Scope

Beberapa tabel master hanya punya company_uuid tanpa ranch_uuid, padahal data bisa per-cabang.

**Tabel terdampak (tambah kolom ranch_uuid + compound index):**
- employees -- tambah ranch_uuid + index (company_uuid, branch_uuid, deleted_at)
- suppliers -- tambah ranch_uuid + index (company_uuid, branch_uuid, deleted_at)
- payment_terms -- tambah ranch_uuid + index (company_uuid, branch_uuid, deleted_at)
- 	axes -- tambah ranch_uuid + index (company_uuid, branch_uuid, deleted_at)

**Aturan:** Data yang memang global per-company (misal currencies, lookup_sets) TIDAK perlu ranch_uuid. Hanya data yang bisa berbeda antar cabang.

**Deliverable:** Migration alter table + update Model + update CompanyScope/BranchScope jika perlu.

### 26.3 TDD Test Plan

---

## 27. Dark Mode UI System (2026-09-30)

Status: SELESAI untuk layout, master product, tabel, dan form produk.

### 27.1 Prinsip Implementasi

- Gunakan class `html.dark`, bukan media query saja, agar preferensi user bisa dipertahankan.
- Simpan preferensi di `localStorage` key `klinik-theme`; default mengikuti `prefers-color-scheme`.
- Pasang inline script sebelum Vite untuk anti-flash saat pertama render.
- Semua komponen baru wajib pakai CSS variable (`--lv-*`, `--card`, `--border`, `--text`), tidak hardcoded hex.
- Kontras teks pada tombol/gradient memakai `--on-primary`, bukan `--lv-surface`, karena surface berubah jadi gelap di dark mode.

### 27.2 Deliverable

| Item | Detail | Status |
|---|---|---|
| Theme token | Dark palette pada `src/resources/css/theme.css` | Selesai |
| Dark utility | Override Tailwind, input, scrollbar, shadow, inline style legacy pada `src/resources/css/dark-theme.css` | Selesai |
| Toggle | Tombol bulan/matahari di header kiri | Selesai |
| Anti-flash | Inline script sebelum CSS/application | Selesai |
| Layout | `body` tidak lagi pakai Tailwind light-only class | Selesai |
| DataTable | Konversi warna hardcoded ke token tema | Selesai |
| Product page | Konversi `ProductIndex.vue` dan `ProductForm.vue` | Selesai |
| Dashboard | Override glass surface dan premium shadow | Selesai |

### 27.3 Aturan Lanjutan

- Setiap halaman `.vue` baru wajib mengikuti token tema; tidak menambah hex warna neutral.
- Jika menemukan halaman lama dengan warna hardcoded, konversi ke token sebelum menambah fitur.
- Guest auth pages (Login/Register) boleh punya visual sendiri, tapi tetap kontras dan tidak menimpa utility dark global.

---

## 28. Vue Feature Architecture Guard (2026-09-30)

Status: SELESAI.

### 28.1 Aturan

- Feature `.vue` hanya render data VM, meneruskan aksi user, dan memanggil lifecycle VM.
- Logic bisnis, API, validasi, transformasi data, keyboard shortcut, dan formatting dipindah ke VM/helper TS.
- Initial state dan UI refs dipisah ke file state per feature; feature `.vue` tidak membuat `ref`, `reactive`, `computed`, atau `watch`.
- Shared presentational components di `Core/shared/Components` tetap boleh menjadi adapter Vue, tetapi tidak boleh memanggil API/business service.

### 28.2 Guard Test

- `modules/Core/shared/tests/common/vue_architecture.spec.ts` memindai seluruh feature `.vue`.
- Test memblokir API/browser call, inline shortcut, reactive helper, function declaration, transformasi collection, dan inline array/object state.

| Test | Scope |
|---|---|
| PivotAuditColumnsTest | Verifikasi user_companies dan user_branches punya kolom updated_by, deleted_by, deleted_at |
| MasterBranchScopeTest | Verifikasi employees, suppliers, payment_terms, 	axes punya kolom ranch_uuid + compound index (company_uuid, branch_uuid, deleted_at) |

---

## 29. QA & Static Analysis Infrastructure (2026-10-06)

Status: SELESAI -- CI Application + coverage gate + static analysis.

### 29.1 CI Pipeline

CI disederhanakan dari monorepo (be/fe/docker detect-changes) menjadi
**Application CI** yang hanya fokus pada src/ dan scripts/:

| Job | Isi |
|---|---|
| php-pint | Code style Laravel Pint |
| php-static | PHPStan level 1 (src/phpstan.neon) |
| php-tests | PHPUnit + coverage gate clover |
| frontend-typecheck | vue-tsc typecheck |
| frontend-vitest | Unit test Vitest + coverage gate |
| playwright | E2E test + coverage gate |

Trigger path: src/**, scripts/**, .github/workflows/ci.yml.

### 29.2 Coverage Gate

Coverage minimum 1% (threshold parameter script, dinaikkan bertahap):

- PHP: scripts/check-clover-coverage.php membaca coverage/clover.xml.
- Playwright: scripts/check-playwright-coverage.mjs.
- Vitest: scripts/check-vitest-coverage.mjs.

### 29.3 PHPStan Scope

Analisis level 1 diberlakukan bertahap pada file yang paling sering
berubah (fail fast di area aktif, sisanya menyusul):

- modules/Operational/Master/Support (ProductFilter, ProductFilterBuilder)
- modules/Operational/Master/Services/ProductExportService.php
- modules/Operational/Master/Services/ProductImportService.php
- modules/Core/Identity/Repositories/UserRepository.php
- modules/Core/Identity/Http/Controllers/UserController.php

Aturan: file baru di area ini wajib lolos phpstan sebelum merge.

#### 29.3.1 Ekspansi Scope ke Modul Analytics + Finance (2026-10-09)

Scope diperluas menjadi dua modul penuh (hasil akhir: 0 error):

- `modules/Analytics` (seluruh modul)
- `modules/Finance` (seluruh modul)

Konfigurasi `phpstan.neon` yang diperlukan agar relasi Eloquent
terdeteksi statis (menghilangkan false positive
`larastan.relationExistence` untuk relasi yang jelas ada):

- `databaseMigrationsPath: [modules]` -- larastan membaca skema tabel
  dari migrasi modul.
- `excludePaths.analyse`: `modules/*/tests` dan
  `modules/*/database/migrations`.

Pola perbaikan yang terbukti (urut dampak):

1. **Kelas duplikat dihapus** -- `Finance/Journal/Entities/AccountingEntity.php`
   (duplikat CoaEntity/AccountEntity/AssetEntity, 0 referensi) dan
   `Finance/Journal/Models/AccountBalance.php` (dead code; semua import
   menunjuk `Finance\Ledger\Models\AccountBalance`). Definisi ganda
   mengacaukan resolusi tipe PHPStan (54 -> 32 error).
2. **Typed relation return type** -- pola yang andal memperbaiki false
   positive `relationExistence`:
   ```php
   /** @return BelongsTo<Account, $this> */
   public function account(): BelongsTo
   ```
   Diterapkan pada 11 model yang masih ter-flag: JournalLine (account),
   AccountMapping (account), AccountBalance (account), GeneralLedger
   (account), TaxSetting (account), ItemOpeningBalance (item/warehouse),
   PersonBalance (person), Encounter (patient/doctor), Sale (patient),
   ServicePackage (items), Purchase (supplier). Tanpa return type
   native, larastan menganggap return relasi `mixed`.
3. **Import same-namespace** -- kelas relasi di namespace sama tetap
   harus di-`use` eksplisit (PersonBalance+Location/Person,
   ItemOpeningBalance+Unit, Journal+JournalLine/JournalAttachment,
   JournalLine+Account).
4. **Perbaikan bug nyata yang ikut tertangkap**:
   - `InitialBalanceRepository` eager-load relasi 'unit' yang tidak
     ada di model.
   - `GeneralLedgerController::exportPdf()` variabel `$dateFrom`/
     `$dateTo` undefined (diulang query tanpa variabel).
   - `ExportService` bergantung pada `ExportRequestEntity` yang tidak
     pernah didefinisikan (phantom class) -> semua method menerima
     `array $data`.

Hasil: 54 error -> 21 (semua `relationExistence` false positive) ->
0 error. Vendor-side catatan: `package:discover` bisa korup saat
phpstan paralel; perbaiki dengan `php artisan package:discover`.

Langkah berikutnya (belum dieksekusi): trial `modules/Clinical` masih
43 error -- ekspansi modul berikutnya masuk iterasi terpisah.

### 29.4 Deptrac Boundary

src/deptrac.yaml diberlakukan untuk menjaga batas antar layer/module
(Controller -> Service -> Repository -> Model). Pelanggaran boundary
digagalkan di CI; refactor boundary wajib update deptrac.yaml terlebih
dahulu.

---

## 30. Audit Model Multi-org Scope -- HasBranchScope & HasCompanyScope (2026-10-07)

Status: AUDIT SELESAI -- baseline penuh 129 model di src/modules.

### 30.1 Metode Audit

- Pemindaian seluruh file `src/modules/**/Models/*.php` terhadap
  penggunaan trait `HasBranchScope` dan `HasCompanyScope`
  (App\Shared\Traits).
- Hasil diklasifikasi 3 kategori: pakai keduanya, hanya HasBranchScope,
  dan belum menerapkan keduanya.

### 30.2 Model Sudah Menerapkan Keduanya (9)

Location, Unit, Tax, PaymentTerm, Product, Service, ServicePackage,
Supplier, StockAdjustment.

### 30.3 Model Hanya HasBranchScope (15)

Complaint, Encounter, EncounterItem, Patient, Registration,
AccountBalance (Finance\Journal), RecurringJournal, AccountBalance
(Finance\Ledger), ClosingPeriod, Payable, Receivable, Employee,
ServicePackageItem, Sale, SaleItem.

### 30.4 Model Belum Menerapkan Keduanya (104)

**Clinical:** LabOrder, LabOrderItem, RadiologyOrder, Dispensing,
DispensingItem.

**Core:** Permission, Role, RolePermission, UserRole, User, UserBranch,
UserCompany, Branch, Company, File, FileAudit, FileVariant, KanbanCard.

**Finance:** Account, AccountMapping, Asset, Coa, CoaSetup, Journal,
JournalAttachment, JournalLine, OpeningBalanceHeader,
RecurringJournalLine, TransactionSequence, AuditLog, GeneralLedger,
SalesInvoice, SalesInvoiceAging, SalesInvoiceDetail, Transaction,
TransactionDetail.

**Operational\Inventory:** ItemBalanceRecord, ItemCustomField,
ItemDetail, ItemDiscount, ItemDiscountRule, ItemOpeningBalance,
ItemStok, Stock, StockAdjustmentItem, StockMovement, StockTransfer,
StockTransferItem.

**Operational\Manufacturing:** Assembly, AssemblyItem, Bom, BomCost,
BomDetail, Ingredient.

**Operational\Master:** ItemConversion, ItemSupplier, ItemUom,
LookupSet, LookupType, LookupValue, LookupValueDetail, MasterCustomer,
MasterProduct, Person, PersonAddress, PersonBalance, PersonContact,
PersonCustomField, PersonSaldo, PriceLevel, ProductAccount,
ProductDiscountRule, ProductImage, ProductLocation,
ProductOpeningBalance, ProductPrice, ProductPurchase, ProductQuotation,
ProductSale, ProductUnit, Shift, SupplierRecord, TaxSetting,
TempLookup, TempLookupDetail, UserWarehouse.

**Operational\Procurement:** GoodsReceipt, GoodsReceiptItem, Purchase,
PurchaseItem, PurchaseOrder, PurchaseOrderItem, PurchaseRequisition,
PurchaseRequisitionItem, PurchaseReturn, PurchaseReturnItem,
PurchaseReturnPayment.

**Operational\Sales:** DeliveryOrder, DeliveryOrderDetail, SalesOrder,
SalesOrderDetail.

**Clinical\Integration & Classification (global by design):** Icd10Code,
Icd9Code, BpjsSetting, SatusehatSetting.

### 30.5 Klasifikasi Prioritas Perbaikan

| Prioritas | Kategori | Contoh Model | Aksi |
|---|---|---|---|
| Tinggi | Data transaksi/bisnis per cabang | Journal, JournalLine, Transaction, TransactionDetail, SalesInvoice, Purchase, PurchaseOrder, SalesOrder, DeliveryOrder, Stock, StockMovement, Dispensing | Tambah HasCompanyScope + HasBranchScope bila kolom sudah ada |
| Menengah | Detail/child dari model ber-scope | JournalLine, TransactionDetail, SalesInvoiceDetail, StockTransferItem, PurchaseItem | Ikut scope parent atau tambah sendiri agar query standalone aman |
| Rendah | Master global per-company | Coa, Account, LookupSet, PriceLevel | Cukup HasCompanyScope, tanpa branch_uuid |
| Tidak perlu | Global sistem & entitas infrastruktur | Branch, Company, User, Role, Permission, Icd10Code, BpjsSetting, File, AuditLog | Tidak diberi multi-org scope |

### 30.6 Rencana Eksekusi

1. Audit skema tabel: pastikan kolom company_uuid/branch_uuid ada sebelum
   menambah trait (mengacu Section 26.2).
2. Tambah trait per prioritas (tinggi -> menengah -> rendah), satu modul
   per iterasi, disertai test isolasi antar company/branch.
3. Model kategori "tidak perlu" didokumentasikan sebagai pengecualian
   eksplisit agar tidak terdeteksi gap pada audit berikutnya.

### 30.7 Hasil Eksekusi (2026-10-08) -- 4 Iterasi TDD Selesai

> Hasil eksekusi TDD + koreksi audit dipindah ke `_progress.md` (entri
> 17.73, blok "30.7 Hasil Eksekusi").

### 30.8 Pengecualian Eksplisit -- Model Tanpa Multi-org Scope

Dokumentasi agar model berikut TIDAK dianggap gap pada audit berikutnya.

**A. Global by design (entitas infrastruktur/sistem):**

| Model | Alasan |
|---|---|
| Company, Branch | Akar hierarki org sendiri; scope menghapus datanya |
| User, UserRole, UserCompany, UserBranch | Identitas & pemetaan lintas org |
| Role, Permission, RolePermission | RBAC global sistem |
| Icd10Code, Icd9Code | Kode diagnosis internasional, dipakai semua klinik |
| BpjsSetting, SatusehatSetting | Konfigurasi integrasi nasional, bukan per org |
| File, FileAudit, FileVariant, KanbanCard, AuditLog | Infrastruktur aplikasi lintas org |

**B. Tabel tanpa kolom company_uuid/branch_uuid (scope mustahil tanpa
migrasi skema baru; terfilter transitif via parent untuk yang ber-relasi):**

| Model | Tabel | Status |
|---|---|---|
| TaxSetting | tax_settings | Tanpa kolom scope; konfigurasi pajak dipakai lintas org |
| ProductPurchase | product_purchases | Detail pembelian tanpa kolom scope; ikut Purchase |
| ProductSale | product_sales | Detail penjualan tanpa kolom scope; ikut Sale |
| ProductQuotation | product_quotation | Detail quotation tanpa kolom scope; ikut Quotation |
| BomCost, BomDetail | bom_costs, bom_details | Detail Bom tanpa kolom scope; ikut Bom |
| ItemSupplier | item_suppliers | Tanpa kolom scope (c0/b0 pada audit 26.2) |
| TempLookup, TempLookupDetail | temp_lovs, temp_lov_details | Tabel sementara tanpa kolom scope |

**C. Semantik khusus -- scope justru merusak:**

| Model | Alasan |
|---|---|
| LookupSet | `company_uuid` NULLABLE dengan semantik "NULL = lookup global"; CompanyScope akan menyembunyikan lookup global dari semua user. Detail konsumen (LookupType/LookupValue/LookupValueDetail) tetap di-scope company. |

**Catatan pembaruan 2026-10-12:** `ProductPrice` (`item_prices`) tidak
lagi masuk pengecualian karena tabel sudah punya `company_uuid` dan
`branch_uuid`. Trait multi-org dipasang pada iterasi lanjutan.

**D. Company-only sudah cukup (punya company_uuid, tanpa branch_uuid):**

LookupType, LookupValue, LookupValueDetail -- dipasang HasCompanyScope
saja pada iterasi 3.

**E. Transaksi ter-scope (untuk kelengkapan):** TransactionSequence
punya company_uuid tanpa branch (sequence nomor dokumen per company);
tidak masuk iterasi 1-3 karena tidak ber-relasi model transaksi yang
diuji. Model transaksi dengan pola sama (company-only) mengikuti aturan
30.5: cukup HasCompanyScope.

### 30.9 Regression

Verifikasi dilakukan dengan test terarah (bukan suite penuh, sesuai
arahan 2026-10-09): ketiga suite scope di
`tests/Feature/Infrastructure/` -- 120 test pass (237 asersi), tidak ada
kegagalan baru pada area yang disentuh perubahan. Baseline suite penuh
yang tercatat saat iterasi 1: 528 failed / 2085 passed, identik sebelum
dan sesudah pemasangan trait (kegagalan pre-existing; sampel
InteractsWithSandboxRuntimeTest gagal tanpa perubahan juga). Suite penuh
tidak dijalankan ulang di akhir iterasi 4 atas arahan user; verifikasi
berikutnya cukup test terarah per file/direktori.

### 30.10 Penyeragaman Primary Key Legacy id -> uuid (2026-10-09)

Empat tabel warisan aplikasi POS lama ber-PK `char('id', 36)` yang diisi
UUID oleh HasUuids direname menjadi `uuid` agar satu konvensi dengan
120+ tabel lain (aplikasi masih tahap pengembangan, migrasi create
diedit langsung tanpa migrasi rename):

- transactions, transaction_details, shifts (pos_legacy_tables.php)
- stocks (create_ingredients_table.php)

Perubahan ikutan:

- 4 model: `protected $primaryKey` 'id' -> 'uuid' (Transaction,
  TransactionDetail, Stock, Shift).
- 2 relasi sah: Transaction->shift dan TransactionDetail->transaction
  kini menunjuk 'uuid'.
- 5 relasi rusak diperbaiki: ProductSale->product/sale,
  ProductPurchase->product/purchase, ProductQuotation->product sebelumnya
  menunjuk 'id' padahal parent (items/purchases/sales) ber-PK uuid --
  relasinya tidak akan pernah match.
- 3 bug import laten diperbaiki: Shift/Sale/Purchase direferensikan
  pada relasi tanpa `use`, sehingga kelas resolves ke namespace model
  yang salah (fatal saat relasi dipanggil). Import ditambahkan di
  Transaction, ProductSale, ProductPurchase.

Test: `tests/Feature/Infrastructure/LegacyPrimaryKeyUuidTest.php`
(11 test, 29 asersi) -- kolom uuid ada / id hilang di 4 tabel, PK model,
relasi Transaction->shift dan TransactionDetail->transaction match via
uuid, dan 5 relasi product-detail terverifikasi menunjuk kolom uuid.

CATATAN: scan lanjutan menemukan pola import relasi unresolved yang
sistemik (Branch/Company/MasterProduct dll. tanpa `use` di 40+ model) --
tercatat sebagai pekerjaan terpisah, di luar scope 30.10.

### 30.11 Perbaikan Import Kelas Relasi Unresolved (2026-10-09)

Tindak lanjut CATATAN 30.10 -- dieksekusi di sesi terpisah, diverifikasi
mandiri (re-scan + run test) oleh sesi utama.

Hasil scan final: 151 referensi unresolved di 59 file Models (estimasi
awal ~160/40 file -- over-count karena scan pertama belum memfilter
import yang sudah ada).

Perbaikan:

- 59 file Models ditambah statement `use` (~150 import baru) setelah
  deklarasi namespace.
- 3 file dengan bug kategori kedua (import ada tapi menunjuk namespace
  salah): Patient & Registration (Encounter: Registration\Models ->
  MedicalRecord\Models), Product (StockMovement: Master\Models ->
  Inventory\Models).
- Konvensi Product vs MasterProduct (keduanya menunjuk tabel items):
  item_uuid -> MasterProduct, product_uuid -> Product -- hanya import
  yang ditambahkan, FK tidak disentuh.
- Di luar Models ditemukan 17 import salah-namespace lain
  (Repositories/Services/Controllers) -- TIDAK disentuh, tercatat
  sebagai pekerjaan terpisah (sebagian menunjuk kelas yang memang belum
  ada, mis. FileRequestEntity).

Verifikasi (diulang mandiri oleh sesi utama, hasil identik):

- Test baru `ModelRelationClassResolutionTest` (reflection memanggil
  semua method relasi di 19 direktori Models, assert mengembalikan
  Relation tanpa Class not found): 397 passed (1191 asersi).
- 4 suite scope/PK terarah: 131 passed (266 asersi), 0 failure.
- Re-scan final: 0 unresolved, 0 import salah-namespace di Models.

### 30.12 Perbaikan Import Salah-Namespace Non-Models + Kelas Hilang (2026-10-09)

Kelanjutan CATATAN 30.11: 17 import salah-namespace di layer non-Models
(Repositories/Services/Controllers) dieksekusi di sesi ini, termasuk kelas
yang dirujuk tapi belum ada.

Audit (kategori):

- (A) Import menunjuk namespace lama padahal kelas sudah pindah modul
  (15 referensi di 10 file) -- contoh: AnalyticsRepository menunjuk
  Core\Identity\Models dan Operational\Sales\Models, SettingsRepository
  menunjuk Core\Models, ProcurementRepository menunjuk Master\Models lama,
  UserService menunjuk App\Exports/Imports legacy.
- (B) Kelas dirujuk tapi belum ada (6 kelas) -- dibuat mengikuti pola
  yang ada:
  - `Core/Platform/Entities/FileRequestEntity.php`: dipulihkan dari
    riwayat git (f6ba103c9^, dulu app/Entities/Infrastructure), 28
    properti constructor promotion + fromArray/toArray; kontrak
    FileService/ImageService tetap.
  - `Operational/Master/Entities/CustomerSupplierFilterEntity.php`:
    filter search untuk CustomerSupplierService/Repository (kontrak
    aktual: hanya properti `search`).
  - `Finance/Journal/Exports/`: AssetExport, AssetTemplateExport,
    CoaExport, CoaTemplateExport (Maatwebsite FromCollection +
    WithHeadings + WithMapping + Exportable).
- (C) Import `App\...` menuju app/ legacy yang ternyata sah
  (ExceptionLogger, ApiDataResource, UserResource, ResolvesKlinikContext,
  UnauthorizedException): diverifikasi ada di app/ dan ter-cover composer
  autoload (`App\` -> `app/`) -- TIDAK diubah.

Perbaikan: 18 import di 11 file diroute ulang via script (Analytics×2,
SettingsRepository, ApiUnitController, Procurement, Branch×2, FileService,
ImageService, UserService×3, AccountingService×4).

Perbaikan tambahan: properti `readonly` pada CoaExport/AssetExport yang
di-reassign di constructor (fatal error saat array dikonversi Collection)
diganti `private` biasa -- tertangkap saat smoke test.

Verifikasi:

- Scan final seluruh layer non-Models (exclude Models/database/Tests):
  0 import `App\` menuju file yang tidak ada (case-sensitive; scan awal
  1077/33 adalah false positive dari bug path script scan, bukan kode).
- php -l: 17 file yang diubah/dibuat -- semua OK.
- Smoke test autoload + perilaku 6 kelas baru: semua OK (CoaExport map
  array->Collection, mapping kolom, template headings).
- Test terarah: ModelRelationClassResolutionTest 397 passed (1191
  asersi); FileServiceTest+ImageServiceTest+AccountingServiceTest 21
  passed; UserServiceTest 2 passed; EmployeeServiceTest 4 passed.

#### 30.12.1 Test Perilaku untuk Kelas Baru (2026-10-09)

Setelah 30.12, test yang ada ternyata reflection stub (hanya cek
method isPublic) untuk kelas-kelas terkait. Dibuat 4 file test
perilaku nyata (37 test / 129 asersi, semua hijau):

- `tests/Unit/Entities/Core/FileRequestEntityTest.php` (5): fromArray,
  default value, alias legacy (uuid/user_uuid/user_ip), cast boolean
  string, roundtrip toArray.
- `tests/Unit/Entities/MasterData/CustomerSupplierFilterEntityTest.php`
  (3): search hold/default-null/roundtrip.
- `tests/Unit/Exports/FinanceJournalExportTest.php` (11): CoaExport &
  AssetExport (array->Collection, mapping label Indonesia, null-safe,
  cast numerik), CoaTemplateExport & AssetTemplateExport (headings +
  baris contoh/kosong).
- `tests/Feature/Infrastructure/FileServiceUploadFlowTest.php` (12):
  integrasi nyata FileService+FileRepository (SQLite :memory:) --
  upload membuat file+variant+audit dan simpan ke storage; duplikat
  hash mengembalikan file existing (kontrak is_duplicate); reject
  parent hilang/extension berbahaya/oversize; soft delete+restore;
  verify integrity (missing & repair hash); ImageService reject
  non-image; storage usage per branch.
- `tests/Feature/Repositories/Operational/Master/CustomerSupplierFilterFlowTest.php`
  (6): filter search customer/supplier end-to-end service+repo,
  mapping bentuk baris, payload halaman, validasi search >100 char.

BUG PRODUCTION tertangkap test ini:

- `FileService::buildVariants(File $file)` men-type-hint `File`
  TANPA statement use -- resolve ke `Services\File` (tidak ada),
  fatal TypeError saat upload dieksekusi sungguhan. Sudah diperbaiki
  dengan `use App\Modules\Core\Platform\Models\File;`. Ini pola bug
  class-resolution sama dengan Section 30.11, tersembunyi karena
  test lama tidak pernah memanggil method-nya.

Verifikasi regression: 22 test stub lama tetap hijau;
ModelRelationClassResolutionTest 397 passed (1191 asersi).

### 30.13 TransactionSequence Multi-org Scope (2026-10-09)

TDD (test merah -> hijau): penomoran dokumen otomatis (invoice,
jurnal, dst) per cabang tidak boleh berbagi counter antar
company/branch.

Perubahan:

- Model `Finance/Journal/Models/TransactionSequence.php`: tambah
  trait `HasCompanyScope` + `HasBranchScope` (skema tabel sudah punya
  `branch_uuid` NOT NULL + `company_uuid` + unique
  `(branch_uuid, type_code, period)`, jadi hanya model yang telat).
- Test baru `tests/Feature/Infrastructure/TransactionSequenceScopeTest.php`:
  dua company/branch berbeda tidak saling melihat sequence; create
  mengisi scope dari konteks user login.
- Test pendamping `UserFactoryLegacyScopeTest.php` (factory User
  tidak lagi menulis kolom legacy yang sudah di-drop).

Verifikasi: 9 passed (32 asersi) untuk kedua file test.

## 31. Evaluasi Alternatif Runtime Bun + Kebutuhan Socket/QZ Printer (2026-10-12)

### 31.1 Konteks & Pertanyaan

Diskusi 2026-10-12 (app masih tahap pengembangan, belum ada user):
apakah lebih baik pindah backend ke Bun, dengan dua kebutuhan yang
diusulkan sebagai alasan: (1) kebutuhan socket, (2) kebutuhan QZ
Tray printer yang "perlu pakai socket".

### 31.2 Analisa

#### Kebutuhan socket -- sudah terjawab di stack existing

- Laravel Reverb terinstall dan 7/7 task selesai (17.40-17.47):
  install, channel authorization, 6 event broadcast, SatusehatSyncJob
  async, E2E Echo test, k6 load test 100 koneksi WS.
- Reverb event-loop non-blocking; 200-1000 koneksi WS konkuren =
  satu server kecil. 200 user aktif ~= 200 koneksi WS (mayoritas
  idle, puluhan KB/koneksi) -- bukan beban berat.
- Threshold ganti teknologi sudah tertulis Section 25: >1000 koneksi
  WS atau latency <50ms requirement.

#### Kebutuhan QZ Tray -- miskonsepsi "butuh socket di backend"

Arsitektur QZ Tray (qz.io/docs/print-server, /docs/Signing,
github.com/qzind/tray/wiki/Architecture):

- WebSocket-nya adalah BROWSER -> QZ Tray (aplikasi Java terinstall
  di PC kasir) via wss://localhost:8181 (secure) atau ws://localhost:8182
  (insecure). Bukan server -> printer, bukan tanggung jawab backend.
- Peran backend hanya SATU: endpoint HTTP signing RSA SHA512 agar
  print silent tanpa dialog popup (privileged action). Contoh resmi
  QZ justru PHP: `openssl_sign($req, $signature, $privateKey, "sha512")`
  -- 8 baris.
- Frontend: qz.security.setCertificatePromise() (fetch certificate)
  + setSignaturePromise() (fetch signature dari backend) -- pola
  fetch/AJAX biasa.

#### Evaluasi Bun sebagai pengganti Laravel

Biaya rewrite (yang hilang jika pindah):

- 129 model, 78 repository, pola Controller -> Service -> Repository
  -> Entity di 5 modul.
- Tenant isolation multi-org: pivot company/branch, middleware
  ResolveCompanyContext, scope trait di ~120 model (Section 30) --
  kerjaan keamanan paling berisiko untuk di-port ulang + verifikasi.
- Domain akuntansi (jurnal, ledger, COA, closing, trial balance).
- 2000+ test hijau, PHPStan 0 error, deptrac, module boundary check,
  CI -- seluruh QA infrastructure PHP-specific.
- Eloquent scope/observer/trait + migrasi MySQL tidak punya padanan
  1:1 di Prisma/Drizzle (ORM Bun) -- porting = tulis ulang.

Benefit Bun yang relevan: startup cepat, single binary, tooling
menyatu. Tidak relevan untuk long-running ERP server; N+1 query,
laporan berat, dan queue blocking terjadi di Bun juga
(language-independent).

Benefit yang justru dirisikokan: identitas bahasa tim tetap Laravel
(diputuskan 17.33); standar klinikdev/klinikbe PHP-specific.

### 31.3 Temuan

- Tidak ada kebutuhan teknis yang mewajibkan Bun/Node.js: socket
  sudah terjawab Reverb, QZ printer tidak butuh socket backend.
- Estimasi beban nyata 200 user konkuren: 20-60 req/s HTTP (ERP
  klik-baca-typing, bukan streaming) + 200 koneksi WS idle -- beban
  kecil untuk PHP-FPM + OPcache + Redis.
- Risiko "lelet saat 200+ user" bukan soal bahasa: penyebab riil
  N+1, laporan sync tanpa queue, missing index -- semuanya sudah
  termitigasi (compound index 17.39, Redis config, Pulse monitoring)
  atau terkontrol via standar coding.
- Escape hatch tanpa rewrite jika suatu saat jenuh: Laravel Octane
  (FrankenPHP/Swoole) -- aplikasi long-lived, throughput naik 10-30x,
  nol perubahan kode aplikasi.

### 31.4 Solusi & Keputusan

- TETAP Laravel 12 (PHP 8.3). Bun ditolak -- biaya rewrite tidak
  sepadan, tidak ada kebutuhan teknis yang terjawab oleh Bun.
- Integrasi QZ Tray dibatalkan -- todo dihapus per keputusan user
  (2026-10-12); analisa arsitektur di 31.2 tetap sebagai referensi.
- Jalur mitigasi performansi tetap Section 24 (Fase 1 -> Octane ->
  Fase 2 -> Fase 3), trigger berbasis metrik P95/Pulse.

### 31.5 Rencana Integrasi QZ Tray (dibatalkan)

> Integrasi QZ Tray dibatalkan -- todo dihapus per keputusan user
> (2026-10-12). Analisa arsitektur tetap tersimpan di 31.2 dan
> _progress.md 17.71 sebagai referensi bila suatu saat dibutuhkan lagi.

### 31.6 Testing

- Sudah terverifikasi (basis keputusan): k6 WS 100 koneksi (17.47);
  compound index + config Redis (17.39); Pulse/Telescope (17.43).
- Performance gate k6 HTTP SELESAI DIBUAT (17.71.1):
  loadtest/http-load-test.js -- profil full (ramp 50 -> 200 hold 2m
  -> 500 hold 2m), skenario login JWT, listing produk + paginasi,
  jurnal, cash-flow; threshold p(95)<200ms p(99)<500ms fail<1%.
  Budget + langkah eksekusi + tabel baseline di
  ../deployment-checklist.md (Performance Gate).

### 31.7 Checklist

> Dipindah ke `_progress.md` (entri 17.73, blok "31.7 Checklist").

### 31.8 Kesimpulan

Tetap Laravel. Kebutuhan socket terjawab oleh Reverb yang sudah
jalan; QZ Tray tidak butuh socket backend -- hanya endpoint signing
PHP 8 baris. Pindah ke Bun menambah bulan-bulan rewrite + re-audit
tenant isolation dan akuntansi tanpa menyelesaikan satu pun
kebutuhan. Risiko performansi dikelola via metrik terukur (Section
24) dan escape hatch Octane tanpa rewrite -- bukan ganti bahasa.

## 32. Monitoring Performa Aplikasi -- Laravel Pulse (2026-10-12)

### 32.1 Kebutuhan

User meminta kemampuan monitoring untuk menjawab: kapan dan di mana
server dipakai berat, query mana dan method mana yang makan resource,
user siapa yang jadi penyebab, dalam bentuk tabel dengan detail +
filter range tanggal + deteksi lonjakan.

### 32.2 Solusi yang Dipilih

**Laravel Pulse** (sudah ada di composer: `laravel/pulse ^1.8`,
ternyata aktif dan merekam data sejak lama -- 30k+ entri di DB dev).

Dipilih karena:
- Zero app-code change -- recorder diaktifkan via middleware global.
- Data persis sesuai kebutuhan: `user_request` (atribusi user per
  request), `slow_request`, `slow_query`, `slow_job`, `exception`,
  `cache_hit/miss`, `queue`.
- Dashboard siap pakai di `/pulse`: card "Application Usage" (top
  user), "Slow Requests", "Slow Queries", "Exceptions", "Servers"
  (CPU/memory/disk via `Servers` recorder), semua dengan filter
  waktu (1-5 menit ... 1-2 minggu).
- Tabel `pulse_entries`/`pulse_aggregates` bisa di-query langsung
  untuk analisa lonjakan per range tanggal (agregat per menit/jam).

Alternatif yang dipertimbangkan:
- **Telescope** (sudah ter-install, dev-only): detail per-request
  terlalu berat utk production -- dilarang di prod by design.
- **Sentry/APM eksternal**: perlu service/cloud, biaya bulanan;
  Pulse cukup untuk kebutuhan self-hosted ini.
- **MySQL slow query log**: hanya query, tanpa atribusi user/URL.

Kombinasi akhir: Pulse (runtime, prod-ready) + Telescope (dev
debugging) + k6 (gate pre-launch, Section 31) + MySQL slow log
(optional, deep-dive query).

### 32.3 Temuan Setup (file/baris)

- Dashboard `/pulse` SEBELUMNYA TERBUKA PUBLIK: gate default vendor
  `viewPulse` = semua orang saat APP_ENV=local
  (vendor/laravel/pulse/src/PulseServiceProvider.php:100).
- Atribusi user SUDAH BEKERJA tanpa tabel `pulse_users`: recorder
  `UserRequests` menyimpan `key_hash = md5(uuid user)` di
  `pulse_entries.key_hash`; resolver default `Laravel\Pulse\Users`
  (vendor/laravel/pulse/src/Users.php:35) memakai
  `getAuthIdentifier()` -> PK uuid User. Verifikasi: entries 5
  menit terakhir ter-atribusi ke admin@klinik.local.
- `pulse_entries.type` tercatat: cache_miss=13804, user_request=6902,
  slow_request=4708, slow_user_request=4708, exception=2 -- termasuk
  lonjakan dari k6 run (Section 31/17.71.1).
- Threshold default terlalu longgar utk budget P95<200ms:
  slow_query=1000ms, slow_request=1000ms.

### 32.4 Perubahan

1. **Proteksi dashboard** (app/Providers/AppServiceProvider.php,
   setelah Gate::before super-admin):
   `Gate::define('viewPulse', fn($user) => $user->can('system.monitor.view'))`
   -- anonim sekarang 403; aman saat APP_ENV berubah ke production.
2. **Permission baru** `system.monitor.view`
   (modules/Core/Authorization/database/seeders/V1/RolePermissionSeeder.php,
   group "Monitoring & Observability") -- otomatis masuk $allPerms
   (admin/owner/superadmin). Re-seed dilakukan, relasi role terverifikasi
   (admin = 99 permission).
3. **Threshold sesuai budget Section 24.1** (.env):
   `PULSE_SLOW_QUERIES_THRESHOLD=200`,
   `PULSE_SLOW_REQUESTS_THRESHOLD=300`.
   Verifikasi: request products 429-459ms -> 9 slow_request terekam;
   slow_query 0 = tidak ada query individual >200ms (latency dari
   akumulasi banyak query kecil + overhead, bukan satu query jahat).

### 32.5 Cara Pakai Dashboard

1. Login web sebagai admin/owner/superadmin -> buka `/pulse`.
2. Filter waktu di kanan atas (1-5 menit s/d 1-2 minggu).
3. **"Application Usage"**: user terberat (jumlah request). Klik
   user -> detail request-nya.
4. **"Slow Requests"**: endpoint terberat + durasinya -- ini
   jawaban "method mana yang makan resource".
5. **"Slow Queries"**: SQL persis + durasi + count -- jawaban
   "query mana yang berat". Klik baris -> SQL lengkap.
6. **"Exceptions"**: error terjadi kapan saja.
7. **"Servers"**: CPU/RAM/disk server per waktu (recorder Servers
   perlu cron `pulse:check` di production).
8. **Analisa lonjakan**: grafik card menampilkan tren per interval;
   lonjakan terlihat sebagai puncak. Untuk analisa mendalam per
   range tanggal, query langsung `pulse_aggregates` (kolom
   period/aggregate/type) atau buat card Pulse custom.

### 32.6 Catatan Produksi

- Jalankan `php artisan pulse:check` via cron/scheduler (recorder
  Servers + trim data lama).
- Ingest default pakai DB yang sama; jika beban tinggi, pindahkan
  ingest ke Redis (`PULSE_INGEST_DRIVER=redis`) -- sudah tersedia
  (predis ada).
- Trim otomatis menjaga tabel tidak membesar tanpa batas
  (config/pulse.php `storage.trim`).
- k6 load test (Section 31.6) terlihat di Pulse sebagai lonjakan
  -- gunakan untuk korelasi beban vs latency.

### 32.7 Testing

- Gate: `GET /pulse` tanpa login = 403 (terverifikasi).
- Permission: admin@klinik.local `can('viewPulse')` = true
  (terverifikasi via tinker).
- Recorder: request products berulang -> slow_request bertambah
  9 entri (threshold 300ms), atribusi user benar (terverifikasi).
- Regression permission: re-seed RolePermissionSeeder -> admin
  tetap 99 permission, relasi utuh (terverifikasi).

### 32.8 Checklist

> Dipindah ke `_progress.md` (entri 17.73, blok "32.8 Checklist").

### 32.9 Kesimpulan

Kebutuhan monitoring terjawab oleh Laravel Pulse yang sudah ada
di dalam stack -- tidak ada paket baru, tidak ada biaya. Data
atribusi user + slow query + slow endpoint + exception dengan
filter waktu sudah jalan; pekerjaan tinggal proteksi (selesai),
threshold (selesai), dan wiring production (cron + ingest driver).
