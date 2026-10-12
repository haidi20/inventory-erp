---
name: klinikui
description: Standar frontend modular untuk fitur baru di proyek Klinik 2026. Gunakan saat Codex diminta membuat repo implementation, use case, viewmodel, view Vue, atau test frontend di Inertia + Vue 3 + TypeScript agar patuh pada struktur modular, Either pattern, TDD, server-side listing/search/pagination, dan validasi melalui Vitest, Playwright, typecheck, serta build.
---

# Klinik UI (Klinik 2026)

Gunakan skill ini saat task dominan frontend.

---

> ## ⚠️ ATURAN TIDAK BISA DIKECUALIKAN: SETIAP FILE = SATU FILE TEST
>
> **Setiap file yang dibuat WAJIB memiliki file test pasangannya.** Tidak ada pengecualian.
>
> | Layer | Format test |
> |-------|-------------|
> | `data/repositories/*.repo_impl.ts` | `*.repo_impl.spec.ts` — Vitest (unit + integration) |
> | `domain/usecases/*.usecase.ts` | `*.usecase.spec.ts` — Vitest (mock repo) |
> | `presentations/viewmodels/*.vm.ts` | `*.vm.spec.ts` — Vitest (mock use case) |
> | `presentations/views/*.vue` | `*.view.spec.ts` — **Playwright E2E** (browser nyata) |
>
> **File view (.vue) HANYA ditest dengan E2E Playwright** — bukan Vitest/jsdom.  
> Artinya test view = login nyata + navigasi nyata + interaksi browser nyata.  
> Jika file view dibuat tanpa `*.view.spec.ts` Playwright → dianggap **belum selesai**.

---

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

1. **Setiap file yang dibuat langsung diikuti file testnya — tidak boleh ditunda.**
   - `repo_impl.ts` dibuat → langsung buat `repo_impl.spec.ts`
   - `usecase.ts` dibuat → langsung buat `usecase.spec.ts`
   - `vm.ts` dibuat → langsung buat `vm.spec.ts`
   - `*.vue` dibuat → langsung buat `*.view.spec.ts` dengan **Playwright E2E**
   
   Minimal cover per test:
   - happy path
   - validation error
   - unauthorized
   - data not found
   - duplicate case bila ada form/import

2. Repo impl adalah satu-satunya layer HTTP.
- boleh Axios atau Inertia
- wajib `try-catch`
- wajib return `Either<Failure, T>`

3. ViewModel tidak boleh memanggil Inertia langsung.
- ViewModel hanya panggil use case
- kelola state UI, filter, sort, pagination, loading, form, dan permission

4. View hanya untuk binding UI.
- jangan taruh logika bisnis besar di `.vue`

5. Listing wajib server-side.
- search semua field atau per kolom
- sort server-side
- pagination server-side
- per-page server-side

## Wajib: Test di Setiap Layer

**Setiap file implementasi WAJIB memiliki file test pasangannya.** Tidak ada layer yang boleh skip.

### Struktur Test (mirror dari struktur fitur)

```text
resources/js/tests/[module]/[feature]/
├── data/repositories/
│   └── [feature].repo_impl.spec.ts      ← test untuk repo_impl.ts
├── domain/usecases/
│   ├── fetch-[feature].usecase.spec.ts   ← test untuk fetch usecase
│   └── save-[feature].usecase.spec.ts    ← test untuk save/delete usecase
├── presentation/
│   ├── viewmodels/
│   │   └── [feature].vm.spec.ts          ← test untuk viewmodel
│   └── views/
│       └── [feature].view.spec.ts        ← E2E Playwright test untuk view
```

### Cakupan Test Wajib per Layer

#### `repo_impl.spec.ts` — Vitest, integration dengan API nyata
```typescript
// Minimal cover:
it('harus bisa fetch data (paginate/list)')
it('harus bisa save data baru (create)')
it('harus bisa update data (edit)')
it('harus bisa delete data')
it('harus return Left(Failure) saat 401 Unauthorized')
it('harus return Left(ValidationFailure) saat 422')
it('harus bisa export') // bila ada
it('harus bisa import') // bila ada
```

