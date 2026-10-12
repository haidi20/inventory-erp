# Freeze Mapping URL Endpoint Lama vs Final

Status: disetujui per modul.
Tanggal freeze: 2026-09-07.

Dokumen ini menjadi acuan agar endpoint lama/NineSoft/FE legacy tidak berubah liar saat implementasi API final. Prinsipnya: satu modul punya satu tabel mapping, endpoint final memakai prefix backend ` /api/v1 `, dan endpoint lama hanya menjadi referensi migrasi.

## Aturan Freeze

- Endpoint final dibekukan per modul setelah tabel modul disetujui.
- FE harus mengarah ke endpoint final, bukan endpoint lama.
- Endpoint lama tidak dihapus dari catatan; statusnya `legacy/reference`.
- Perubahan setelah freeze wajib update kolom `Status` dan `Catatan`.
- Format resource final memakai kebab-case plural, kecuali endpoint aksi seperti `post`, `import`, `export`, dan `template`.

## Auth & Akses

| Modul | Endpoint Lama | Endpoint Final | Status | Catatan |
|---|---|---|---|---|
| Login | NineSoft/session login | `POST /api/v1/auth/login` | final | Token auth aplikasi |
| Logout | NineSoft/session logout | `POST /api/v1/auth/logout` | final | Invalidate sesi/token |
| Sidebar menu | `/site/master/menu` | `GET /api/v1/app-menu/sidebar` | final | Menu FE berbasis role |
| Users | `/site/master/user` | `GET/POST /api/v1/users`, `GET/PUT/DELETE /api/v1/users/:uuid` | final | Termasuk export/import/template |
| Roles | `/site/master/user` / role legacy | `GET/POST /api/v1/roles`, `GET/PUT/DELETE /api/v1/roles/:uuid` | final | Role CRUD |
| Permissions | `/site/master/menu` / permission legacy | `GET/POST /api/v1/permissions`, `GET/PUT/DELETE /api/v1/permissions/:uuid` | final | Permission CRUD |

## Master Data

| Modul | Endpoint Lama | Endpoint Final | Status | Catatan |
|---|---|---|---|---|
| Company | setup/company legacy | `GET/POST /api/v1/master/companies`, `GET/PUT/DELETE /api/v1/master/companies/:uuid` | final | Ada export/import/template |
| Branch | cabang/outlet legacy | `GET/POST /api/v1/master/branches`, `GET/PUT/DELETE /api/v1/master/branches/:uuid` | final | Ada preview-import/export/import/template |
| Patient | master pasien legacy | `GET/POST /api/v1/master/patients`, `GET/PUT/DELETE /api/v1/master/patients/:uuid` | final | Klinik |
| Person/Customer | `/site/master/customer`, `/site/master/alamat` | `GET/POST /api/v1/master/persons`, `GET/PUT/DELETE /api/v1/master/persons/:uuid` | final | Customer/person party |
| Supplier | `/site/master/supplier` | `GET/POST /api/v1/master/suppliers`, `GET/PUT/DELETE /api/v1/master/suppliers/:uuid` | final | Supplier master |
| Product master | `/site/master/item` | `GET/POST /api/v1/master/products`, `GET/PUT/DELETE /api/v1/master/products/:uuid` | final | Master item; ada export/import/template |
| Product simple | product legacy | `GET /api/v1/products`, `POST /api/v1/products`, `POST/DELETE /api/v1/products/:uuid` | perlu konsolidasi | Endpoint non-`/master`; perlu diputuskan dipertahankan atau alias |
| Ingredients | bahan/formula legacy | `GET/POST /api/v1/ingredients`, `GET/PUT/DELETE /api/v1/ingredients/:uuid` | final | Ada export/import/template |
| Formula/BOM | `/site/master/formula` | `GET/POST /api/v1/master/boms`, `GET/PUT/DELETE /api/v1/master/boms/:uuid` | final | Nama final BOM/formula perlu konsisten di FE |
| Unit/Satuan | `/site/master/satuan` | `GET/POST /api/v1/master/units`, `GET/PUT/DELETE /api/v1/master/units/:uuid` | final | Dari route/test unit |
| Warehouse/Gudang | `/site/master/gudang` | `GET/POST /api/v1/master/warehouses`, `GET/PUT/DELETE /api/v1/master/warehouses/:uuid` | final | Ada juga `GET /api/v1/warehouses` untuk active lookup |
| Payment Term | `/site/master/termin` | `GET/POST /api/v1/master/payment-terms`, `GET/PUT/DELETE /api/v1/master/payment-terms/:uuid` | final | Termin pembayaran |
| Tax/Pajak | `/site/master/pajak` | `GET/POST /api/v1/master/taxes`, `GET/PUT/DELETE /api/v1/master/taxes/:uuid` | final | Pajak master |
| Employee/Karyawan | `/site/master/karyawan` | `GET/POST /api/v1/master/employees`, `GET/PUT/DELETE /api/v1/master/employees/:uuid` | final | Karyawan |
| Services/Treatment | master layanan legacy | `GET/POST /api/v1/master/services`, `PUT/DELETE /api/v1/master/services/:uuid` | final | Layanan klinik |
| Packages | paket layanan legacy | `GET/POST /api/v1/master/packages`, `PUT/DELETE /api/v1/master/packages/:uuid` | final | Paket treatment |
| Item Conversion | `/site/master/konversi` | `GET/POST /api/v1/master/item-conversions`, `DELETE /api/v1/master/item-conversions/:uuid` | final | Plus `GET /api/v1/master/item-uoms` |
| Item Price | `/site/master/harga` | `GET/POST /api/v1/master/item-prices`, `GET/PUT/DELETE /api/v1/master/item-prices/:uuid` | final | Harga item |
| LOV | master lookup legacy | `GET/POST /api/v1/lov/sets`, `GET/PUT/DELETE /api/v1/lov/sets/:uuid`, `GET/POST /api/v1/lov/values`, `GET/PUT/DELETE /api/v1/lov/values/:uuid` | final | Lookup ERP |

