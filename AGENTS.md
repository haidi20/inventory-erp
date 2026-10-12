# AGENTS.md

File ini adalah panduan wajib untuk coding agent di seluruh repository. File
`AGENTS.md` di dalam folder modul hanya memperkaya atau memperketat aturan di
sini untuk kode di folder tersebut.

## Konteks Proyek

- Proyek ini adalah aplikasi klinik + ERP berbasis Laravel, Inertia, Vue, dan
  Pinia.
- Kode aplikasi utama ada di `src/`. Jalankan perintah Laravel, Composer, npm,
  test, dan linter dari direktori `src/`.
- Struktur target bersifat module-first di `src/modules/{Module}/{SubModule}`.
- Dokumen keputusan arsitektur ada di
  `src/guides/modularization/_plan.md`; status pekerjaan, checklist, dan todo
  ada di `src/guides/modularization/_progress.md`.

## Alur Kerja Agent

- Jangan melakukan `git commit` atau `git push` tanpa instruksi eksplisit dari
  user.
- Sebelum mengubah kode, periksa status repository dan baca file terkait;
  jangan menebak struktur dari nama file saja.
- Kerjakan perubahan secara minimal dan terarah. Jangan refactor luas,
  mengganti nama file, atau memperbaiki bug tidak terkait tanpa permintaan.
- Update `_progress.md` hanya untuk status kerja, checklist, dan hasil
  validasi; keputusan arsitektur baru dicatat di `_plan.md`.
- Jika perlu mengubah endpoint, cek dulu keputusan freeze endpoint di
  `_plan.md` sebelum mengganti URL.

## Arsitektur

- Ikuti pola `Controller -> Service -> Repository -> Model`.
- `Entity` dipakai sebagai DTO/kontrak data antar layer, bukan pengganti Model
  Eloquent.
- Service tidak boleh langsung mengakses atau mengembalikan Eloquent Model ke
  luar; konversikan ke Entity/DTO.
- Controller hanya orkestrasi HTTP, validasi, authorization, dan response.
- Repository adalah pemilik akses data dan transaksi database.
- Binding `RepositoryInterface -> Repository` dideklarasikan di service
  container/provider, bukan di controller.
- Larang import Model, Repository, atau Service lintas modul secara langsung
  kecuali melalui Service publik milik modul pemilik domain dan berbasis
  Entity/DTO.
- Tidak boleh menambahkan namespace legacy `App\Services\`,
  `App\Repositories\`, atau `App\Entities\` di kode modul.

## Model, Scope, Dan Database

- Gunakan UUID sebagai identifier internal untuk tabel baru dan relasi baru.
- Model domain multi-tenant wajib memperhatikan `company_uuid` dan
  `branch_uuid`; gunakan scope/trait yang sudah tersedia.
- Superadmin boleh membypass scope company/branch hanya jika fiturnya memang
  dimaksudkan untuk akses lintas scope; pengguna normal wajib tetap terisolasi.
- Tulis database multi-step di Repository dengan transaksi eksplisit:
  `DB::beginTransaction()`, `try`, `DB::commit()`, lalu `DB::rollBack(); throw
  $e;` pada `catch`.
- Jangan gunakan closure `DB::transaction(function () { ... })` karena pola
  proyek sudah memakai transaksi eksplisit.
- Migrasi harus ditempatkan pada folder migration modul pemilik domain dan
  didaftarkan melalui module provider.

## Frontend

- Vue component tidak boleh menyimpan business logic. Fetch, transformasi,
  validasi bisnis, dan side effect ditempatkan di VM/state/store sesuai pola
  yang sudah ada.
- Nama `*.vue`, `*.vm.ts`, dan `*.state.ts` untuk satu fitur harus sinkron.
- Setiap view dengan endpoint/controller sendiri wajib memiliki VM-nya sendiri.
- Jalankan typecheck setelah mengubah file TypeScript atau Vue.

## Testing

- Prioritaskan test fokus pada area yang diubah; jangan menjalankan seluruh
  suite test tanpa instruksi eksplisit.
- Gunakan TDD untuk bug fix atau perilaku baru: tulis/ubah test yang membuktikan
  masalah, perbaiki implementasi, lalu jalankan test tersebut.
- Test fitur wajib mencakup happy path, validation error, not found,
  unauthorized, duplicate, dan isolation company/branch bila relevan.
- Untuk perubahan UI yang punya alur nyata, pertimbangkan test Playwright; guna
  fixture autentikasi yang sudah ada, bukan login manual di dalam test kecuali
  skenarionya memang menguji login.

## Validasi Minimum

Pilih validasi sesuai area perubahan, jalankan dari `src/`:

- PHP: `vendor/bin/pint --test <file-yang-diubah>` dan
  `vendor/bin/phpstan analyse --memory-limit=1G` bila perlu.
- Boundary: `composer ci:module-boundaries`.
- Frontend: `npm run typecheck`.
- Unit/feature frontend: `npx vitest run <file-test>` atau
  `npm run test -- <filter>` sesuai konfigurasi yang tersedia.
- E2E: `npm run e2e -- <file-spec>` untuk file yang relevan.

## Git Dan Commit

- Jangan commit otomatis. Commit hanya dilakukan ketika user secara eksplisit
  meminta commit/push.
- Jika user meminta commit/push, kelompokkan perubahan secara granular
  berdasarkan domain atau intent, bukan satu commit campuran.
- Gunakan pesan commit Bahasa Indonesia dengan format imperative dan body yang
  menjelaskan konteks, perilaku sebelum/sesudah, file terdampak, dan hasil
  validasi.

## Dual Repository Push

Project ini memakai dua repositori dengan aturan push yang berbeda:

| Remote | URL | Branch | Visibilitas | History |
|---|---|---|---|---|
| `origin` | `https://github.com/haidi20/klinik.git` | `haidi` | Private | Lengkap |
| `public` | `https://github.com/haidi20/inventory-erp.git` | `main` | Public | Bersih (orphan) |

- Push default ke `origin haidi` dengan full history. Semua data boleh ada
  di sini.
- Push ke repo public HANYA melalui branch lokal `clean-public`. Branch ini
  dibuat dengan `git checkout --orphan clean-public` sehingga tidak membawa
  history lama.
- SEBELUM push ke `public`, WAJIB verifikasi file sensitif berikut tidak ada:
  - `ninesoft.sql` (dump ERP production: NPWP, alamat, telepon, rekening bank)
  - `src/database/data/products-main.json` (produk real dari database main)
  - Data pasien real (nama, NIK, no HP, alamat) di file mana pun
  - Kredensial hardcoded (password, API key, token)
  - File `.env` (hanya `.env.example` yang boleh)
- Untuk sinkronisasi perubahan baru ke repo public:
  1. Commit dan push ke `origin haidi` terlebih dahulu.
  2. `git checkout clean-public`.
  3. Terapkan perubahan yang sama, KECUALI file sensitif di atas.
  4. Commit di `clean-public`.
  5. `git push public clean-public:main`.
  6. `git checkout haidi` untuk kembali bekerja.
- Jika file baru yang sensitif ditambahkan ke `haidi`, TIDAK BOLEH di-copy
  ke `clean-public`. Gunakan data dummy atau env variable.
- Repo public mendapat GitHub Actions unlimited; repo private terkena limit
  2.000 menit/bulan. Prioritaskan CI di repo public.

