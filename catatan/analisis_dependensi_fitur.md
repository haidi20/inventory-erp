# Analisis Fitur: Data Master Independen vs Dependen

Dokumen ini memetakan seluruh fitur/entitas data master dalam sistem, dikategorikan berdasarkan apakah data tersebut **berdiri sendiri (independen)** — tidak membutuhkan foreign key ke entitas lain — atau **bergantung (dependen)** pada data master lain.

---

## Ringkasan Kategori

| Kategori | Keterangan |
|----------|------------|
| **Independen** | Data master murni, tidak punya FK ke entitas aplikasi lain. Bisa diisi pertama kali tanpa prasyarat. |
| **Semi-Independen** | Punya FK opsional (nullable) ke entitas lain, tapi tetap bisa dibuat tanpa mengisi FK tersebut. |
| **Dependen** | Punya FK wajib (not null) ke satu atau lebih entitas lain. Entitas lain harus ada terlebih dahulu. |

---

## 1. Data Master Independen (Tidak Bergantung pada Data Lain)

Entitas berikut **tidak memiliki foreign key** ke entitas aplikasi lain (selain company_id dan ranch_id yang merupakan scope multi-tenant sistem, bukan relasi bisnis).

### 1.1 Gudang (warehouses)
- **Model:** Warehouse
- **Tabel:** warehouses
- **FK ke entitas bisnis:** Tidak ada
- ranch_id dan company_id adalah scope multi-tenant, bukan FK bisnis
- ddress_id dan jenis_id adalah field legacy nullable, tidak dipakai aktif
- **Kesimpulan:** Gudang adalah data master murni independen. Bisa dibuat tanpa data lain.

### 1.2 Satuan (units)
- **Model:** Unit
- **Tabel:** units
- **FK ke entitas bisnis:** Tidak ada
- Hanya ranch_id dan company_id sebagai scope tenant
- **Kesimpulan:** Satuan adalah data master murni independen.

### 1.3 Cabang (ranches)
- **Model:** MasterBranch
- **Tabel:** ranches
- **FK ke entitas bisnis:** company_id (FK ke companies, nullable)
- Company diisi via preload tapi bukan FK wajib untuk operasional cabang
- **Kesimpulan:** Cabang bisa dibuat tanpa perusahaan aktif (company_id nullable).

### 1.4 Perusahaan (companies)
- **Model:** Company
- **Tabel:** companies
- **FK ke entitas bisnis:** Tidak ada
- **Kesimpulan:** Data paling atas hierarki, sepenuhnya independen.

### 1.5 Kode ICD-10 (icd10_codes)
- **Model:** Icd10Code
- **Tabel:** icd10_codes
- **FK ke entitas bisnis:** Tidak ada
- Data referensi medis standar internasional
- **Kesimpulan:** Independen sepenuhnya, bisa diimpor massal tanpa prasyarat.

### 1.6 Pajak / Tax (	axes)
- **Model:** Tax
- **Tabel:** 	axes
- **FK ke entitas bisnis:** Tidak ada FK aktif
- legacy_tax_type_id, legacy_sale_account_id, legacy_purchase_account_id adalah ID legacy integer (bukan UUID FK aktif)
- ranch_id nullable (scope tenant)
- **Kesimpulan:** Independen. Tidak membutuhkan COA aktif untuk dibuat.

### 1.7 Jangka Waktu Pembayaran / Payment Term (payment_terms)
- **Model:** PaymentTerm
- **Tabel:** payment_terms
- **FK ke entitas bisnis:** Tidak ada
- ranch_id nullable (scope tenant)
- **Kesimpulan:** Independen sepenuhnya.

### 1.8 Role & Permission (oles, permissions)
- **Model:** Role, Permission
- **Tabel:** oles, permissions, ole_permissions
- **FK ke entitas bisnis:** Tidak ada
- Relasi antar Role-Permission via pivot table many2many
- **Kesimpulan:** Independen. Tidak bergantung pada data master bisnis lain.

### 1.9 Akun COA / Chart of Accounts (ccounts)
- **Model:** Account
- **Tabel:** ccounts
- **FK ke entitas bisnis:** parent_id (self-referencing, nullable) ke ccounts
- company_id, ranch_id sebagai scope tenant
- **Kesimpulan:** Semi-independen. FK hanya ke dirinya sendiri (hierarki akun). Akun induk bisa dibuat dulu tanpa prasyarat lain.

