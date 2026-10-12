jangan di hapus yg di bawah ini:
yg di bawah ini paling penting
sdh support search semua field atau by column defaultnya semua field, server side paginate, per page server side, dan import export beserta contoh file import. ada 2 sheet sheet utama dan sheet ke2 penjelasan lengkap. code import gunakan konsep upsert. mesti di cari dulu field mana yg menjadi unique key nya untuk jadi patokannya.
di migration wajib indexing, wajib softdelete, timestamp, created_by, updated_by, deleted_by.
wajib / harus gunakan konsep TDD ke semua skenario yang ada. sebutkan kata kata di atas di dalam analisa anda.

# Panduan Pengembangan Fitur (Inertia + Vue 3 + TS)

## Tujuan

Panduan ini menjadi standar pengembangan fitur baru agar konsisten mengikuti struktur modular dan arsitektur yang telah ditetapkan. Dokumentasi ini wajib diikuti untuk setiap fitur baru. **Dilarang menggunakan jQuery.**

## Referensi Wajib

- Patokan utama struktur path, folder, file, dan naming: `D:\projects\klinik\src\guides\structure_guide.md`.
- Semua implementasi frontend wajib mengikuti pola `resources/js/features/[Module]/[Feature]/...`.
- **Tahap Awal (Scaffolding)**: Untuk mempercepat pengembangan, diperbolehkan melakukan copas dari fitur referensi berikut:
  - Fitur: `D:\projects\klinik\src\resources\js\features\Settings\User`
  - Test: `D:\projects\klinik\src\resources\js\tests\settings\user`
  - **PENTING**: Meskipun melakukan copas, seluruh konsep, arsitektur, dan aturan yang didefinisikan dalam panduan ini (seperti TDD, Entity, Layering, dan Larangan Inertia di VM) **wajib tetap terpenuhi dan disesuaikan** dengan fitur yang sedang dibuat.

---

## Prinsip Utama

### 1. Wajib Menggunakan Konsep TDD
**Wajib / harus gunakan konsep TDD ke semua skenario yang ada.**
- **Cakupan Testing Lapisan Backend**: Pastikan membuat testing di **semua lapisan** secara komprehensif, mulai dari **Route, Controller, Service, Repository, Model, Seeder, hingga Migration**.
- **Cakupan Method**: Semua method wajib diuji baik untuk skenario **Success** maupun **Gagal/Error** (Validation error, data not found, unauthorized, duplicate case, dll).
- Scenario RED: Tulis test terlebih dahulu sebelum memulai implementasi kode.
- Implementasi baru dimulai setelah skenario test siap.

### 2. Arsitektur Wajib & Kontrak Layer
Pengembangan harus mengikuti pemisahan layer yang ketat:
- **Backend**: `Controller -> Service -> Repository (with Entity)`.
- **Frontend**: Modular per fitur di `resources/js/features/[Module]/[Feature]/`.

**Aturan Kontrak Method (Mandatory):**
- **Parameter**: Dilarang menggunakan `array` mentah untuk data kompleks. Wajib menggunakan **Entity** sebagai parameter input di setiap method Service dan Repository.
- **Response**: Setiap method wajib mendefinisikan tipe data kembalian (**return type**) secara eksplisit (contoh: `Entity`, `Collection<Entity>`, `LengthAwarePaginator`, atau custom response object).

### 3. Standar Database & Migration
- **Modifikasi Migrasi**: Jika ada penambahan field atau update pada struktur tabel, maka **wajib ditambahkan langsung di file migration yang sudah ada** (selama masih dalam tahap pengembangan), bukan dengan membuat file migration baru.
- **Wajib indexing** pada kolom pencarian/join.
- **Wajib softdelete, timestamp, created_by, updated_by, deleted_by**.
- Gunakan `char(36)` atau `uuid` untuk kolom audit.
- Fitur baru di Laravel `src` harus mempertimbangkan dua konteks lingkungan:
  - **Sandbox / Staging (Lingkungan Uji Coba)**
  - **Production (Lingkungan Nyata)**
- Arsitektur data lebih diutamakan memakai **1 database dengan pemisahan data berdasarkan perusahaan**.
- **Scoping Perusahaan & Cabang (Wajib)**:
  - Desain tabel, relasi, query, filter, dan permission harus sadar `scope perusahaan` dan `scope cabang` agar data tidak bocor.
  - Untuk tabel **master** dan **transaksi** yang dimiliki tenant/perusahaan, **utamakan menyimpan `company_uuid` dan `branch_uuid` langsung di tabel**.
  - **Restriksi Cabang**: Jika seorang user memiliki data keterikatan pada cabang tertentu (`branch_uuid` tidak null pada profile user), maka user tersebut **dilarang keras** melakukan operasi CRUD (Create, Read, Update, Delete) pada data milik cabang lain. Sistem harus memastikan data terscope secara otomatis berdasarkan cabang user tersebut.
