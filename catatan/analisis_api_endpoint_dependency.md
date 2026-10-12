# Analisis Dependensi API Endpoint

Dokumen ini memetakan **urutan API endpoint yang harus ready** sebelum endpoint lain bisa berjalan normal.
Berdasarkan analisis foreign key dan relasi antar model di e/app/model/.

---

## Konvensi

- ✅ = Endpoint sudah ada di routes
- ⚠️ = Endpoint ada tapi partial / perlu validasi
- [WAJIB] = endpoint referensi harus tersedia sebelum endpoint ini dipanggil
- [OPSIONAL] = endpoint referensi direkomendasikan tapi tidak blocking

---

## Group 1 — Endpoint Independen (Tidak Butuh Endpoint Lain)

Endpoint berikut bisa dipanggil pertama kali tanpa prasyarat data lain.

### Auth
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | /api/v1/auth/login | Login user |
| POST | /api/v1/auth/logout | Logout |
| GET | /api/v1/auth/me | Info user aktif |

### Company (Perusahaan)
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | /api/v1/settings/company | Lihat profil perusahaan |
| PUT | /api/v1/settings/company | Update profil perusahaan |

### Branch (Cabang)
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | /api/v1/master/branches | List cabang |
| POST | /api/v1/master/branches | Buat cabang baru |
| PUT | /api/v1/master/branches/:uuid | Update cabang |
| DELETE | /api/v1/master/branches/:uuid | Hapus cabang |

### Gudang — **Independen Murni**
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | /api/v1/master/warehouses | List gudang |
| POST | /api/v1/master/warehouses | Buat gudang baru |
| GET | /api/v1/master/warehouses/:uuid | Detail gudang |
| PUT | /api/v1/master/warehouses/:uuid | Update gudang |
| DELETE | /api/v1/master/warehouses/:uuid | Hapus gudang |
| POST | /api/v1/master/warehouses/import | Import Excel |

> Gudang tidak membutuhkan data apapun selain ranch_id + company_id dari JWT context.
> Ini adalah salah satu endpoint paling bebas dependensi di seluruh sistem.

### Satuan — **Independen Murni**
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | /api/v1/master/units | List satuan |
| POST | /api/v1/master/units | Buat satuan |
| PUT | /api/v1/master/units/:uuid | Update satuan |
| DELETE | /api/v1/master/units/:uuid | Hapus satuan |

### Payment Term — **Independen Murni**
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | /api/v1/master/payment-terms | List payment term |
| POST | /api/v1/master/payment-terms | Buat payment term |
| PUT | /api/v1/master/payment-terms/:uuid | Update |
| DELETE | /api/v1/master/payment-terms/:uuid | Hapus |

### Pajak (Tax) — **Independen Murni**
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | /api/v1/master/taxes | List pajak |
| POST | /api/v1/master/taxes | Buat pajak |
| PUT | /api/v1/master/taxes/:uuid | Update |
| DELETE | /api/v1/master/taxes/:uuid | Hapus |

### Role & Permission
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | /api/v1/roles | List role |
| POST | /api/v1/roles | Buat role |
| GET | /api/v1/permissions | List permission |
| PUT | /api/v1/roles/:uuid/permissions | Assign permission ke role |

### ICD-10
| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | /api/v1/settings/icd10 | List kode ICD-10 |
| POST | /api/v1/settings/icd10 | Tambah kode |

---

## Group 2 — Endpoint dengan Dependensi Ringan

Membutuhkan minimal 1 endpoint Group 1 sudah berjalan.

### COA / Akun (Chart of Accounts)
| Method | Endpoint | Dependensi | Keterangan |
|--------|----------|------------|------------|
| GET | /api/v1/accounting/accounts | Branch [WAJIB] | List COA |
| POST | /api/v1/accounting/accounts | Branch [WAJIB] | Buat akun |
| PUT | /api/v1/accounting/accounts/:uuid | COA parent [OPSIONAL] | Update akun |

### User
| Method | Endpoint | Dependensi | Keterangan |
|--------|----------|------------|------------|
| GET | /api/v1/users | Role [OPSIONAL] | List user |
| POST | /api/v1/users | Role [OPSIONAL] | Buat user |
| PUT | /api/v1/users/:uuid/warehouses | Gudang [WAJIB] | Assign gudang ke user |

### Produk / Item Master
| Method | Endpoint | Dependensi | Keterangan |
|--------|----------|------------|------------|
| GET | /api/v1/master/products | - | List produk |
| POST | /api/v1/master/products | Satuan [OPSIONAL], Gudang [OPSIONAL] | Buat produk |
| PUT | /api/v1/master/products/:uuid | - | Update produk |
| DELETE | /api/v1/master/products/:uuid | - | Hapus produk |
| POST | /api/v1/master/products/import | - | Import Excel |

### Pasien
| Method | Endpoint | Dependensi | Keterangan |
|--------|----------|------------|------------|
| GET | /api/v1/master/patients | - | List pasien |
| POST | /api/v1/master/patients | - | Tambah pasien |
| PUT | /api/v1/master/patients/:uuid | - | Update pasien |

