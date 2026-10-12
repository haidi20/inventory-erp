---
name: klinikbe
description: Standar backend Laravel untuk fitur baru di proyek Klinik 2026. Gunakan saat Codex diminta membuat migration, entity, repository, service, controller, permission, import-export, atau feature test backend agar patuh pada audit columns, indexing, entity-driven contract, TDD, dan server-side data processing.
---

# Klinik BE (Klinik 2026)

Gunakan skill ini saat task dominan backend Laravel.

## Aturan Wajib

1. Mulai dari test backend.
Minimal siapkan skenario:
- `happy path`
- `validation error`
- `data not found`
- `unauthorized`
- `duplicate case`

2. Migration wajib punya audit columns.
Gunakan:
- `created_at`
- `updated_at`
- `deleted_at` default `0`
- `created_by`
- `updated_by`
- `deleted_by`

3. Gunakan UUID.
Primary key atau identifier utama harus `uuid` atau `char(36)`.

4. Tambahkan index.
Index wajib untuk kolom:
- pencarian
- join
- foreign key
- sort/filter

5. Pikirkan scope perusahaan sejak migration.
Untuk proyek Laravel `src` dengan konsep `1 database multi-perusahaan`:
- tabel master dan transaksi tenant-owned utamakan menyimpan `company_uuid` langsung
- `created_by`, `updated_by`, dan `deleted_by` tetap untuk audit trail, bukan pengganti scope tenant
- hindari menjadikan `created_by -> user -> branch -> company` sebagai filter utama karena query akan lebih berat dan lebih rapuh
- tabel referensi global murni boleh tanpa `company_uuid`, tetapi harus jelas bahwa data itu shared lintas perusahaan

6. Seeder wajib siapkan company testing.
Minimal siapkan:
- company `sandbox/staging/testing`
- company `production`
- branch dan user yang terhubung bila fitur memerlukannya

Test backend harus memverifikasi data hanya terbaca dan berubah pada company yang benar, dan default fixture test harus mengarah ke company `sandbox/staging/testing`.

## Kontrak Layer

- `Controller`: tipis
- `Service`: bisnis logic
- `Repository`: query database
- `Entity`: parameter dan response

### Larangan

- jangan query model langsung di controller
- jangan buat service menerima array mentah kalau entity sudah tersedia
- jangan campur validasi bisnis berat di repository

## Checklist Backend

- buat migration
- buat entity
- buat repository interface
- buat repository implementation
- buat service
- buat controller
- sambungkan route dan permission
- tambahkan export/import bila dibutuhkan
- pastikan import mendukung upsert bila ada unique key
- siapkan seeder company testing dan pastikan data fitur mengikuti scope company yang aktif

## Validasi Akhir

Jalankan yang relevan:
- `php artisan test`
- `php artisan test --filter=...`
- `npm run typecheck` bila payload frontend terdampak

## Referensi Proyek

- [`references/project-guides.md`](references/project-guides.md)