#### `*.usecase.spec.ts` — Vitest, mock repository
```typescript
// Minimal cover:
it('harus memanggil repository dengan data yang benar')
it('harus return Right saat repository sukses')
it('harus return Left saat repository gagal')
```

#### `*.vm.spec.ts` — Vitest, mock use case
```typescript
// Minimal cover:
it('harus fetch data saat init')
it('harus update state isLoading dengan benar')
it('harus set errors ke form saat validasi gagal')
it('harus closeForm dan reset state setelah save berhasil')
it('harus trigger deleteUser dengan konfirmasi')
it('harus filter/search dengan debounce')
```

#### `*.view.spec.ts` — Playwright E2E (butuh server aktif)

Test view **wajib** menggunakan Playwright dengan konsep E2E murni:
- Login sebagai user nyata via `beforeEach`
- Navigasi ke halaman target via `page.goto()`
- Interaksi dengan elemen nyata di browser (klik, fill, check)
- Verifikasi melalui response API nyata atau perubahan DOM nyata
- Gunakan `data-testid` untuk selector — tidak boleh pakai class CSS atau tag HTML

**Struktur file wajib:**
```typescript
import { test, expect, type Page } from '@playwright/test';

test.describe('[Feature] View E2E', () => {

    // ── Setup: login sekali sebelum setiap test ────────────────────────────
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'owner@klinik.local');
        await page.fill('input[name="password"]', 'klinik123');
        await page.click('button[type="submit"]');
        await page.waitForURL((url) => !url.pathname.includes('/login'));
        await page.goto('/[route]');
        await expect(page.getByTestId('btn-create-[feature]')).toBeVisible({ timeout: 20000 });
    });

    // ── Helper reusable (search, dll) ─────────────────────────────────────
    async function search[Feature](page: Page, keyword: string) {
        const input = page.getByTestId('input-search-[feature]');
        await expect(input).toBeVisible();
        await input.fill(keyword);
        await page.waitForTimeout(1000); // tunggu debounce
    }

    // ── 1. Initial load ───────────────────────────────────────────────────
    test('harus memuat data pertama kali dengan token valid', async ({ page }) => {
        const apiRequest = page.waitForResponse((resp) =>
            resp.url().includes('/api/v1/[route]') && resp.request().method() === 'GET'
        );
        await page.goto('/[route]');
        const response = await apiRequest;
        expect(response.status()).toBe(200);
        await expect(page.locator('table')).toBeVisible();
    });

    // ── 2. Unauthorized → redirect login ──────────────────────────────────
    test('jika sesi berakhir harus redirect ke login', async ({ page }) => {
        await page.route('**/api/v1/[route]**', async (route) => {
            await route.fulfill({
                status: 401,
                contentType: 'application/json',
                body: JSON.stringify({ message: 'Unauthorized' }),
            });
        });
        await page.goto('/[route]');
        await page.waitForURL(/\/login$/, { timeout: 15000 });
    });

    // ── 3. Loading indicator ──────────────────────────────────────────────
    test('harus tampil loading indicator saat filter', async ({ page }) => {
        await page.route('**/api/v1/[route]*', async (route) => {
            await new Promise(f => setTimeout(f, 1000));
            await route.continue();
        });
        const input = page.getByTestId('input-search-[feature]');
        await input.fill('test-' + Date.now());
        await expect(page.getByTestId('loading-indicator')).toBeVisible({ timeout: 5000 });
    });

    // ── 4. CRUD lengkap ───────────────────────────────────────────────────
    test('harus bisa CRUD lengkap: create → search → edit → delete', async ({ page }) => {
        const ts = Date.now();
        const testName = `PW Test ${ts}`;

        // CREATE
        await page.getByTestId('btn-create-[feature]').click();
        await expect(page.getByTestId('input-[feature]-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-[feature]-name').fill(testName);
        // ... isi field lain ...
        await page.getByTestId('btn-submit-[feature]').click();
        await expect(page.getByTestId('input-[feature]-name')).not.toBeVisible({ timeout: 15000 });

        // READ (search)
        await search[Feature](page, testName);
        await expect(page.locator('table')).toContainText(testName, { timeout: 15000 });

        // UPDATE
        const row = page.locator('tr').filter({ hasText: testName }).first();
        await row.getByTestId('btn-edit-[feature]').click();
        await page.getByTestId('input-[feature]-name').fill(`${testName} Updated`);
        await page.getByTestId('btn-submit-[feature]').click();
        await expect(page.locator('table')).toContainText(`${testName} Updated`, { timeout: 15000 });

        // DELETE
        await page.locator('tr').filter({ hasText: `${testName} Updated` }).first()
            .getByTestId('btn-delete-[feature]').click();
        await page.locator('button:has-text("Ya, Hapus!")').click();
        await expect(page.locator('table')).not.toContainText(testName, { timeout: 15000 });
    });

    // ── 5. Filter per kolom ───────────────────────────────────────────────
    test('harus bisa filter berdasarkan kolom', async ({ page }) => {
        const columnSelect = page.getByTestId('select-search-column-[feature]');
        await columnSelect.selectOption('name'); // atau kolom spesifik
        await search[Feature](page, 'kata kunci yang pasti ada');
        await expect(page.locator('table')).toContainText('kata kunci yang pasti ada');
    });

    // ── 6. Per-page & pagination ──────────────────────────────────────────
    test('harus bisa ubah per-page dan pindah halaman', async ({ page }) => {
        const perPage = page.getByTestId('select-per-page-[feature]');
        await Promise.all([
            page.waitForResponse((resp) =>
                resp.url().includes('/api/v1/[route]') &&
                resp.url().includes('per_page=10') &&
                resp.status() === 200
            ),
            perPage.selectOption('10'),
        ]);
        const page2 = page.locator('.pagination-link').filter({ hasText: '2' }).first();
        await expect(page2).toBeVisible({ timeout: 15000 });
        await page2.click();
        await expect(page.locator('.pagination-link.active')).toContainText('2');
    });

    // ── 7. Sorting ────────────────────────────────────────────────────────
    test('harus bisa sorting via klik header kolom', async ({ page }) => {
        await Promise.all([
            page.waitForResponse((resp) =>
                resp.url().includes('sort_by=name') &&
                resp.url().includes('sort_direction=asc') &&
                resp.status() === 200
            ),
            page.getByTestId('sort-[feature]-name').click(),
        ]);
        await Promise.all([
            page.waitForResponse((resp) =>
                resp.url().includes('sort_direction=desc') && resp.status() === 200
            ),
            page.getByTestId('sort-[feature]-name').click(),
        ]);
    });

    // ── 8. Export & template (bila ada) ──────────────────────────────────
    test('harus bisa export dan download template', async ({ page }) => {
        const exportPromise = page.waitForEvent('download');
        await page.getByTestId('btn-export-[feature]').click();
        const dl = await exportPromise;
        expect(dl.suggestedFilename()).toBe('[feature]-export.xlsx');
    });
});
```

