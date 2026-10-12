---
name: klinikdev
description: Standar utama pengembangan fitur baru di proyek Klinik 2026. Gunakan saat Codex diminta merancang, mengimplementasikan, atau mereview fitur baru end-to-end di Laravel + Inertia + Vue 3 + TypeScript agar patuh pada TDD, arsitektur modular, entity-driven backend, server-side listing/search/pagination, import-export upsert, audit columns, indexing, dan validasi penuh lewat Vitest, PHPUnit, Playwright, typecheck, dan build.
---

# Klinik Development Guide (Klinik 2026)

Gunakan skill ini sebagai patokan default untuk fitur baru. Jika task hanya fokus backend, gunakan referensi atau skill turunan Laravel. Jika task hanya fokus frontend, gunakan referensi atau skill turunan frontend.

## Aturan Wajib

1. Mulai dari TDD.
Selalu buat atau revisi test lebih dulu sebelum implementasi. Minimal cakup:
- `happy path`
- `validation error`
- `data not found`
- `unauthorized`
- `duplicate case`

2. Wajib sebut standar teknis saat analisa.
Dalam analisa awal, selalu nyatakan:
- TDD
- audit columns
- indexing
- server-side listing/search/pagination
- import/export upsert bila relevan

3. Wajib pakai standar audit database.
Tabel baru harus punya:
- `created_at`
- `updated_at`
- `deleted_at` bertipe `bigInteger unsigned` default `0`
- `created_by`
- `updated_by`
- `deleted_by`

4. Wajib pakai UUID.
Gunakan `uuid` atau `char(36)` untuk identifier utama.

5. Wajib pasang index.
Tambahkan index untuk:
- kolom pencarian
- foreign key
- kolom join
- kolom filter dan sort yang sering dipakai

6. Wajib server-side untuk data table.
Listing harus mendukung:
- search default semua field
- search per kolom
- sort di server
- pagination di server
- per-page di server

7. Wajib upsert untuk import bila ada unique key.
Template import minimal 2 sheet:
- `Data`
- `Penjelasan`

8. Wajib pikirkan pemisahan lingkungan dan data perusahaan.
Dalam proyek Laravel `src`, fitur baru harus dirancang dengan konsep:
- `Sandbox / Staging` untuk lingkungan uji coba
- `Production` untuk lingkungan nyata
- lebih diutamakan memakai `1 database` dengan pemisahan data berdasarkan perusahaan, misalnya `company_uuid`, `company_id`, atau relasi perusahaan yang setara

Konsekuensinya:
- query, listing, import, export, dan validasi harus selalu sadar scope perusahaan
- data antar perusahaan tidak boleh saling bocor
- fixture dan assertion test `.php` dan `.ts` wajib memverifikasi data hanya terbaca, berubah, dan terhapus di perusahaan yang benar
- skenario `unauthorized`, `not found`, dan `duplicate` juga harus diuji dalam konteks perusahaan yang aktif
- untuk tabel master dan transaksi yang dimiliki tenant/perusahaan, utamakan menyimpan `company_uuid` langsung di tabel
- jangan menjadikan `created_by -> user -> branch -> company` sebagai scope utama data; jalur itu dipakai untuk audit, bukan tenant boundary
- siapkan seeder eksplisit untuk company `sandbox/staging/testing` dan company `production` agar runtime dan test punya batas data yang konsisten
- semua test `.php` dan `.ts` yang relevan harus default mengarah ke company `sandbox/staging/testing`

## Standar Backend

- `Controller`: tipis, hanya validasi request flow, panggil service, dan render response
- `Service`: pusat logika bisnis
- `Repository`: satu-satunya layer akses database
- `Entity`: kontrak parameter dan return antar layer

### Larangan Backend

- jangan pakai array mentah sebagai kontrak service jika fitur mengikuti pola modular baru
- jangan campur query database di controller
- jangan bocorkan model Eloquent ke frontend bila entity atau transformasi sudah tersedia

### Checklist Backend

- buat migration dengan audit columns
- buat entity input/output
- buat interface repository typed
- implementasikan repository
- implementasikan service dengan entity
- hubungkan controller ke service
- tambahkan auth atau permission yang sesuai
- tambahkan export/import bila fitur listing membutuhkannya

## Standar Frontend

Struktur folder wajib modular:

```text
resources/js/features/[Module]/[Feature]/
```

Urutan implementasi:
- `data/repositories/*.repo_impl.ts`
- `domain/repositories/*.repository.ts`
- `domain/usecases/*.usecase.ts`
- `presentations/viewmodels/*.vm.ts`
- `presentations/views/*.vue`

### Aturan Repo Impl

- satu-satunya tempat memakai Axios atau Inertia
- setiap method wajib `try-catch`
- setiap method wajib return `Either<Failure, T>`

### Aturan ViewModel

- kelola state UI
- panggil use case, bukan Inertia langsung
- tangani permission, loading, filter, sort, pagination, dan form state

### Aturan View

- fokus ke binding UI
- jangan simpan logika bisnis berat di `.vue`

## Alur Kerja Standar

1. Analisa kebutuhan dan nyatakan standar teknis wajib.
   Sertakan juga catatan:
   - lingkungan `Sandbox / Staging` dan `Production`
   - strategi `1 database` dengan pemisahan data per perusahaan
   - kewajiban test `.php` dan `.ts` untuk memverifikasi isolation data per perusahaan
2. Tulis test dan migration.
3. Implementasikan backend memakai entity, service, repository.
4. Implementasikan frontend memakai pola modular.
5. Jalankan validasi akhir.

## Validasi Akhir Wajib

Setelah mengubah `.php`, `.ts`, atau `.vue`, jalankan yang relevan:
- `npm run typecheck`
- `npm run build`
- Vitest
- PHPUnit / feature test backend
- Playwright real integrated test di `resources/js/tests/`

Jangan tutup task sebelum validasi yang relevan selesai atau ada blocker yang dijelaskan jelas.

## Referensi Proyek

Jika perlu detail tambahan, baca:
- [`references/project-guides.md`](references/project-guides.md)
