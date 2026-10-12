import { test, expect } from '@playwright/test';

test.describe('Unit View E2E', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.waitForSelector('input[name="username"]', { timeout: 20000 });
        await page.fill('input[name="username"]', 'superadmin');
        await page.fill('input[name="password"]', 'superadmin');
        await page.click('button[type="submit"]');
        await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 20000 });
        await page.goto('/master/units');
        await expect(page.locator('table')).toBeVisible({ timeout: 30000 });
        await expect(page.getByTestId('btn-create-unit')).toBeVisible({ timeout: 20000 });
    });

    test('harus memuat halaman satuan dan menampilkan tabel', async ({ page }) => {
        await expect(page.locator('table')).toBeVisible();
        await expect(page.locator('body')).toContainText('Satuan');
    });

    test('harus menampilkan data seeder Ninesoft - BATANG', async ({ page }) => {
        await page.getByTestId('input-search-unit').fill('BATANG');
        await page.waitForTimeout(800);
        await expect(page.locator('table')).toContainText('BATANG', { timeout: 15000 });
    });

    test('harus menampilkan data seeder Ninesoft - PCS', async ({ page }) => {
        await page.getByTestId('input-search-unit').fill('PCS');
        await page.waitForTimeout(800);
        await expect(page.locator('table')).toContainText('PCS', { timeout: 15000 });
    });

    test('harus menampilkan data seeder Ninesoft - BTL', async ({ page }) => {
        await page.getByTestId('input-search-unit').fill('BTL');
        await page.waitForTimeout(800);
        await expect(page.locator('table')).toContainText('BTL', { timeout: 15000 });
    });

    test('harus berhasil CRUD satuan', async ({ page }) => {
        const ts   = Date.now();
        const code = `PWU${ts}`.slice(0, 20);
        const name = `Unit Test ${ts}`.slice(0, 50);
        await page.getByTestId('btn-create-unit').click();
        await expect(page.getByTestId('input-unit-code')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-unit-code').fill(code);
        await page.getByTestId('input-unit-name').fill(name);
        await page.getByTestId('input-unit-symbol').fill('tst');
        await page.getByTestId('btn-submit-unit').click();
        await expect(page.getByTestId('input-unit-code')).not.toBeVisible({ timeout: 15000 });
        await page.getByTestId('input-search-unit').fill(code);
        await page.waitForTimeout(1000);
        await expect(page.locator('table')).toContainText(name, { timeout: 15000 });
        const row = page.locator('tr').filter({ hasText: code }).first();
        await row.getByTestId('btn-delete-unit').click();
        await expect(page.locator('.swal2-confirm')).toBeVisible({ timeout: 10000 });
        const deleteResp = page.waitForResponse((resp) =>
            resp.url().includes('/masters/units/') && resp.request().method() === 'DELETE'
        );
        await page.locator('.swal2-confirm').click();
        await deleteResp;
        await page.waitForTimeout(3000);
        await page.getByTestId('input-search-unit').fill(code);
        await page.waitForTimeout(1000);
        await expect(page.locator('table')).not.toContainText(name, { timeout: 15000 });
    });

    test('harus bisa mencari satuan dengan search', async ({ page }) => {
        await page.getByTestId('input-search-unit').fill('PCS');
        await page.waitForTimeout(800);
        await expect(page.locator('table')).toContainText('PCS', { timeout: 10000 });
    });

    test.describe('Filter Status Aktif', () => {
        test('klik Aktif hanya menampilkan data is_active=true', async ({ page }) => {
            const responsePromise = page.waitForResponse(
                resp => resp.url().includes('/api/v1/masters/units') &&
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
                resp => resp.url().includes('/api/v1/masters/units') &&
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
                resp => resp.url().includes('/api/v1/masters/units') &&
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