---

## 2. Data Master Semi-Independen (FK Opsional/Nullable)

Entitas berikut punya FK ke entitas lain tapi **semua FK bersifat nullable** — artinya bisa dibuat tanpa mengisi FK tersebut.

### 2.1 Produk/Item (master_products)
- **Model:** MasterProduct
- **Tabel:** master_products
- **FK Nullable:**
  - ase_unit_id → units (opsional)
  - default_warehouse_id → warehouses (opsional)
  - product_id (category) → lookup/kategori (opsional)
  - item_category_lookup_id, item_group_lookup_id, item_type_lookup_id, rand_lookup_id, origin_lookup_id, grade_lookup_id → semua nullable lookup
- **Kesimpulan:** Bisa dibuat tanpa satuan atau gudang, tapi idealnya Satuan dan Gudang diisi dulu agar fungsionalitas penuh.

### 2.2 Pasien (patients)
- **Model:** PatientRecord
- **Tabel:** patients
- **FK ke entitas bisnis:** Tidak ada FK ke entitas lain (hanya scope tenant)
- **Kesimpulan:** Independen secara teknis. Data pasien berdiri sendiri.

### 2.3 Customer/Supplier (people)
- **Model:** Person
- **Tabel:** people
- **FK Nullable:**
  - payment_term_id → payment_terms (opsional)
  - warehouse_id → warehouses (opsional)
  - payable_account_id → ccounts (opsional)
  - eceivable_account_id → ccounts (opsional)
  - price_level_id, employee_id, currency_id, customer_type_id, salesman_id, company_type_id → semua nullable
- **Kesimpulan:** Semi-Independen. Bisa dibuat tanpa payment term atau COA, tapi fitur akuntansi tidak akan berjalan optimal.

### 2.4 Konversi Satuan (item_conversions)
- **Model:** ItemConversion
- **Tabel:** item_conversions
- **FK:**
  - item_id → master_products (nullable — konversi global tidak butuh item spesifik)
  - rom_unit_id → units (not null tapi Unit independen)
  - 	o_unit_id → units (not null tapi Unit independen)
- **Kesimpulan:** Bergantung pada Unit, tapi Unit sendiri independen. Bisa diisi setelah Unit selesai.

---

## 3. Data Master Dependen (FK Wajib ke Entitas Lain)

Entitas berikut **tidak bisa dibuat** sebelum entitas referensinya ada.

### 3.1 Gudang User (user_warehouses)
- **Model:** UserWarehouse
- **Tabel:** user_warehouses
- **FK Wajib:**
  - user_id → users
  - warehouse_id → warehouses
- **Urutan pengisian:** User → Gudang → User Warehouse

### 3.2 BOM / Formula Produksi (oms, om_details)
- **Model:** Bom, BomDetail
- **Tabel:** oms, om_details
- **FK Wajib:**
  - item_id → master_products (finished good)
  - unit_id → units
  - Detail: item_id → master_products (bahan baku)
- **Urutan pengisian:** Unit → Produk → BOM

### 3.3 Perakitan / Assembly (ssemblies, ssembly_items)
- **Model:** Assembly, AssemblyItem
- **Tabel:** ssemblies, ssembly_items
- **FK Wajib:**
  - item_id → master_products
  - unit_id → units
  - warehouse_id → warehouses
- **Urutan pengisian:** Unit → Produk → Gudang → Assembly

### 3.4 Purchase Order (purchase_orders, purchase_order_items)
- **Model:** PurchaseOrder, PurchaseOrderItem
- **Tabel:** purchase_orders, purchase_order_items
- **FK Wajib:**
  - supplier_id → people (is_supplier=true)
  - warehouse_id → warehouses
  - Item: item_id → master_products
  - Item: unit_id → units
- **Urutan pengisian:** Unit → Gudang → Produk → Supplier → PO

### 3.5 Pindah Antar Gudang / Stock Transfer (stock_transfers, stock_transfer_items)
- **Model:** StockTransfer, StockTransferItem
- **Tabel:** stock_transfers, stock_transfer_items
- **FK Wajib:**
  - source_warehouse_id → warehouses
  - destination_warehouse_id → warehouses
  - Item: product_id → master_products
  - Item: unit_id → units