## Saldo Awal & Akuntansi

| Modul | Endpoint Lama | Endpoint Final | Status | Catatan |
|---|---|---|---|---|
| COA | `/site/master/account`, `/site/master/coa` | `GET/POST /api/v1/accounting/accounts`, `PUT/DELETE /api/v1/accounting/accounts/:uuid` | final | Ada template/export/import |
| Mapping Akun | setting COA legacy | `GET /api/v1/accounting/mappings`, `PUT /api/v1/accounting/mappings` | final | Wajib lengkap sebelum posting transaksi |
| Tax Setting | setting pajak legacy | `GET /api/v1/accounting/taxes`, `PUT /api/v1/accounting/taxes` | final | Setting akuntansi pajak |
| Opening Balance Accounting | `/site/master/saldocoa` | `GET /api/v1/accounting/opening-balances`, `PUT /api/v1/accounting/opening-balances` | final | Saldo awal COA |
| Opening Balance Import/Post | `/site/master/saldo` | `GET /api/v1/master/opening-balances`, `POST /api/v1/master/opening-balances/import`, `POST /api/v1/master/opening-balances/:uuid/post` | final | Import/post saldo awal |
| Initial Item Balance | saldo item legacy | `GET/POST /api/v1/master/initial-balances/items`, `DELETE /api/v1/master/initial-balances/items/:uuid` | final | Saldo awal barang |
| Initial Person Balance | saldo customer/supplier legacy | `GET /api/v1/master/initial-balances/persons`, `GET /api/v1/master/initial-balances/person-details` | final | Saldo piutang/hutang |
| Closing Period | tutup buku legacy | `GET /api/v1/accounting/closing-periods`, `POST /api/v1/accounting/closing-periods/close` | final | Freeze periode |
| Income Statement | `/site/report/analysis/LbRgGl/`, `/site/report/analysis/LbRgNt` | `GET /api/v1/accounting/reports/income-statement` | final | Laba rugi |
| Balance Sheet | `/site/report/analysis/LprnNrcStndrd/` | `GET /api/v1/accounting/reports/balance-sheet` | final | Neraca |
| Cash Flow | report cashflow legacy | `GET /api/v1/accounting/reports/cash-flow` | final | Arus kas |
| Assets | `/site/master/asset` | `GET/POST /api/v1/accounting/assets`, `GET/PUT/DELETE /api/v1/accounting/assets/:uuid` | final | Ada template/export/import |
| COA Setup | setup COA legacy | `PUT /api/v1/accounting/coa-setups` | final | Setting struktur COA |

## Pembelian

| Modul | Endpoint Lama | Endpoint Final | Status | Catatan |
|---|---|---|---|---|
| Purchase Order | pembelian order legacy | `GET/POST /api/v1/purchase-orders`, `GET/PUT/DELETE /api/v1/purchase-orders/:uuid` | final | Ada export/template/import |
| Purchase Invoice | faktur beli legacy | `GET/POST /api/v1/purchases`, `GET/PUT/DELETE /api/v1/purchases/:uuid` | final | Ada export/template/import |
| Purchase Return | retur beli legacy | `GET/POST /api/v1/purchase-returns`, `GET/PUT/DELETE /api/v1/purchase-returns/:uuid` | final | Ada export/template/import |

