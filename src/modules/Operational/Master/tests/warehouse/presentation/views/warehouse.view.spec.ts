import { test, expect } from '@playwright/test';

test.describe('Warehouse View E2E', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.waitForSelector('input[name="username"]', { timeout: 20000 });
        await page.fill('input[name="username"]', 'superadmin');
        await page.fill('input[name="password"]', 'superadmin');
        await page.click('button[type="submit"]');
        await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 20000 });
        await page.goto('/master/warehouses');
        await expect(page.getByTestId('btn-create-warehouse')).toBeVisible({ timeout: 30000 });
    });

    test('harus memuat halaman gudang dan menampilkan tabel', async ({ page }) => {
        await expect(page.locator('table')).toBeVisible();
        await expect(page.locator('body')).toContainText('Gudang');
    });

    test('harus menampilkan data seeder Ninesoft - PUSAT', async ({ page }) => {
        await page.getByTestId('input-search-warehouse').fill('PUSAT');
        await page.waitForTimeout(800);
        await expect(page.locator('table')).toContainText('PUSAT', { timeout: 15000 });
    });

    test('harus menampilkan data seeder Ninesoft - KONSINYASI', async ({ page }) => {
        await expect(page.locator('table')).toContainText('KONSINYASI', { timeout: 15000 });
    });

    test('harus menampilkan data seeder Ninesoft - ABADI JAYA', async ({ page }) => {
        await expect(page.locator('table')).toContainText('ABADI JAYA', { timeout: 15000 });
    });

    test('harus menampilkan data seeder Ninesoft - KALIANAK', async ({ page }) => {
        await expect(page.locator('table')).toContainText('KALIANAK', { timeout: 15000 });
    });

    test('harus berhasil CRUD gudang', async ({ page }) => {
        const ts   = Date.now();
        const code = `WH${ts}`.slice(0, 20);
        const name = `Gudang Test ${ts}`.slice(0, 50);
        const responsePromise = page.waitForResponse((resp) =>
            resp.url().includes('/master/warehouses') && resp.request().method() === 'POST'
        );
        await page.getByTestId('btn-create-warehouse').click();
        await expect(page.locator('.modal-card')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-warehouse-code').fill(code);
        await page.getByTestId('input-warehouse-name').fill(name);
        await page.getByTestId('btn-submit-warehouse').click();
        const resp = await responsePromise;
        expect(resp.status(), `API error: ${await resp.text()}`).toBeLessThan(400);
        await expect(page.locator('.modal-card')).not.toBeVisible({ timeout: 15000 });
        await page.getByTestId('input-search-warehouse').fill(code);
        await page.waitForTimeout(800);
        await expect(page.locator('table')).toContainText(name, { timeout: 15000 });
        const row = page.locator('tr').filter({ hasText: code }).first();
        const deleteResp = page.waitForResponse((resp) =>
            resp.url().includes('/master/warehouses/') && resp.request().method() === 'DELETE'
        );
        await row.getByTestId('btn-delete-warehouse').click();
        await expect(page.locator('.swal2-confirm')).toBeVisible({ timeout: 10000 });
        await page.locator('.swal2-confirm').click();
        await deleteResp;
        await page.waitForTimeout(2000);
        await page.getByTestId('input-search-warehouse').fill(name);
        await page.waitForTimeout(800);
        await expect(page.locator('table')).not.toContainText(name, { timeout: 15000 });
    });

    test('harus bisa mencari gudang dengan search', async ({ page }) => {
        await page.getByTestId('input-search-warehouse').fill('PUSAT');
        await page.waitForTimeout(800);
        await expect(page.locator('table')).toContainText('PUSAT', { timeout: 10000 });
    });

    test.describe('Filter Status Aktif', () => {
        test.setTimeout(90000);

        test('klik Aktif hanya menampilkan data is_active=true', async ({ page }) => {
            const responsePromise = page.waitForResponse(
                resp => resp.url().includes('/api/v1/master/warehouses') &&
                        resp.url().includes('filter_active=true') &&
                        resp.request().method() === 'GET',
                { timeout: 15000 }
            );
            await page.getByTestId('filter-active').click();
            const resp = await responsePromise;
            expect(resp.status()).toBe(200);
            const body = await resp.json();
            const items: any[] = body.data ?? [];
            for (const item of items) {
                expect(item.is_active, `Data ${item.uuid ?? item.code ?? ''} seharusnya aktif`).toBe(true);
            }
            await expect(page.locator('table')).toBeVisible();
        });

        test('klik Non-aktif hanya menampilkan data is_active=false', async ({ page }) => {
            const responsePromise = page.waitForResponse(
                resp => resp.url().includes('/api/v1/master/warehouses') &&
                        resp.url().includes('filter_active=false') &&
                        resp.request().method() === 'GET',
                { timeout: 15000 }
            );
            await page.getByTestId('filter-inactive').click();
            const resp = await responsePromise;
            expect(resp.status()).toBe(200);
            const body = await resp.json();
            const items: any[] = body.data ?? [];
            for (const item of items) {
                expect(item.is_active, `Data ${item.uuid ?? item.code ?? ''} seharusnya tidak aktif`).toBe(false);
            }
            await expect(page.locator('table')).toBeVisible();
        });

        test('klik Semua memuat ulang data tanpa filter is_active', async ({ page }) => {
            await page.getByTestId('filter-active').click();
            await page.waitForTimeout(500);

            const responsePromise = page.waitForResponse(
                resp => resp.url().includes('/api/v1/master/warehouses') &&
                        !resp.url().includes('filter_active') &&
                        resp.request().method() === 'GET',
                { timeout: 15000 }
            );
            await page.getByTestId('filter-all').click();
            const resp = await responsePromise;
            expect(resp.status()).toBe(200);
            const body = await resp.json();
            expect(body.data).toBeDefined();
            await expect(page.locator('table')).toBeVisible();
        });

        test('tombol filter aktif menampilkan state visual yang benar', async ({ page }) => {
            await expect(page.getByTestId('filter-all')).toBeVisible();
            await expect(page.getByTestId('filter-active')).toBeVisible();
            await expect(page.getByTestId('filter-inactive')).toBeVisible();

            // default: Semua active
            await expect(page.locator('[data-testid="filter-all"].active')).toBeVisible();
            await expect(page.locator('[data-testid="filter-active"].active')).not.toBeVisible();

            // klik Aktif
            await page.getByTestId('filter-active').click();
            await page.waitForTimeout(300);
            await expect(page.locator('[data-testid="filter-active"].active')).toBeVisible();
            await expect(page.locator('[data-testid="filter-all"].active')).not.toBeVisible();

            // klik Non-aktif
            await page.getByTestId('filter-inactive').click();
            await page.waitForTimeout(300);
            await expect(page.locator('[data-testid="filter-inactive"].active')).toBeVisible();
            await expect(page.locator('[data-testid="filter-active"].active')).not.toBeVisible();

            // klik Semua
            await page.getByTestId('filter-all').click();
            await page.waitForTimeout(300);
            await expect(page.locator('[data-testid="filter-all"].active')).toBeVisible();
        });
    });

});