- **Urutan pengisian:** Unit → Produk → Gudang (min. 2) → Stock Transfer

### 3.6 Transaksi POS (	ransactions, 	ransaction_details)
- **Model:** Transaction
- **Tabel:** 	ransactions
- **FK Wajib:**
  - warehouse_id → warehouses
  - user_id → users
  - order_type_id → order types
- **Urutan pengisian:** Gudang → User → Transaksi

### 3.7 Sales Order (sales_orders, sales_order_details)
- **Model:** SalesOrder
- **Tabel:** sales_orders
- **FK Wajib:**
  - person_id → people (is_customer=true)
  - warehouse_id → warehouses
  - payment_term_id → payment_terms
  - Detail: item_id → master_products
- **Urutan pengisian:** Payment Term → Gudang → Customer → Produk → Sales Order

### 3.8 Jurnal Akuntansi (journals, journal_lines)
- **Model:** Account dipakai via Journal (lihat accounting.go)
- **FK Wajib:**
  - Setiap baris jurnal → ccounts (COA)
  - Header jurnal → ranches
- **Urutan pengisian:** COA → Cabang → Jurnal

### 3.9 Saldo Awal (opening_balance_records)
- **Model:** OpeningBalanceHeader
- **Tabel:** opening_balance_records
- **FK Wajib (via posting):**
  - journal_id → jurnal (dibuat saat posting)
- **Urutan pengisian:** COA → Saldo Awal → Post → Jurnal otomatis dibuat

### 3.10 Stok (stocks)
- **Model:** Stock
- **Tabel:** stocks
- **FK Wajib:**
  - ingredient_id → item/produk
- **Urutan pengisian:** Produk → Stok

---

## 4. Urutan Pengisian Data Master yang Direkomendasikan

Berdasarkan pemetaan dependensi di atas, urutan pengisian data dari yang paling bebas ke paling terikat:

`
Level 1 (Independen Penuh):
  Perusahaan → Cabang → Role/Permission → ICD-10

Level 2 (Scope Tenant):
  Gudang → Satuan → Payment Term → Pajak

Level 3 (Semi-Dependen):
  COA/Akun (self-ref) → Customer/Supplier → Produk/Item

Level 4 (Konversi & Formula):
  Konversi Satuan → BOM/Formula

Level 5 (Transaksi Operasional):
  Purchase Order → Assembly → Stock Transfer → Sales Order → Transaksi POS

Level 6 (Akuntansi):
  Saldo Awal → Jurnal → Laporan Keuangan
`

---

## 5. Ringkasan Tabel

| Entitas | Tabel | Kategori | Bergantung Pada |
|---------|-------|----------|-----------------|
| Perusahaan | companies | Independen | - |
| Cabang | ranches | Independen | - |
| Role/Permission | oles, permissions | Independen | - |
| ICD-10 | icd10_codes | Independen | - |
| Gudang | warehouses | **Independen** | - |
| Satuan | units | **Independen** | - |
| Payment Term | payment_terms | Independen | - |
| Pajak | 	axes | Independen | - |
| COA/Akun | ccounts | Semi-Independen | ccounts (self) |
| Pasien | patients | Semi-Independen | - |
| Customer/Supplier | people | Semi-Independen | payment_terms, ccounts (opsional) |
| Produk/Item | master_products | Semi-Independen | units, warehouses (opsional) |
| Konversi Satuan | item_conversions | Semi-Dependen | units (wajib), master_products (opsional) |
| User Warehouse | user_warehouses | Dependen | users, warehouses |
| BOM | oms, om_details | Dependen | master_products, units |
| Assembly | ssemblies | Dependen | master_products, units, warehouses |
| Purchase Order | purchase_orders | Dependen | people, warehouses, master_products, units |
| Stock Transfer | stock_transfers | Dependen | warehouses (x2), master_products, units |
| Sales Order | sales_orders | Dependen | people, warehouses, payment_terms, master_products |
| Transaksi POS | 	ransactions | Dependen | warehouses, users |
| Stok | stocks | Dependen | master_products |
| Saldo Awal | opening_balance_records | Dependen | ccounts (via jurnal) |
| Jurnal | journals | Dependen | ccounts, ranches |

---

*Dibuat: 2026-09-09 | Sumber: analisis model Go (be/app/model/) dan struktur frontend feature*