## Penjualan

| Modul | Endpoint Lama | Endpoint Final | Status | Catatan |
|---|---|---|---|---|
| Sales Order | sales order legacy | `GET/POST /api/v1/sales-orders`, `GET/PUT/DELETE /api/v1/sales-orders/:uuid` | final | Ada export/template/import |
| Delivery Order | delivery order legacy | `GET/POST /api/v1/delivery-orders`, `GET/PUT/DELETE /api/v1/delivery-orders/:uuid` | final | Ada export/template/import |
| Sales Invoice | `/site/sales/report` / faktur jual legacy | `GET/POST /api/v1/sales/invoices`, `GET/PUT/DELETE /api/v1/sales/invoices/:uuid` | final | Ada export/template/import |
| POS/Transaction | transaksi kasir legacy | `GET /api/v1/transactions`, `POST /api/v1/transaction` | perlu konsolidasi | Singular `transaction` untuk create; bisa dipertahankan sementara sebagai legacy API internal |

## Inventory

| Modul | Endpoint Lama | Endpoint Final | Status | Catatan |
|---|---|---|---|---|
| Stock Balances | nilai/persediaan legacy | `GET /api/v1/inventory/stock-balances`, `GET /api/v1/inventory/stock-balances/export` | final | Saldo stok |
| Stock Adjustments | adjustment stok legacy | `GET/POST /api/v1/inventory/stock-adjustments`, `PUT/DELETE /api/v1/inventory/stock-adjustments/:uuid`, `POST /api/v1/inventory/stock-adjustments/:uuid/post` | final | Ada export/template/import |
| Stock Transfer | mutasi stok legacy | `GET/POST /api/v1/inventory/stock-transfers`, `GET/PUT/DELETE /api/v1/inventory/stock-transfers/:uuid`, `POST /api/v1/inventory/stock-transfers/:uuid/post` | final | Ada export/template/import |
| Assembly | rakit/BOM produksi legacy | `GET/POST /api/v1/inventory/assemblies`, `GET/PUT/DELETE /api/v1/inventory/assemblies/:uuid`, `POST /api/v1/inventory/assemblies/:uuid/post` | final | Ada export/template/import |

## Integrasi & Settings

| Modul | Endpoint Lama | Endpoint Final | Status | Catatan |
|---|---|---|---|---|
| Settings | setup legacy | `GET /api/v1/settings` | final | Root settings |
| Database Backup | setup database legacy | `GET /api/v1/settings/database`, `POST /api/v1/settings/database/export`, `GET /api/v1/settings/database/download`, `GET /api/v1/settings/database/history`, `POST /api/v1/settings/database/restore` | final | Backup/restore |
| SATUSEHAT Settings | setup SATUSEHAT legacy | `GET /api/v1/settings/satusehat`, `PUT /api/v1/settings/satusehat` | final | Konfigurasi integrasi |
| ICD10 Settings | setup ICD10 legacy | `GET/POST /api/v1/settings/icd10`, `DELETE /api/v1/settings/icd10/:uuid` | final | Master ICD10 lokal |
| SATUSEHAT Bridging | API eksternal SATUSEHAT legacy | `GET/POST /api/v1/satusehat/*` | final | Practitioner, patient, encounter, claim, invoice, dll |

## Endpoint Yang Perlu Keputusan Khusus

| Isu | Endpoint Saat Ini | Rekomendasi | Status |
|---|---|---|---|
| Product ganda | `/api/v1/products` dan `/api/v1/master/products` | Jadikan `/api/v1/master/products` sebagai final; `/api/v1/products` sebagai alias sementara jika masih dipakai FE | perlu keputusan |
| Transaction singular | `POST /api/v1/transaction` | Pertahankan sementara untuk kompatibilitas; target final ideal `POST /api/v1/transactions` jika FE siap migrasi | perlu keputusan |
| Warehouse ganda | `/api/v1/warehouses` dan `/api/v1/master/warehouses` | `/api/v1/warehouses` khusus lookup active; CRUD tetap `/api/v1/master/warehouses` | final bersyarat |
| Formula vs BOM | `/site/master/formula` ke `/api/v1/master/boms` | Pakai nama final BOM di API, label FE boleh Formula | final |