- **Jangan mengandalkan `created_by -> user -> branch -> company` sebagai scope utama data**, karena jalur itu hanya cocok untuk audit dan cenderung lebih berat pada query listing, search, report, import, export, dan validasi.
- `created_by`, `updated_by`, dan `deleted_by` tetap dipakai untuk **audit trail**, bukan sebagai pengganti batas utama tenant/company.
- Tabel referensi global murni yang benar-benar dipakai bersama lintas perusahaan boleh tidak memiliki `company_uuid`, tetapi keputusan itu harus jelas dan disengaja.
- Untuk fitur yang mendukung sandbox/staging dan production dalam **1 database**, **wajib** menyiapkan data perusahaan terpisah minimal:
  - company untuk `sandbox/staging/testing`
  - company untuk `production`
  - branch dan user yang terhubung ke masing-masing company bila fitur memerlukannya
- Seeder harus memastikan seluruh data uji `.php` dan `.ts` mengarah ke company `sandbox/staging/testing`, bukan bercampur dengan data production.

### 4. Standar Listing, Search, Pagination, Sorting, Import, Export
- **Sdh support search semua field atau by column (defaultnya semua field), server side paginate, per page server side, dan sorting by column (clickable table headers)**.
- **Sorting wajib diimplementasikan dengan konsep TDD (Scenario RED untuk arah ASC/DESC)**.
- **Import export beserta contoh file import. Ada 2 sheet: sheet utama dan sheet ke2 penjelasan lengkap**.
- **Code import gunakan konsep upsert. Mesti di cari dulu field mana yg menjadi unique key nya untuk jadi patokannya**.
- Semua proses listing, filter, import, export, dan validasi wajib mempertimbangkan `scope perusahaan` pada model `1 database multi-perusahaan`.

### 5. Aturan Vue, ViewModel, UseCase, dan Inertia
- **ViewModel wajib menggunakan UseCase** untuk menjalankan aksi bisnis.
- **Tidak boleh ada kode Inertia di ViewModel** (`router.get/post/put/delete`, `Inertia.visit`, dan sejenisnya).
- **Semua kode Inertia wajib ada di Repo Impl** pada `data/repositories/*.repo_impl.ts`.
- **Repo Impl wajib menerapkan `try-catch` pada setiap method dan mengembalikan pola `Either` (`Left` untuk Failure, `Right` untuk Success).**
- **UseCase wajib menerapkan `try-catch` bila memiliki proses transaksional / orchestration dan tetap mengembalikan pola `Either`.**
- Komponen Vue (`views`) hanya binding UI/state/action ke ViewModel, tidak boleh memanggil Inertia secara langsung.

---

## Urutan Kerja Standar

### Step 1: Tulis Test Backend Dulu
Tulis feature test untuk memastikan endpoint backend bekerja sesuai kontrak data.
- Test `.php` wajib memverifikasi data hanya terbaca dan berubah pada perusahaan yang benar.
- Tambahkan skenario kebocoran lintas perusahaan sebagai bagian dari `unauthorized`, `not found`, atau `duplicate case` bila relevan.

### Step 2: Migration & Seeder
Buat tabel dengan standar audit lengkap. Isi seeder dengan data yang **relevan** (skenario klinik nyata), bukan data dummy asal-asalan.
- Pada arsitektur `1 database multi-perusahaan`, seeder wajib menyiapkan data company testing/sandbox secara eksplisit agar test backend dan frontend punya scope perusahaan yang konsisten.

### Step 3: Implementasi Backend (Service & Repo)
Fitur listing **sudah support search semua field atau by column (defaultnya semua field), server side paginate, per page server side, dan sorting by column (clickable table headers).**

**Contoh Kontrak Method di Service:**
```php
public function store(UserEntity $entity): UserEntity
{
    // Validasi dan logika bisnis
    return $this->repository->store($entity);
}
```

**Contoh Kontrak Method di Repository:**
```php
public function store(UserEntity $entity): UserEntity
{
    // Query database
}
```

### Step 4: Implementasi Frontend (Modular)
1. **Root Fitur**: Buat folder di `resources/js/features/[Module]/[Feature]/`.
2. **Domain**:
   - `domain/di/[feature].di.ts`
   - `domain/models/[feature].model.ts`
   - `domain/repositories/[feature].repository.ts`
   - `domain/usecases/*.usecase.ts`
3. **Data**: Implementasikan repositori konkret di `data/repositories/[feature].repo_impl.ts`.
4. **Presentation**:
   - `presentations/viewmodels/[feature].vm.ts`
   - `presentations/views/*.vue` (PascalCase, contoh: `UserIndex.vue`, `UserForm.vue`)
   - `presentations/views/Partials/*.vue`
5. **Alur wajib Vue**: `Views (Vue) -> ViewModel -> UseCase -> Repository Interface -> Repo Impl (Inertia)`.
6. **Larangan keras**: Tidak boleh ada import/pemanggilan Inertia di `views` dan `viewmodels`.
7. **Catatan wajib**:
   - Setiap method di `repo_impl.ts` harus dibungkus `try-catch`.
   - `repo_impl.ts` harus mengembalikan `Either<Failure, T>`.
   - `usecase.ts` harus menjaga kontrak `Either<Failure, T>` dan menggunakan `try-catch` bila ada orchestration/proses tambahan.

