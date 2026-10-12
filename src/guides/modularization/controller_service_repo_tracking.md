# Tracking: Controller yang Belum Terapkan Service → Repository → Model

> Audit per 2026-09-19. Status: **10 controller NO LAYER** (perlu refaktor prioritas), 11 controller REPO only (perlu Service layer), 13 controller SVC only (perlu Repository layer), 4 controller Stub (return back, belum diimplementasi).

## Legenda

- **NO LAYER** — controller langsung pakai Model atau logic mentah, tidak ada Service/Repository. Prioritas refaktor tertinggi.
- **REPO only** — controller inject Repository langsung (bypass Service). Perlu tambah Service layer.
- **SVC only** — controller inject Service, tapi Service belum punya Repository (logic di Service). Perlu pisah Repository.
- **SVC+REPO** — sudah lengkap (Controller → Service → Repository → Model).
- **Stub** — controller return `back()` tanpa logic. Fitur belum diimplementasi.

## 1. NO LAYER — SELESAI DIREFAKTOR (7 controller)

Controller ini langsung manipulasi data tanpa Service/Repository. Wajib refaktor ke pattern Controller → Service → Repository → Model.

| # | Controller | Modul | CRUD | Catatan |
| --- | --- | --- | ---: | --- |
| 1 | `AccountController.php` | Finance\Journal | store, update | Logic akun langsung di controller |
| 2 | `EncounterController.php` | Clinical\MedicalRecord | store, update, destroy | 178 baris, logic encounter besar |
| 3 | `Icd10CodeController.php` | Clinical\Classification | store, update, destroy | CRUD ICD-10 langsung |
| 4 | `LabOrderController.php` | Clinical\Ancillary | store, update, destroy | CRUD lab order langsung |
| 5 | `PurchaseController.php` | Operational\Procurement | store | Logic pembelian langsung |
| 6 | `RadiologyOrderController.php` | Clinical\Ancillary | store, update, destroy | CRUD radiology langsung |
| 7 | `RegistrationController.php` | Clinical\Registration | store, update, destroy | 108 baris, logic pendaftaran |
| 8 | `SaleController.php` | Operational\Sales | store | 129 baris, logic POS besar |
| 9 | `ServiceController.php` | Operational\Sales | store, update, destroy | CRUD tindakan langsung |
| 10 | `ServicePackageController.php` | Operational\Sales | store, update, destroy | CRUD paket tindakan langsung |

## 2. REPO Only — SELESAI DIREFAKTOR — Perlu Tambah Service Layer (11 controller)

**Status: SEMUA SUDAH DIREFAKTOR.** 12 controller sudah dipindah dari inject Repository langsung ke inject Service. Adapter methods ditambahkan ke service yang sudah ada (EmployeeService, PatientService, dll) agar backward-compatible dengan method names yang controller pakai. AssemblyService & BomService sudah ada sebelumnya — controller tinggal di-wire. BomController pakai saveSimple() untuk flow store tanpa validasi items ketat.

| # | Controller | Modul | Catatan |
| --- | --- | --- | --- |
| 1 | `AssemblyController.php` | Operational\Manufacturing | Store/destroy assembly |
| 2 | `BomController.php` | Operational\Manufacturing | Store/destroy BOM |
| 3 | `EmployeeController.php` | Operational\Master | CRUD karyawan |
| 4 | `PatientController.php` | Clinical\Registration | CRUD pasien (sudah fix company_uuid) |
| 5 | `PaymentTermController.php` | Operational\Master | CRUD termin pembayaran |
| 6 | `PriceLevelController.php` | Operational\Master | CRUD level harga |
| 7 | `ProductController.php` | Operational\Master | CRUD produk |
| 8 | `PurchaseOrderController.php` | Operational\Procurement | Store/destroy PO |
| 9 | `SupplierController.php` | Operational\Master | CRUD supplier |
| 10 | `TaxController.php` | Operational\Master | CRUD pajak |
| 11 | `TreatmentController.php` + `TreatmentPackageController.php` | Operational\Master | CRUD tindakan/paket |

## 3. SVC Only — Perlu Tambah Repository Layer (13 controller)

Controller inject Service, tapi Service belum punya Repository terpisah (logic query di Service). Perlu pisah akses data ke Repository.

| # | Controller | Modul | Catatan |
| --- | --- | --- | --- |
| 1 | `ApiUnitController.php` | Operational\Master | Satuan |
| 2 | `ApiWarehouseController.php` | Operational\Master | Gudang/Location |
| 3 | `BranchController.php` | Core\Organization | Cabang |
| 4 | `CompanyController.php` | Core\Organization | Perusahaan |
| 5 | `ComplaintController.php` | Clinical\MedicalRecord | Keluhan |
| 6 | `ItemConversionController.php` | Operational\Master | Konversi satuan |
| 7 | `JournalController.php` | Finance\Journal | Jurnal |
| 8 | `LocationController.php` | Operational\Master | Lokasi |
| 9 | `RoleController.php` | Core\Authorization | Role (WebRoleController juga) |
| 10 | `UserController.php` | Core\Identity | User (WebUserController juga) |
| 11 | `WebComplaintController.php` | Clinical\MedicalRecord | Keluhan (web) |

## 4. Stub CRUD — Belum Diimplementasi (4 controller)

Controller `return back()` tanpa logic. Fitur belum jadi.

| # | Controller | Modul | Catatan |
| --- | --- | --- | --- |
| 1 | `BpjsSettingsController.php` | Clinical\Integration | Store = `return back()` |
| 2 | `LabController.php` | Clinical\MedicalRecord | Store/destroy = `return back()` |
| 3 | `RadiologyController.php` | Clinical\MedicalRecord | Store/destroy = `return back()` |
| 4 | `PendaftaranController.php` | Clinical\Registration | Store/destroy = `return back()` |

## 5. Sudah Lengkap (SVC+REPO)

| Controller | Modul |
| --- | --- |
| `SatusehatSettingsController.php` | Clinical\Integration |
| `SatusehatController.php` | Clinical\Integration |
| `CashFlowController.php` | Finance\Journal |

## Prioritas Refaktor

1. **NO LAYER (10 controller)** — refaktor ke Service + Repository + Model. Mulai dari yang paling besar logic-nya: `SaleController` (POS), `EncounterController`, `RegistrationController`, `PurchaseController`.
2. **REPO only (11 controller)** — tambah Service layer, pindah logic bisnis dari controller ke Service.
3. **SVC only (13 controller)** — tambah Repository layer, pindah query dari Service ke Repository.
4. **Stub (4 controller)** — implementasi CRUD sebelum refaktor.