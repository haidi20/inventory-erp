# Analisis Priority Implementasi Fitur (Urutan Development)

Dokumen ini memetakan **urutan pengerjaan fitur** berdasarkan dependensi data master.
Fitur tanpa dependensi dikerjakan pertama, fitur dengan banyak dependensi dikerjakan terakhir.

---

## Prinsip

- Fitur independen = bisa di-develop dan di-test secara terisolasi
- Fitur dependen = harus menunggu fitur di level atasnya selesai (endpoint + seed data tersedia)
- Setiap level bisa dikerjakan paralel antar-fitur di level yang sama

---

## Level 1 — Foundation (Tidak Ada Dependensi)

Semua fitur ini bisa dikerjakan **secara paralel** tanpa menunggu yang lain.

| Prioritas | Fitur | Tabel | Alasan |
|-----------|-------|-------|--------|
| P1 | Perusahaan (Company) | companies | Root dari semua multi-tenant |
| P1 | Cabang (Branch) | ranches | Scope semua data operasional |
| P1 | Role & Permission | oles, permissions, ole_permissions | Dibutuhkan sebelum user bisa login dengan hak akses |
| P1 | ICD-10 | icd10_codes | Data referensi medis, bisa diimpor massal |
| P1 | **Gudang** | warehouses | Data master murni, tidak butuh FK bisnis apapun |
| P1 | **Satuan** | units | Data master murni, tidak butuh FK bisnis apapun |
| P1 | **Payment Term** | payment_terms | Data master murni, tidak butuh FK bisnis apapun |
| P1 | **Pajak (Tax)** | 	axes | Data master murni, tidak butuh FK bisnis apapun |

> **Catatan Gudang & Satuan:** Kedua entitas ini adalah contoh terbaik data master tanpa FK bisnis.
> Gudang hanya punya ranch_id dan company_id sebagai scope tenant (bukan relasi bisnis).
> Field ddress_id dan jenis_id adalah sisa legacy nullable yang tidak aktif dipakai.

---

## Level 2 — Core Master Data (Dependensi Minimal)

Bisa dimulai setelah Level 1 selesai. Masih bisa dikerjakan paralel antar fitur di level ini.

| Prioritas | Fitur | Tabel | Bergantung Pada |
|-----------|-------|-------|-----------------|
| P2 | COA / Chart of Accounts | ccounts | Cabang (branch_id), self-ref parent |
| P2 | User | users | Role (many2many via user_roles) |
| P2 | User Warehouse | user_warehouses | User + Gudang |
| P2 | Pasien | patients | Tidak ada FK bisnis, tapi perlu Branch/Company context |
| P2 | Produk / Item | master_products | Satuan & Gudang (nullable, tapi idealnya sudah ada) |

---

## Level 3 — Extended Master (Dependensi Menengah)

Dimulai setelah Level 2 selesai.

| Prioritas | Fitur | Tabel | Bergantung Pada |
|-----------|-------|-------|-----------------|
| P3 | Customer / Supplier | people | Payment Term, COA (opsional) |
| P3 | Konversi Satuan | item_conversions | Satuan (wajib), Produk (opsional) |
| P3 | BOM / Formula | oms, om_details | Produk + Satuan |
| P3 | Saldo Awal | opening_balance_records | COA + Branch |

---

## Level 4 — Transaksi Operasional

Dimulai setelah Level 3. Ini adalah fitur utama yang menghasilkan data transaksi.

| Prioritas | Fitur | Tabel | Bergantung Pada |
|-----------|-------|-------|-----------------|
| P4 | Purchase Order (PO) | purchase_orders, purchase_order_items | Supplier + Gudang + Produk + Satuan |
| P4 | Assembly / Perakitan | ssemblies, ssembly_items | Produk + Satuan + Gudang (+ BOM opsional) |
| P4 | Pindah Antar Gudang (PAG) | stock_transfers, stock_transfer_items | Gudang (min. 2) + Produk + Satuan |
| P4 | Sales Order (SO) | sales_orders, sales_order_details | Customer + Gudang + Payment Term + Produk |
| P4 | Transaksi POS | 	ransactions, 	ransaction_details | Gudang + User |

---

## Level 5 — Akuntansi & Laporan

Dimulai setelah Level 3-4. Bergantung pada COA dan data transaksi operasional.

| Prioritas | Fitur | Tabel | Bergantung Pada |
|-----------|-------|-------|-----------------|
| P5 | Jurnal Umum | journals, journal_lines | COA + Branch |
| P5 | Buku Besar | (view/query) | Jurnal |
| P5 | Neraca Saldo | (view/query) | Jurnal |
| P5 | Laporan Laba Rugi | (view/query) | Jurnal + COA bertipe revenue/expense |
| P5 | Neraca | (view/query) | Jurnal + COA bertipe asset/liability/equity |
| P5 | Closing Period | (prosedur) | Jurnal + COA |

---

## Diagram Alur Priority

```
[L1] Perusahaan ──┐
[L1] Cabang ──────┤
[L1] Role/Perm ───┤──► [L2] User ──────────────────► [L4] Transaksi POS
[L1] Gudang ──────┤──► [L2] User Warehouse
[L1] Satuan ──────┤──► [L2] Produk ──────────────────► [L3] BOM ──► [L4] Assembly
                  │          │                         [L3] Konversi
[L1] Payment Term ┤──► [L3] Customer/Supplier ────────► [L4] PO / SO
[L1] Pajak ───────┤
[L1] ICD-10 ──────┘
[L2] COA ─────────────────────────────────────────────► [L3] Saldo Awal
                                                         [L5] Jurnal → Laporan
```

---

*Dibuat: 2026-09-09 | Sumber: analisis model Go (be/app/model/)*