**Teknik E2E penting yang wajib digunakan:**

| Teknik | Kapan | Contoh |
|--------|-------|--------|
| `page.route()` | Mock response API (401, 422, delay) | Simulate session expired |
| `page.waitForResponse()` | Verifikasi parameter API yang dikirim | Cek `sort_by=name` di URL |
| `page.waitForURL()` | Tunggu redirect | Setelah 401 → `/login` |
| `page.waitForEvent('download')` | Verifikasi file diunduh | Export xlsx |
| `Promise.all([waitFor..., click()])` | Aksi + verifikasi serentak | Klik sort sambil tunggu API |
| `evaluateAll()` | Inspect DOM computed state | Cek berapa checkbox ter-centang |
| `boundingBox()` | Verifikasi ukuran/posisi elemen | Modal tidak melebihi viewport |
| `getComputedStyle()` via `evaluate()` | Cek CSS computed | `overflow-y: auto` |
| `data-testid` | **Satu-satunya selector yang boleh** | `getByTestId('btn-submit-user')` |

**Aturan `data-testid`:**
- Setiap elemen interaktif di view **wajib** punya `data-testid`
- Format: `[aksi]-[feature]-[field]` → `btn-create-user`, `input-user-name`, `sort-user-email`
- Jangan pakai class CSS atau tag HTML sebagai selector di test

### Aturan Penamaan File Test

