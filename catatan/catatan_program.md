# Catatan Program — Klinik Starter

Tanggal update: 2026-03-15

---

## Tujuan Sistem

Membangun sistem manajemen klinik yang:
1. Operasional klinik stabil harian (registrasi, pemeriksaan, POS, stok).
2. Keuangan valid dan dapat diaudit (jurnal otomatis, laporan accounting).
3. Integrasi BPJS + SATUSEHAT aktif end-to-end.
4. Dashboard analitik dari data nyata untuk keputusan manajemen.

---

## Scope Modul

### 1. User, Role & Permission
- CRUD user, role, permission (RBAC granular).
- Role dasar: `admin`, `dokter`, `kasir`, `owner`.
- Guard API (403 jika unauthorized).
- Audit log untuk setiap aksi penting.

### 2. Master Data
- Pasien / pelanggan.
- Dokter / karyawan + fee default.
- Supplier + term payment.
- Obat / alkes: kode, kategori, harga beli/jual, min stok.
- Jasa tindakan + paket (bundle jasa/obat).

### 3. Operasional
- Registrasi & antrian pasien.
- Pemeriksaan + rekam medis.
- Pemakaian obat/alkes dari pemeriksaan.
- POS penjualan obat/jasa.
- Pembelian stok + hutang supplier.
- Piutang pasien / BPJS.

### 4. Keuangan (Accounting)
- COA hierarkis + mapping akun wajib.
- Jurnal otomatis dari POS, pembelian, pemeriksaan.
- Jurnal manual + reversal.
- Laporan: buku besar, neraca saldo, neraca, laba rugi, arus kas.
- Closing period bulanan + lock jurnal posted.

### 5. Integrasi Eksternal
- BPJS: verifikasi peserta, SEP basic.
- SATUSEHAT: sinkron IHS pasien, kirim Encounter, kirim rekam medis FHIR.

### 6. Storage (File)
- Bucket tunggal `klinik-starter`:
  - `klinik-invoices/` untuk PDF invoice
  - `klinik-images/` untuk foto pasien/scan dokumen
  - `klinik-reports/` untuk PDF laporan
  - `klinik-temp/` untuk upload sementara

### 7. Reporting & Analytics
- Laporan POS, tindakan, pendapatan dokter.
- Dashboard: tren pendapatan, top dokter, komposisi omzet.
- Customer retention + profitability obat/alkes.

### 8. System
- Global settings (profil klinik, tema).
- Backup/restore DB via admin.
- Audit log lengkap.

---

## Fase Pengerjaan

| Fase | Target | Durasi |
|---|---|---|
| **Fase 1** | MVP Operasional (tanpa integrasi eksternal) | 2–3 minggu |
| **Fase 2** | Stabilitas Keuangan & Laporan Accounting | 2 minggu |
| **Fase 3** | Integrasi BPJS & SATUSEHAT | 2–3 minggu |
| **Fase 4** | Analitik, Optimasi, Hardening | 1–2 minggu |

**Total estimasi: 8 minggu**

---

## Asumsi Teknis

1. Semua transaksi memiliki `created_by`, `updated_by`, `reference_no`.
2. Timezone konsisten `Asia/Makassar`.
3. Soft delete untuk master data penting.
4. Laporan hanya membaca data status `posted`, bukan `draft`.
5. Posting jurnal selalu dilakukan dalam satu DB transaction (atomic).
6. File PDF/gambar disimpan ke MinIO, bukan ke disk lokal.

---

## Akun Dummy (Seeder)

- dokter1@klinik.local / password
- dokter2@klinik.local / password
- kasir@klinik.local / password
- admin@klinik.local / admin123

---

## Kriteria Go-Live

- [ ] Semua skenario kritikal lulus UAT.
- [ ] Backup restore terverifikasi di staging.
- [ ] Role permission final dan terkunci.
- [ ] Tim operasional menerima SOP singkat (kasir, admin, dokter).
- [ ] Monitoring error dan audit log aktif.

---

## Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Kredensial BPJS/SATUSEHAT bocor | Secret manager, masking di UI, rotate key berkala |
| Data tidak konsisten antara transaksi dan jurnal | Posting service terpusat + DB transaction atomic |
| API eksternal down | Queue retry + fallback status draft |
| Laporan lambat | Index query, cache agregat, pre-aggregation |
| File rusak / hilang | MinIO replikasi, backup berkala |