---

## Group 3 — Endpoint dengan Dependensi Menengah

### Customer / Supplier
| Method | Endpoint | Dependensi | Keterangan |
|--------|----------|------------|------------|
| GET | /api/v1/master/people | - | List customer/supplier |
| POST | /api/v1/master/people | Payment Term [OPSIONAL], COA [OPSIONAL] | Buat |
| PUT | /api/v1/master/people/:uuid | - | Update |

### Konversi Satuan
| Method | Endpoint | Dependensi | Keterangan |
|--------|----------|------------|------------|
| GET | /api/v1/master/item-conversions | - | List konversi |
| POST | /api/v1/master/item-conversions | Satuan [WAJIB], Produk [OPSIONAL] | Buat konversi |

### BOM / Formula
| Method | Endpoint | Dependensi | Keterangan |
|--------|----------|------------|------------|
| GET | /api/v1/master/boms | - | List BOM |
| POST | /api/v1/master/boms | Produk [WAJIB], Satuan [WAJIB] | Buat formula |
| PUT | /api/v1/master/boms/:uuid | - | Update |

### Saldo Awal Akuntansi
| Method | Endpoint | Dependensi | Keterangan |
|--------|----------|------------|------------|
| GET | /api/v1/accounting/opening-balance | COA [WAJIB] | List saldo awal |
| POST | /api/v1/accounting/opening-balance | COA [WAJIB], Branch [WAJIB] | Buat saldo awal |
| POST | /api/v1/accounting/opening-balance/:uuid/post | COA [WAJIB] | Posting → buat jurnal |

---

## Group 4 — Endpoint Transaksi Operasional

### Purchase Order
| Method | Endpoint | Dependensi |
|--------|----------|------------|
| GET | /api/v1/purchase-orders | - |
| POST | /api/v1/purchase-orders | Supplier [WAJIB], Gudang [WAJIB], Produk [WAJIB], Satuan [WAJIB] |
| PUT | /api/v1/purchase-orders/:uuid | - |
| POST | /api/v1/purchase-orders/:uuid/confirm | - |

### Assembly / Perakitan
| Method | Endpoint | Dependensi |
|--------|----------|------------|
| GET | /api/v1/assemblies | - |
| POST | /api/v1/assemblies | Produk [WAJIB], Satuan [WAJIB], Gudang [WAJIB] |
| POST | /api/v1/assemblies/:uuid/post | BOM [OPSIONAL] |

### Pindah Antar Gudang (PAG)
| Method | Endpoint | Dependensi |
|--------|----------|------------|
| GET | /api/v1/stock-transfers | - |
| POST | /api/v1/stock-transfers | Gudang Asal [WAJIB], Gudang Tujuan [WAJIB], Produk [WAJIB] |
| POST | /api/v1/stock-transfers/:uuid/post | - |

### Sales Order
| Method | Endpoint | Dependensi |
|--------|----------|------------|
| GET | /api/v1/sales/orders | - |
| POST | /api/v1/sales/orders | Customer [WAJIB], Gudang [WAJIB], Payment Term [WAJIB], Produk [WAJIB] |

### Transaksi POS
| Method | Endpoint | Dependensi |
|--------|----------|------------|
| GET | /api/v1/transactions | - |
| POST | /api/v1/transactions | Gudang [WAJIB], User [WAJIB] |

---

## Group 5 — Endpoint Akuntansi & Laporan

Semua endpoint ini bergantung pada COA dan data jurnal yang terisi.

| Method | Endpoint | Dependensi |
|--------|----------|------------|
| GET | /api/v1/accounting/journals | COA [WAJIB] |
| POST | /api/v1/accounting/journals | COA [WAJIB] |
| GET | /api/v1/accounting/general-ledger | Jurnal [WAJIB] |
| GET | /api/v1/accounting/trial-balance | Jurnal [WAJIB] |
| GET | /api/v1/accounting/financial-report/income-statement | Jurnal + COA PL [WAJIB] |
| GET | /api/v1/accounting/financial-report/balance-sheet | Jurnal + COA BS [WAJIB] |
| POST | /api/v1/accounting/closing-period | Jurnal + Period [WAJIB] |

---

## Urutan Minimal untuk Demo / Staging Fungsional

Urutan endpoint yang harus berjalan agar sistem bisa dipakai end-to-end:

`
1.  POST /auth/login
2.  GET/POST /master/branches
3.  GET/POST /master/warehouses      ← independen, bisa langsung
4.  GET/POST /master/units           ← independen, bisa langsung
5.  GET/POST /master/payment-terms   ← independen, bisa langsung
6.  GET/POST /master/taxes           ← independen, bisa langsung
7.  GET/POST /accounting/accounts    ← butuh branch
8.  GET/POST /master/products        ← butuh unit (opsional tapi disarankan)
9.  GET/POST /master/people          ← butuh payment term (opsional)
10. GET/POST /purchase-orders        ← butuh supplier + gudang + produk
11. GET/POST /accounting/journals    ← butuh COA
`

---

*Dibuat: 2026-09-09 | Sumber: be/routes/v1/ dan be/app/model/*
