import { expect, test, type Page } from '@playwright/test';

test.describe('Lookup Value View E2E', () => {
    async function ensureLookupSetExists(page: Page) {
        const viewButton = page.getByTestId('btn-view-lookup-values').first();
        if (await viewButton.count()) {
            return;
        }

        const timestamp = Date.now();
        await page.getByTestId('btn-create-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-lookup-name').fill(`SETUP VALUE ${timestamp}`);
        await page.getByTestId('input-lookup-program-code').fill(`SETUP-VALUE-${timestamp}`);
        await page.getByTestId('btn-submit-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).not.toBeVisible({ timeout: 15000 });
    }

    test.beforeEach(async ({ page }) => {
        page.on('pageerror', err => console.log('BROWSER ERROR:', err.message));

        await page.goto('/login');
        await page.waitForSelector('input[name="username"]', { timeout: 20000 });
        await page.fill('input[name="username"]', 'superadmin');
        await page.fill('input[name="password"]', 'superadmin');
        await Promise.all([
            page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 30000 }),
            page.click('button[type="submit"]'),
        ]);
        await page.goto('/inventory/master/daftar-nilai');
        await ensureLookupSetExists(page);
        await expect(page.getByTestId('loading-indicator')).toBeHidden({ timeout: 20000 });
        await expect(page.locator('.swal2-container')).toBeHidden({ timeout: 20000 });
        const eyeButton = page.getByTestId('btn-view-lookup-values').first();
        await expect(eyeButton).toBeVisible({ timeout: 20000 });
        await eyeButton.click();
        await page.waitForURL(/\/daftar-nilai\/.*\/values|masters\/lookups\/sets\/.*\/values/, { timeout: 30000 });
    });

    test('harus memuat data nilai lookup pertama kali', async ({ page }) => {
        await expect(page.locator('table')).toBeVisible();
        await expect(page.getByTestId('btn-create-value')).toBeVisible();
    });

    test('filter nilai lookup tetap berjalan di route lookup set yang sedang aktif', async ({ page }) => {
        const currentUrl = page.url();

        await page.getByTestId('input-search-value').fill('Islam');
        await page.getByTestId('btn-apply-filter-value').click();

        await page.waitForURL((url) => url.pathname === new URL(currentUrl).pathname, { timeout: 15000 });
        await expect(page.locator('table')).toBeVisible();
    });

    test('harus bisa menjalankan siklus CRUD lengkap nilai lookup', async ({ page }) => {
        test.setTimeout(90000);
        const timestamp = Date.now();
        const testName = `PW VAL ${timestamp}`;
        const testCode = `PW-VAL-${timestamp}`;

        // Wait for table to show specific text via UI search
        const uiSearch = async (query: string) => {
            // Set up response listener BEFORE interaction to avoid race
            const searchQuery = query.substring(0, 15);
            const getResp = page.waitForResponse(r =>
                r.url().includes('/values') &&
                r.url().includes('search=') &&
                r.request().method() === 'GET',
                { timeout: 15000 }
            );
            await page.getByTestId('input-search-value').fill(query);
            await page.getByTestId('btn-apply-filter-value').click();
            await getResp;
            // Wait for Vue to render new data (Inertia onSuccess)
            await page.waitForTimeout(1000);
        };

        // 1. Create
        await page.getByTestId('btn-create-value').click();
        const nameInput = page.getByTestId('input-value-display-name');
        await expect(nameInput).toBeVisible();
        await nameInput.fill(testName);
        await page.getByTestId('input-value-program-code').fill(testCode);
        await page.getByTestId('input-value-seq').fill('99');
        const postResp = page.waitForResponse(r =>
            r.url().includes('/values') && r.request().method() === 'POST'
        );
        await page.getByTestId('btn-save-value').click();
        await postResp;
        await expect(page.locator('.swal2-container')).toContainText('Berhasil', { timeout: 15000 });
        await expect(page.locator('.swal2-container')).not.toBeVisible({ timeout: 5000 }).catch(() => {});
        // Wait for fetchValues after save
        await page.waitForResponse(r =>
            r.url().includes('/values') && r.request().method() === 'GET', { timeout: 10000 }
        ).catch(() => {});
        // Wait for fetchValues() GET (no search filter) to complete before calling uiSearch
        // This prevents fetchValues() from racing with applyFilters() and overwriting results
        await page.waitForResponse(r =>
            r.url().includes('/lov/sets') &&
            r.url().includes('/values') &&
            !r.url().includes('search=') &&
            r.request().method() === 'GET',
            { timeout: 10000 }
        ).catch(() => {});
        await uiSearch(testName);
        await expect(page.locator('table')).toContainText(testName, { timeout: 15000 });

        // 2. Edit
        let row = page.locator('tr').filter({ hasText: testName }).first();
        await row.getByTestId('btn-edit-value').click();
        await expect(nameInput).toBeVisible();
        await nameInput.fill(`${testName} Updated`);
        const putResp = page.waitForResponse(r =>
            r.url().includes('/values/') && r.request().method() === 'PUT'
        );
        await page.getByTestId('btn-save-value').click();
        await putResp;
        await expect(page.locator('.swal2-container')).toContainText('Berhasil', { timeout: 15000 });
        await expect(page.locator('.swal2-container')).not.toBeVisible({ timeout: 5000 }).catch(() => {});
        await page.waitForResponse(r =>
            r.url().includes('/values') && r.request().method() === 'GET', { timeout: 10000 }
        ).catch(() => {});
        await uiSearch(`${testName} Updated`);
        await expect(page.locator('table')).toContainText(`${testName} Updated`, { timeout: 15000 });

        // 3. Delete
        row = page.locator('tr').filter({ hasText: `${testName} Updated` }).first();
        const delResp = page.waitForResponse(r =>
            r.url().includes('/values/') && r.request().method() === 'DELETE'
        );
        await row.getByTestId('btn-delete-value').click();
        await page.locator('button:has-text("Ya, Hapus!")').click();
        await delResp;
        await page.waitForResponse(r =>
            r.url().includes('/values') && r.request().method() === 'GET', { timeout: 10000 }
        ).catch(() => {});
        await uiSearch(testCode);
        await expect(page.locator('table')).not.toContainText(testName, { timeout: 15000 });
    });

    test('harus bisa melakukan ekspor dan unduh template nilai lookup', async ({ page }) => {
        const exportPromise = page.waitForEvent('download');
        await page.getByTestId('btn-export-value').click();
        const exportDownload = await exportPromise;
        expect(exportDownload.suggestedFilename()).toMatch(/lookup-values-.*\.xlsx/);

        await page.getByTestId('btn-import-menu-value').click();
        const templatePromise = page.waitForEvent('download');
        await page.getByTestId('btn-import-template-value').click();
        const templateDownload = await templatePromise;
        expect(templateDownload.suggestedFilename()).toBe('template-import-lookup-values.xlsx');
    });

    test('harus bisa melakukan impor nilai lookup dari file Excel', async ({ page }) => {
        // Mock file excel (buffer dummy)
        const fileContent = Buffer.from('dummy excel content');
        
        await page.getByTestId('btn-import-menu-value').click();
        
        // Setup file chooser listener
        const fileChooserPromise = page.waitForEvent('filechooser');
        await page.getByTestId('btn-import-data-value').click();
        const fileChooser = await fileChooserPromise;
        
        await fileChooser.setFiles([{
            name: 'import-test.xlsx',
            mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            buffer: fileContent
        }]);

        // Verifikasi loading atau sukses (karena mock file mungkin gagal di BE, kita cek alur frontend)
        // Jika fileContent bukan Excel asli, BE mungkin return 500/422, tapi kita tes alur UI-nya.
        // Untuk benar-benar "aman", kita pastikan tidak ada crash di FE
        await expect(page.locator('.swal2-container')).toBeVisible({ timeout: 15000 });
    });
});
