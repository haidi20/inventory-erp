# Mapping Fitur: Sistem Klinik-Git vs Ninesoft Accounting Online

Dokumen ini membandingkan struktur fitur antara sistem **klinik-git** (sistem internal)
dengan **Ninesoft Accounting Online** (https://web.ninesoft.id) sebagai referensi.

---

## Modul Perbandingan

### 1. Master Data

| Fitur | Ninesoft (URL) | Klinik-Git (Tabel) | Status |
|-------|----------------|---------------------|--------|
| Daftar Nilai / Lookup | /site/master/nilai | lookups (via lookup model) | ✅ Ada |
| Barang dan Jasa | /site/master/barang | master_products | ✅ Ada |
| Formula / BOM | /site/master/formula | oms, om_details | ✅ Ada |
| Satuan | /site/master/satuan | units | ✅ Ada |
| Saldo Awal Item | /site/master/saldo-awal | item_balance_records | ✅ Ada |
| Konversi Item | /site/master/konversi | item_conversions | ✅ Ada |
| Gudang | /site/master/gudang | warehouses | ✅ Ada |

---

### 2. Stok / Inventory

| Fitur | Ninesoft | Klinik-Git | Status |
|-------|----------|------------|--------|
| Penerimaan Barang (dari PO) | ✅ | via purchase (penerimaan) | ✅ Ada |
| Pindah Antar Gudang (PAG) | ✅ | stock_transfers | ✅ Ada |
| Perakitan / Assembly | ✅ | ssemblies | ✅ Ada |
| Penyesuaian Stok | ✅ | stock_adjustments | ✅ Ada |
| Laporan Stok | ✅ | stocks, inventory_report | ✅ Ada |

---

### 3. Pembelian

| Fitur | Ninesoft | Klinik-Git | Status |
|-------|----------|------------|--------|
| Purchase Order (PO) | ✅ | purchase_orders | ✅ Ada |
| Penerimaan / Invoice Pembelian | ✅ | purchases | ✅ Ada |
| Retur Pembelian | ✅ | purchase_returns | ✅ Ada |
| Supplier | ✅ | people (is_supplier=true) | ✅ Ada |

---

### 4. Penjualan

| Fitur | Ninesoft | Klinik-Git | Status |
|-------|----------|------------|--------|
| Sales Order (SO) | ✅ | sales_orders | ✅ Ada |
| Delivery Order (DO) | ✅ | delivery_orders | ✅ Ada |
| Invoice Penjualan | ✅ | sales_invoices | ✅ Ada |
| Retur Penjualan | ✅ | via sales model | ⚠️ Partial |
| Customer | ✅ | people (is_customer=true) | ✅ Ada |
| Payment Term | ✅ | payment_terms | ✅ Ada |

---

### 5. Akuntansi

| Fitur | Ninesoft | Klinik-Git | Status |
|-------|----------|------------|--------|
| Chart of Accounts (COA) | ✅ | ccounts | ✅ Ada |
| Jurnal Umum | ✅ | journals, journal_lines | ✅ Ada |
| Saldo Awal Akun | ✅ | opening_balance_records | ✅ Ada |
| Buku Besar | ✅ | query dari journal_lines | ✅ Ada |
| Neraca Saldo | ✅ | query dari journal_lines | ✅ Ada |
| Laporan Laba Rugi | ✅ | inancial_reports | ✅ Ada |
| Neraca | ✅ | inancial_reports | ✅ Ada |
| Closing Period | ✅ | closing_periods | ✅ Ada |

---

### 6. Setting / Konfigurasi

| Fitur | Ninesoft | Klinik-Git | Status |
|-------|----------|------------|--------|
| Profil Perusahaan | ✅ | companies | ✅ Ada |
| Cabang | ✅ | ranches | ✅ Ada |
| User & Role | ✅ | users, oles, permissions | ✅ Ada |
| Pajak (Tax/PPN) | ✅ | 	axes | ✅ Ada |
| Satusehat Integration | ❌ | satusehat_settings | ✅ Ada (klinik-git lebih lengkap) |
| ICD-10 | ❌ | icd10_codes | ✅ Ada (klinik-git lebih lengkap) |
| Pasien | ❌ | patients | ✅ Ada (klinik-spesifik) |

---

## Perbedaan Utama: Ninesoft vs Klinik-Git

### Fitur Ninesoft yang Belum Ada di Klinik-Git
| Fitur | Catatan |
|-------|---------|
| Multi-currency | Ninesoft punya currency_id aktif; klinik-git punya field tapi belum aktif |
| Harga Bertingkat (Price Level) | price_level_id ada di model Person tapi belum ada tabel price_levels |
| Tipe Perusahaan / Customer Type | company_type_id, customer_type_id ada di Person tapi belum ada tabelnya |

### Fitur Klinik-Git yang Tidak Ada di Ninesoft
| Fitur | Catatan |
|-------|---------|
| Integrasi Satusehat (FHIR) | Khusus klinik, koneksi ke platform nasional |
| Data Pasien & Rekam Medis | Tidak ada di akuntansi umum Ninesoft |
| ICD-10 | Kode diagnosa medis standar internasional |
| Transaksi POS | Ninesoft fokus di ERP; klinik-git punya kasir/POS |
| Perakitan (Assembly) | Klinik-git lebih lengkap untuk manufaktur ringan |

---

## Catatan Khusus: master/nilai di Ninesoft

Halaman /site/master/nilai di Ninesoft adalah **Daftar Nilai / Lookup Master**.
Fungsinya sebagai tabel referensi konfigurasi dinamis (dropdown values).

Di klinik-git, equivalent-nya adalah:
- Model Lookup (lookup.go) — untuk nilai-nilai dropdown seperti kategori item, tipe item, brand, origin, grade
- Field-field seperti item_category_lookup_id, item_group_lookup_id, item_type_lookup_id di MasterProduct
- **Karakteristik:** Independen murni — tidak memiliki FK ke entitas bisnis manapun

---

*Dibuat: 2026-09-09 | Referensi: https://web.ninesoft.id (user: web@demo.com)*