### Step 5: Pengujian Terintegrasi (Real Integrated Test)
Selain unit test (Vitest), wajib mengimplementasikan **Real Integrated Test** menggunakan **Playwright (Headless)** untuk memvalidasi Repositori konkret (`repo_impl.ts`).
- **Lokasi**: `resources/js/tests/[Module]/[Feature]/data/repositories/[name].e2e.spec.ts`.
- **Tujuan**: Memastikan repositori benar-benar terhubung ke Live Backend dan Database (bukan mock) dengan menjalan alur CRUD lengkap (Create, Read, Update, Delete).
- **Patokan Utama**: `resources/js/tests/settings/user/data/repositories/user.repo_impl.e2e.spec.ts`.
- Test `.ts` juga wajib memverifikasi isolation data berdasarkan perusahaan pada skenario yang relevan.

### Step 6: Import & Export
**Import export beserta contoh file import. Ada 2 sheet: sheet utama dan sheet ke2 penjelasan lengkap.**
- **Code import gunakan konsep upsert. Mesti di cari dulu field mana yang menjadi unique key nya untuk jadi patokannya.**

### Step 7: Running Test Setelah Implementasi Selesai
Setelah seluruh pengerjaan feature selesai, **wajib menjalankan semua testing yang relevan untuk feature yang dibuat**.
- Jalankan test backend yang dibuat untuk feature tersebut.
- Jalankan test frontend yang dibuat untuk feature tersebut.
- Jalankan Real Integrated Test / Playwright yang dibuat untuk feature tersebut.
- Jalankan `npm run typecheck` dan `npm run build` bila ada perubahan `.ts` atau `.vue`.
- **Tidak wajib menjalankan semua test di seluruh proyek**, tetapi wajib memastikan seluruh test yang terkait feature yang dibuat berjalan dan pass.

---

## Aturan per Layer (Ringkasan)

| Layer | Lokasi | Aturan Keras |
|-------|--------|--------------|
| **Controller** | `app/Http/Controllers/` | Hanya meneruskan request ke Service dan return `Inertia::render`. |
| **Service** | `app/Services/` | Wajib validasi bisnis. Param & Return wajib **Entity**. Dilarang import Model/DB. |
| **Repository** | `app/Repositories/` | Satu-satunya layer yang boleh akses Model/DB. Param & Return wajib **Entity/Collection**. |
| **Domain (FE)** | `resources/js/features/[Module]/[Feature]/domain/` | Hanya kontrak interface, DI, model, repository interface, dan UseCase logic. |
| **Data (FE)** | `resources/js/features/[Module]/[Feature]/data/` | Implementasi konkret repo (`.repo_impl.ts`), **satu-satunya lokasi kode Inertia di frontend**, wajib `try-catch`, dan wajib return `Either`. |
| **ViewModel (FE)** | `resources/js/features/[Module]/[Feature]/presentations/viewmodels/` | Menjembatani state UI dan UseCase. **Wajib pakai UseCase, dilarang panggil Inertia langsung**. |
| **Views (FE)** | `resources/js/features/[Module]/[Feature]/presentations/views/` | Komponen Vue untuk UI saja. **Tidak boleh memanggil Inertia langsung**. |

---

## Checklist Akhir (Definition of Done)
- [ ] Dimulai dari Test (TDD).
- [ ] Param dan Response di setiap method Service & Repo menggunakan Entity (bukan array mentah).
- [ ] Migration memiliki index, softdelete, timestamp, dan audit columns lengkap.
- [ ] Tabel tenant-owned menyimpan `company_uuid` secara eksplisit kecuali benar-benar tabel referensi global.
- [ ] Seeder realistis (bukan dummy).
- [ ] Seeder menyiapkan company `sandbox/staging/testing` yang terpisah dari company `production`.
- [ ] Search (all/column), pagination, per-page, & sorting by column (Server-Side).
- [ ] Import/Export (template 2 sheet + upsert logic).
- [ ] Struktur frontend modular mengikuti `resources/js/features/[Module]/[Feature]/`.
- [ ] Implementasi repo frontend bernama `[name].repo_impl.ts`.
- [ ] Usecase frontend bernama `*.usecase.ts`.
- [ ] Repo Impl frontend menerapkan `try-catch` dan return `Either<Failure, T>`.
- [ ] UseCase frontend menjaga kontrak `Either<Failure, T>` dan memakai `try-catch` bila ada orchestration/proses tambahan.
- [ ] ViewModel frontend bernama `[feature].vm.ts`.
- [ ] ViewModel hanya berinteraksi ke UseCase (bukan ke Inertia).
- [ ] Tidak ada import/pemanggilan Inertia di `views` dan `viewmodels`.
- [ ] Semua kode Inertia frontend berada di `data/repositories/*.repo_impl.ts`.
- [ ] Mengimplementasikan **Real Integrated Test** via Playwright di folder `resources/js/tests/` sebagai validasi repositori asli.
- [ ] Setelah implementasi selesai, semua test yang dibuat/terkait untuk feature tersebut sudah dijalankan dan pass.
- [ ] Tidak ada penggunaan jQuery.
