import { expect, test, type Page } from '@playwright/test';

const BASE_URL = '/inventory/master/daftar-nilai';
const API_SETS_URL = '/api/v1/lov/sets';

test.describe('Lookup (Inventory Master) E2E', () => {

    test.beforeEach(async ({ page }) => {
        page.on('pageerror', error => console.log('BROWSER_ERROR:', error.message));

        await page.goto('/login');
        await page.fill('input[name="username"]', 'superadmin');
        await page.fill('input[name="password"]', 'superadmin');
        await page.click('button[type="submit"]');
        await page.waitForURL((url) => !url.pathname.includes('/login'));
        await page.goto(BASE_URL);
    });

    async function waitForTableLoad(page: Page) {
        await page.waitForResponse((resp) =>
            resp.url().includes(API_SETS_URL) &&
            resp.request().method() === 'GET' &&
            resp.status() === 200,
        );
        await expect(page.locator('table')).toBeVisible();
    }

    async function searchLookup(page: Page, keyword: string) {
        const searchInput = page.getByTestId('input-search-lookup');
        await expect(searchInput).toBeVisible();
        // Setup listener SEBELUM fill agar tidak miss debounce response
        const responsePromise = page.waitForResponse((resp) =>
            resp.url().includes(API_SETS_URL) &&
            resp.request().method() === 'GET' &&
            resp.status() === 200,
        { timeout: 15000 });
        await searchInput.fill(keyword);
        await responsePromise;
    }

    async function ensureRowExists(page: Page) {
        if (await page.getByTestId('btn-edit-lookup').count()) return;

        const ts = Date.now();
        await page.getByTestId('btn-create-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-lookup-name').fill(`SETUP ${ts}`);
        await page.getByTestId('input-lookup-program-code').fill(`SETUP-${ts}`);
        await page.getByTestId('btn-submit-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).not.toBeVisible({ timeout: 15000 });
        await expect(page.getByTestId('btn-edit-lookup').first()).toBeVisible({ timeout: 15000 });
    }

    // -----------------------------------------------------------------------
    // 1. Page load
    // -----------------------------------------------------------------------
    test('harus memuat halaman dan tabel lookup', async ({ page }) => {
        const apiRequest = page.waitForResponse((resp) =>
            resp.url().includes(API_SETS_URL) && resp.request().method() === 'GET',
        );
        await page.goto(BASE_URL);
        const response = await apiRequest;
        expect(response.status()).toBe(200);
        await expect(page.locator('table')).toBeVisible();
    });

    // -----------------------------------------------------------------------
    // 2. Header & auth
    // -----------------------------------------------------------------------
    test('request harus membawa header company scope yang valid', async ({ page }) => {
        const apiRequest = page.waitForRequest((req) =>
            req.url().includes(API_SETS_URL) && req.method() === 'GET',
        );
        await page.goto(BASE_URL);
        const request = await apiRequest;
        const headers = request.headers();
        expect(['local', 'sandbox', 'staging', 'testing', 'development', 'dev'])
            .toContain(headers['x-klinik-environment']);
        expect(headers['x-klinik-company-uuid']).toBeTruthy();
    });

    // -----------------------------------------------------------------------
    // 3. Tombol aksi tersedia
    // -----------------------------------------------------------------------
    test('harus menampilkan tombol create, edit, delete, dan view values', async ({ page }) => {
        await waitForTableLoad(page);
        await expect(page.getByTestId('btn-create-lookup')).toBeVisible();
        await ensureRowExists(page);
        await expect(page.getByTestId('btn-edit-lookup').first()).toBeVisible();
        await expect(page.getByTestId('btn-delete-lookup').first()).toBeVisible();
        await expect(page.getByTestId('btn-view-lookup-values').first()).toBeVisible();
    });

    // -----------------------------------------------------------------------
    // 4. Create
    // -----------------------------------------------------------------------
    test('harus bisa membuat lookup baru', async ({ page }) => {
        const ts = Date.now();
        const name = `INV NILAI ${ts}`;
        const code = `INV-NILAI-${ts}`;

        await waitForTableLoad(page);
        await page.getByTestId('btn-create-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).toBeVisible({ timeout: 10000 });

        await page.getByTestId('input-lookup-name').fill(name);
        await page.getByTestId('input-lookup-program-code').fill(code);
        await page.getByTestId('btn-submit-lookup').click();

        await expect(page.getByTestId('input-lookup-name')).not.toBeVisible({ timeout: 15000 });
        await searchLookup(page, code);
        await expect(page.locator('table')).toContainText(name, { timeout: 15000 });
    });

    // -----------------------------------------------------------------------
    // 5. Edit
    // -----------------------------------------------------------------------
    test('harus bisa mengedit lookup yang ada', async ({ page }) => {
        const ts = Date.now();
        const name = `INV EDIT ${ts}`;
        const code = `INV-EDIT-${ts}`;

        await waitForTableLoad(page);

        // Create first
        await page.getByTestId('btn-create-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-lookup-name').fill(name);
        await page.getByTestId('input-lookup-program-code').fill(code);
        await page.getByTestId('btn-submit-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).not.toBeVisible({ timeout: 15000 });

        // Edit
        await searchLookup(page, code);
        const row = page.locator('tr').filter({ hasText: code }).first();
        await row.getByTestId('btn-edit-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-lookup-name').fill(`${name} Updated`);
        await page.getByTestId('btn-submit-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).not.toBeVisible({ timeout: 15000 });

        // Clear input sebelum search kedua agar debounce selalu trigger
        await page.getByTestId('input-search-lookup').fill('');
        await searchLookup(page, code);
        await expect(page.locator('table')).toContainText(`${name} Updated`, { timeout: 15000 });
    });

    // -----------------------------------------------------------------------
    // 6. Delete
    // -----------------------------------------------------------------------
    test('harus bisa menghapus lookup', async ({ page }) => {
        const ts = Date.now();
        const name = `INV DEL ${ts}`;
        const code = `INV-DEL-${ts}`;

        await waitForTableLoad(page);

        // Create first
        await page.getByTestId('btn-create-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-lookup-name').fill(name);
        await page.getByTestId('input-lookup-program-code').fill(code);
        await page.getByTestId('btn-submit-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).not.toBeVisible({ timeout: 15000 });

        // Delete
        await searchLookup(page, code);
        const row = page.locator('tr').filter({ hasText: code }).first();
        await row.getByTestId('btn-delete-lookup').click();
        await expect(page.locator('button:has-text("Ya, Hapus!")')).toBeVisible({ timeout: 10000 });
        await page.locator('button:has-text("Ya, Hapus!")').click();

        await page.reload();
        await waitForTableLoad(page);
        // Clear input dulu sebelum search agar debounce selalu trigger request baru
        const searchAfterReload = page.getByTestId('input-search-lookup');
        await expect(searchAfterReload).toBeVisible();
        await searchAfterReload.fill('');
        await searchLookup(page, code);
        await expect(page.locator('tr').filter({ hasText: code })).toHaveCount(0, { timeout: 15000 });
    });

    // -----------------------------------------------------------------------
    // 7. Full CRUD cycle
    // -----------------------------------------------------------------------
    test('harus bisa menjalankan siklus penuh CRUD lookup', async ({ page }) => {
        const ts = Date.now();
        const name = `PW INV NILAI ${ts}`;
        const code = `PW-INV-${ts}`;

        await waitForTableLoad(page);

        // CREATE
        await page.getByTestId('btn-create-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-lookup-name').fill(name);
        await page.getByTestId('input-lookup-program-code').fill(code);
        await page.getByTestId('btn-submit-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).not.toBeVisible({ timeout: 15000 });

        await searchLookup(page, code);
        await expect(page.locator('table')).toContainText(name, { timeout: 15000 });

        // UPDATE
        let row = page.locator('tr').filter({ hasText: code }).first();
        await row.getByTestId('btn-edit-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-lookup-name').fill(`${name} Updated`);
        await page.getByTestId('btn-submit-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).not.toBeVisible({ timeout: 15000 });

        await searchLookup(page, code);
        await expect(page.locator('table')).toContainText(`${name} Updated`, { timeout: 15000 });

        // DELETE
        row = page.locator('tr').filter({ hasText: code }).first();
        await row.getByTestId('btn-delete-lookup').click();
        await expect(page.locator('button:has-text("Ya, Hapus!")')).toBeVisible({ timeout: 10000 });
        await page.locator('button:has-text("Ya, Hapus!")').click();

        await page.reload();
        await waitForTableLoad(page);
        // Clear input dulu sebelum search agar debounce selalu trigger request baru
        const searchAfterReload = page.getByTestId('input-search-lookup');
        await expect(searchAfterReload).toBeVisible();
        await searchAfterReload.fill('');
        await searchLookup(page, code);
        await expect(page.locator('tr').filter({ hasText: code })).toHaveCount(0, { timeout: 15000 });
    });

    // -----------------------------------------------------------------------
    // 8. Navigate to values
    // -----------------------------------------------------------------------
    test('harus bisa navigasi ke halaman nilai dari tombol view', async ({ page }) => {
        await waitForTableLoad(page);
        await ensureRowExists(page);

        const eyeButton = page.getByTestId('btn-view-lookup-values').first();
        await expect(eyeButton).toBeVisible();
        await eyeButton.click();
        await page.waitForURL(/\/inventory\/master\/daftar-nilai\/.+\/values/);
        await expect(page.locator('body')).toContainText('Nilai Lookup');
    });

    // -----------------------------------------------------------------------
    // 9. Search & filter
    // -----------------------------------------------------------------------
    test('harus bisa filter pencarian berdasarkan kolom', async ({ page }) => {
        const ts = Date.now();
        const name = `FILTER INV ${ts}`;
        const code = `FILTER-INV-${ts}`;

        await waitForTableLoad(page);
        await page.getByTestId('btn-create-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-lookup-name').fill(name);
        await page.getByTestId('input-lookup-program-code').fill(code);
        await page.getByTestId('btn-submit-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).not.toBeVisible({ timeout: 15000 });

        const columnSelect = page.getByTestId('select-search-column-lookup');
        await expect(columnSelect).toBeVisible();
        // Select2 custom component - klik container lalu pilih option dari dropdown
        await columnSelect.click();
        await page.locator('.vs__dropdown-option', { hasText: 'Kode Program' }).first().click();
        await searchLookup(page, code);

        await expect(page.locator('table')).toContainText(code, { timeout: 15000 });
        await expect(page.locator('table')).toContainText(name, { timeout: 15000 });
    });

    // -----------------------------------------------------------------------
    // 10. Export & template
    // -----------------------------------------------------------------------
    test('harus bisa ekspor data dan unduh template import', async ({ page }) => {
        await waitForTableLoad(page);

        // Export menggunakan blob download via JS - intercept API response
        const exportResponse = page.waitForResponse((resp) =>
            resp.url().includes('/api/v1/lov/sets/export') && resp.request().method() === 'GET'
        );
        await page.getByTestId('btn-export-lookup').click();
        const exportResp = await exportResponse;
        expect(exportResp.status()).toBe(200);

        await page.getByTestId('btn-import-menu-lookup').click();
        const templateResponse = page.waitForResponse((resp) =>
            resp.url().includes('/api/v1/lov/sets/import/template') && resp.request().method() === 'GET'
        );
        await page.getByTestId('btn-import-template-lookup').click();
        const templateResp = await templateResponse;
        expect(templateResp.status()).toBe(200);
    });
});