| Layer | Lokasi | Nama file |
|-------|--------|-----------|
| Repo impl | `tests/[mod]/[feat]/data/repositories/` | `[feat].repo_impl.spec.ts` |
| Use case fetch | `tests/[mod]/[feat]/domain/usecases/` | `fetch-[feat].usecase.spec.ts` |
| Use case save | `tests/[mod]/[feat]/domain/usecases/` | `save-[feat].usecase.spec.ts` |
| ViewModel | `tests/[mod]/[feat]/presentation/viewmodels/` | `[feat].vm.spec.ts` |
| View (E2E) | `tests/[mod]/[feat]/presentation/views/` | `[feat].view.spec.ts` |

> **Referensi lengkap:** lihat `resources/js/tests/settings/user/` sebagai contoh implementasi semua layer test.

---

## Checklist Frontend

> Aturan: buat implementasi → **langsung** buat testnya, jangan tunggu akhir.

- [ ] buat repo impl → **langsung buat `repo_impl.spec.ts` (Vitest)**
- [ ] buat use case (fetch & save) → **langsung buat `*.usecase.spec.ts` (Vitest)**
- [ ] buat viewmodel → **langsung buat `*.vm.spec.ts` (Vitest)**
- [ ] buat view `.vue` → **langsung buat `*.view.spec.ts` (Playwright E2E — bukan Vitest)**
- [ ] sambungkan state filter dan pagination
- [ ] sambungkan import/export bila dibutuhkan
- [ ] pastikan permission UI benar
- [ ] validasi akhir: typecheck → build → vitest → playwright (semua 100% pass)

## Validasi Akhir

Jalankan **semua langkah berikut** sebelum dianggap selesai:

### 1. TypeScript Check — semua `.ts` dan `.vue`
```bash
npm run typecheck
```
`tsconfig.json` sudah dikonfigurasi agar Vue SFC (`.vue`) ikut di-check.  
**Pastikan 0 error** — termasuk file yang tidak disentuh tapi terdampak perubahan type.

Bila ada file `.vue` atau `.ts` yang error karena perubahan model/interface (misal rename field, ganti tipe),  
**wajib fix semuanya** sebelum lanjut ke langkah berikutnya.

### 2. Build
```bash
npm run build
```
Harus selesai tanpa error. Warning boleh, error tidak boleh.

### 3. Unit & Integration Test (Vitest)
```bash
npm run test
```
Cakupan minimal:
- happy path
- validation error (422)
- unauthorized (401)
- data not found
- duplicate case bila ada form/import

**Target: 100% pass.** Bila ada test yang fail karena perubahan field (misal `role_uuid` → `role_uuids`),  
update test-nya agar sesuai kontrak API terbaru.

### 4. E2E Test (Playwright)
```bash
npx playwright test
```
Dijalankan **terpisah** karena butuh browser + dev/prod server aktif.  
Minimal satu test per fitur baru: CRUD, filter, validasi form.

---

> **Urutan wajib:** typecheck → build → vitest → playwright  
> Jangan lompat langkah. Jika typecheck fail, jangan jalankan build dulu.

### Perilaku Wajib saat Ada Issue

**Jangan berhenti dan lapor** — langsung perbaiki semua issue sampai 100% pass:

| Issue | Tindakan |
|-------|----------|
| TypeScript error di file yang tidak disentuh | Buka file itu, fix type-nya |
| Test fail karena field rename | Update test sesuai kontrak baru |
| Build error karena import path salah | Fix import |
| Playwright timeout | Tambah `timeout` parameter, atau fix selector |
| 422 validation error di integration test | Sesuaikan payload test dengan validasi terbaru |
| File implementasi tidak punya test | Buat file test pasangannya **sekarang juga**, bukan nanti |
| File `.vue` tidak punya `*.view.spec.ts` | Buat Playwright E2E test — Vitest tidak cukup untuk view |
| Test ada tapi tidak cover kasus penting | Tambahkan test case yang kurang |

Baru lapor hasilnya setelah **semua hijau dan semua layer punya test**.

## Info Icon + Help Drawer Pattern (Setiap Form)

Setiap form di Vue **wajib** memiliki icon informasi (i) yang membuka help drawer dari sisi kanan. UX pattern mengikuti Upwork job description panel: slide in dari kanan, overlay fade, dengan step-by-step guide dan keyboard shortcuts.

