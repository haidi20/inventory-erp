---
name: klinikui
description: Standar frontend modular untuk fitur baru di proyek Klinik 2026. Gunakan saat Codex diminta membuat repo implementation, use case, viewmodel, view Vue, atau test frontend di Inertia + Vue 3 + TypeScript agar patuh pada struktur modular, Either pattern, TDD, keyboard shortcuts, informative help drawers, server-side listing/search/pagination, dan validasi melalui Vitest, Playwright, typecheck, serta build.
---

# Klinik UI (Klinik 2026)

Gunakan skill ini saat task dominan frontend.

## Struktur Modular Wajib

Gunakan struktur:

```text
resources/js/features/[Module]/[Feature]/
```

Urutan kerja:
- `data/repositories/*.repo_impl.ts`
- `domain/repositories/*.repository.ts`
- `domain/usecases/*.usecase.ts`
- `presentations/viewmodels/*.vm.ts`
- `presentations/views/*.vue`

## Aturan Wajib

0. **Test Mandatory (Wajib)**:
   - Setiap file **WAJIB** memiliki file test pendamping di folder `resources/js/tests/`.
   - **Repository Implementation**: Test integrasi/unit untuk memastikan mapping API benar.
   - **UseCase**: Unit test untuk logika bisnis dan flow `Either`.
   - **ViewModel**: Unit test untuk state management, input handling, dan interaksi usecase.
   - **View (Vue)**: Component test atau E2E test untuk memastikan binding dan event UI berjalan.

1. **Konvensi Bahasa (PENTING)**:
   - **Tampilan User (UI)**: Gunakan Bahasa Indonesia (label, placeholder, pesan error, notifikasi).
   - **Kode (Code)**: Gunakan Bahasa Inggris (nama variabel, fungsi, class, komentar kode, database kolom).

2. **Standard UX Klinik (Keyboard Shortcuts & Bantuan)**:
Setiap fitur (terutama Form) **WAJIB** menerapkan:
   - **Keyboard Shortcuts**: Gunakan `@vueuse/core` (`useMagicKeys`, `whenever`).
     - `Ctrl + S`: Simpan/Submit (wajib `e.preventDefault()`).
     - `Esc`: Tutup/Batal.
     - `F1`: Buka Bantuan.
   - **Informasi/Bantuan**:
     - Gunakan `InfoIcon` dari `@/js/Components/common/InfoIcon.vue` di header.
     - Gunakan **Help Drawer** (Teleport ke body) yang berisi deskripsi, langkah-langkah, dan daftar shortcut.

3. **Mulai dari test frontend (TDD)**.
Minimal pertimbangkan:
- happy path
- validation error
- unauthorized
- data not found
- duplicate case bila ada form/import

3. **Repo impl adalah satu-satunya layer HTTP**.
- boleh Axios atau Inertia
- wajib `try-catch`
- wajib return `Either<Failure, T>`

4. **ViewModel tidak boleh memanggil Inertia langsung**.
- ViewModel hanya panggil use case
- kelola state UI, filter, sort, pagination, loading, form, dan permission

5. **View hanya untuk binding UI**.
- jangan taruh logika bisnis besar di `.vue`

6. **Listing wajib server-side**.
- search semua field atau per kolom
- sort server-side
- pagination server-side
- per-page server-side

## Checklist Frontend

- buat repo interface dan repo impl
- buat use case
- buat viewmodel
- buat view
- sambungkan state filter dan pagination
- sambungkan import/export bila dibutuhkan
- pastikan permission UI benar

## Validasi Akhir

Jalankan:
- `npm run typecheck`
- `npm run build`
- Vitest untuk unit/integration test
- Playwright untuk real integrated test

## Referensi Proyek

- [`references/project-guides.md`](references/project-guides.md)
