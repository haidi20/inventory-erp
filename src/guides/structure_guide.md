# Panduan Struktur Folder dan Penamaan (Inertia Vue 3 TS)

## Tujuan
Panduan ini menjadi standar struktur fitur agar konsisten, mudah di-maintain, dan mendukung pengembangan berbasis **TDD** dengan stack **Inertia + Vue 3 + TypeScript**.

---

## Prinsip Utama & Standar Teknis Wajib

### 1. Wajib Menggunakan Konsep TDD
**Wajib / harus gunakan konsep TDD ke semua skenario yang ada.** Pengembangan dimulai dari penulisan test (scenario RED) sebelum masuk ke implementasi. Minimal mencakup:
- *Happy Path* (aliran utama yang sukses).
- *Validation Error* (masukan tidak valid).
- *Data Not Found* (resource tidak tersedia).
- *Unauthorized/Forbidden* (hak akses).
- *Duplicate Case* (duplikasi data unik).
- *Edge Cases* utama lainnya.

### 2. Standar Migration
Setiap tabel baru wajib memiliki:
- **Indexing**: Pada kolom yang sering digunakan untuk filter, join, atau sorting.
- **Soft Deletes**: Menggunakan `$table->softDeletes()`.
- **Timestamps**: Menggunakan `$table->timestamps()`.
- **Audit Columns**: Wajib ada `created_by`, `updated_by`, dan `deleted_by` (char 36/UUID).

### 3. Pencarian & Paginasi (Server Side)
Fitur listing data **sudah support search semua field atau by column (defaultnya semua field)**.
- **Server Side Pagination**: Tidak boleh menarik semua data sekaligus.
- **Server Side Per-Page**: Pilihan jumlah data per halaman diproses di backend.
- Pencarian menggunakan **Debounce** (minimal 500ms) untuk efisiensi request.

### 4. Import & Export (Standard Industri)
Setiap fitur import/export wajib menyediakan:
- **File Contoh (Template)**: File `.xlsx` dengan minimal 2 sheet:
  - `Sheet 1`: Template data import (header final).
  - `Sheet 2`: Penjelasan lengkap (panduan kolom wajib, format nilai, dan contoh).
- **Konsep Upsert**: Code import **gunakan konsep upsert**. Mesti dicari dulu field yang menjadi unique key untuk patokan.
- **Transaction**: Proses bulk import wajib dibungkus dalam DB Transaction untuk menjaga integritas data.

---

## Struktur Folder Fitur

Referensi utama folder frontend mengikuti implementasi:
`src/resources/js/features/featureName`

```text
app/
|-- Http/
|   `-- Controllers/
|       `-- featureName/
|           `-- featureNameController.php
|-- Services/
|   `-- featureName/
|       `-- featureNameService.php
|-- Repositories/
|   `-- Infrastructure/
|       |-- featureNameRepository.php
|       `-- featureNameRepositoryInterface.php
|-- Entities/
|   `-- featureName/
|       `-- featureNameEntity.php
`-- Models/
    `-- Master/
        `-- featureName.php

resources/
`-- js/
    `-- features/
        `-- featureName/
            |-- data/
            |   `-- repositories/
            |       `-- featureName.repo_impl.ts
            |-- domain/
            |   |-- di/
            |   |   `-- user.di.ts
            |   |-- models/
            |   |   `-- user.model.ts
            |   |-- repositories/
            |   |   `-- user.repository.ts
            |   `-- usecases/
            |       |-- fetch-users.usecase.ts
            |       |-- save-user.usecase.ts
            |       |-- delete-user.usecase.ts
            |       |-- export-users.usecase.ts
            |       `-- import-users.usecase.ts
            `-- presentations/
                |-- viewmodels/
                |   `-- user.vm.ts
                `-- views/
                    |-- UserIndex.vue
                    |-- UserForm.vue
                    `-- Partials/
                        |-- UserFilters.vue
                        `-- UserTable.vue
```

---

## Aturan Layer (Inertia + Vue 3 TS)

| Layer | Folder | Tanggung Jawab |
|-------|--------|----------------|
| **Controller** | `app/Http/Controllers/` | Handle request, panggil Service, return `Inertia::render`. |
| **Service** | `app/Services/` | Business logic, orkestrasi repository, validasi bisnis (wajib). |
| **Repository** | `app/Repositories/` | Satu-satunya layer yang akses `Model`, Query Builder, atau `DB`. |
| **Entity** | `app/Entities/` | Class pengangkut data (DTO) untuk type safety antar layer. |
| **Domain Models** | `resources/js/features/[Module]/[Feature]/domain/models/` | TypeScript interface/type untuk data entitas. |
| **Repo Interfaces** | `resources/js/features/[Module]/[Feature]/domain/repositories/` | Kontrak method repository di frontend. |
| **UseCases** | `resources/js/features/[Module]/[Feature]/domain/usecases/` | Logika bisnis frontend, orchestrator repository. |
| **Repo Impl** | `resources/js/features/[Module]/[Feature]/data/repositories/` | Implementasi konkret repository (`[name].repo_impl.ts`). |
| **ViewModel** | `resources/js/features/[Module]/[Feature]/presentations/viewmodels/` | Menjembatani state UI dan usecase. |
| **Views** | `resources/js/features/[Module]/[Feature]/presentations/views/` | Komponen Vue (`UserIndex.vue`, `UserForm.vue`, `Partials/*`). |

---

## Standar Naming & Implementasi

### PHP (Backend)
- Method `store` digunakan untuk create atau upsert.
- Method di Service/Repo wajib menerima `Entity`, bukan array mentah.

### TypeScript (Frontend)
- **Root fitur**: Gunakan `resources/js/features/[Module]/[Feature]/` (contoh: `Settings/User`).
- **Domain Model**: Letakkan di `domain/models/` dengan nama `[feature].model.ts`.
- **Repo Interface**: Letakkan di `domain/repositories/` dengan nama `[feature].repository.ts`.
- **UseCase**: Letakkan di `domain/usecases/` dengan format `kebab-case` dan suffix `.usecase.ts`.
- **DI**: Letakkan di `domain/di/` dengan nama `[feature].di.ts`.
- **Repo Implementation**: Gunakan format `[feature].repo_impl.ts` di `data/repositories/`.
- **ViewModel**: Letakkan di `presentations/viewmodels/` dengan nama `[feature].vm.ts`.
- **Views**: Letakkan di `presentations/views/`, gunakan `PascalCase` untuk file Vue (`UserIndex.vue`, `UserForm.vue`, `Partials/UserTable.vue`).

---

## Catatan Penting
1. **Tidak menggunakan jQuery**. Seluruh interaksi asinkron ditangani oleh Inertia atau axios.
2. **Data awal di seeder**, bukan di migration.
3. **Naming tetap bahasa Inggris**, bahasa Indonesia hanya untuk UI yang terlihat user.