### Implementasi Pattern

**1. Import InfoIcon component:**
```vue
<script setup lang="ts">
import { ref } from 'vue';
import InfoIcon from '@/js/Components/common/InfoIcon.vue';

const showHelp = ref(false);
const toggleHelp = () => {
    showHelp.value = !showHelp.value;
};
</script>
```

**2. Header dengan Info Icon:**
```vue
<div class="modal-header">
    <div style="display: flex; align-items: center; gap: 10px;">
        <h4>{{ title }}</h4>
        <InfoIcon @click="toggleHelp" title="Bantuan (F1)" />
    </div>
    <button type="button" class="modal-close" @click="closeForm">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
```

**3. Help Drawer (Slide dari Kanan):**
```vue
<!-- Fade Overlay -->
<Transition name="fade">
    <div v-if="showHelp" class="drawer-overlay" @click="showHelp = false"></div>
</Transition>

<!-- Help Drawer dari Kanan -->
<Transition name="slide-right">
    <div v-if="showHelp" class="help-drawer">
        <div class="help-drawer-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="help-drawer-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div>
                    <h4 style="margin:0; font-size: 16px; font-weight: 800; color: #1e293b;">Bantuan: [Feature Name]</h4>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Panduan Penggunaan & Shortcut</div>
                </div>
            </div>
            <button type="button" class="help-drawer-close" @click="showHelp = false">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <div class="help-drawer-body">
            <!-- Intro Deskripsi -->
            <p style="font-size: 13.5px; color: #334155; margin-bottom: 24px; line-height: 1.7; 
                      background: #f8fafc; padding: 16px; border-radius: 12px; border-left: 4px solid #cbd5e1;">
                Penjelasan singkat feature ini.
            </p>

            <!-- Langkah-langkah -->
            <div style="margin-bottom: 28px;">
                <h6 class="help-section-title">
                    <i class="fa-solid fa-list-check"></i>Langkah-langkah:
                </h6>
                <div class="step-list">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-text">Step pertama...</div>
                    </div>
                    <!-- tambah lebih banyak steps -->
                </div>
            </div>

            <!-- Keyboard Shortcuts -->
            <div style="margin-bottom: 28px;">
                <h6 class="help-section-title">
                    <i class="fa-solid fa-keyboard"></i>Keyboard Shortcuts:
                </h6>
                <div class="shortcut-list">
                    <div class="shortcut-item">
                        <kbd>F1</kbd>
                        <span>Buka bantuan ini</span>
                    </div>
                    <div class="shortcut-item">
                        <kbd>Ctrl + S</kbd>
                        <span>Simpan form</span>
                    </div>
                    <div class="shortcut-item">
                        <kbd>Esc</kbd>
                        <span>Tutup form</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</Transition>
```

### CSS Classes (dari modal-forms.css)
Pastikan style drawer sudah ada di `resources/css/modal-forms.css`:
```css
.help-drawer {
    position: fixed;
    right: 0;
    top: 0;
    width: 100%;
    max-width: 380px;
    height: 100vh;
    background: white;
    box-shadow: -2px 0 12px rgba(0,0,0,0.15);
    z-index: 1001;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.help-drawer-header {
    padding: 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}

.help-drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
}

.drawer-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.slide-right-enter-active, .slide-right-leave-active {
    transition: transform 0.3s ease;
}

.slide-right-enter-from {
    transform: translateX(100%);
}

.slide-right-leave-to {
    transform: translateX(100%);
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
```

### Keyboard Shortcuts Integration
Tambah F1 ke keyboard handler:
```typescript
const { f1 } = useMagicKeys({ passive: false });

whenever(f1, () => {
    toggleHelp();
});
```

### Referensi Implementasi
- **Lihat:** `resources/js/features/Settings/Role/presentations/views/Partials/RoleForm.vue` untuk contoh lengkap
- **InfoIcon Component:** `resources/js/Components/common/InfoIcon.vue`
- **Styles:** `resources/css/modal-forms.css`

---

## Referensi Proyek

- [`references/project-guides.md`](references/project-guides.md)
