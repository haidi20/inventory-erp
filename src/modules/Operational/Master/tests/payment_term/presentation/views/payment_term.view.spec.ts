import { test, expect } from '@playwright/test';

test.describe('PaymentTerm View E2E', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.waitForSelector('input[name="username"]', { timeout: 20000 });
        await page.fill('input[name="username"]', 'superadmin');
        await page.fill('input[name="password"]', 'superadmin');
        await page.click('button[type="submit"]');
        await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 20000 });
        await page.goto('/master/payment-terms');
        await page.waitForLoadState('domcontentloaded', { timeout: 20000 });
        await expect(page.getByTestId('btn-create-term')).toBeVisible({ timeout: 20000 });
    });

    test('harus memuat halaman termin dan menampilkan tabel', async ({ page }) => {
        await expect(page.locator('table')).toBeVisible();
        await expect(page.locator('body')).toContainText('Termin');
    });

    test('harus menampilkan data seeder Ninesoft - T0', async ({ page }) => {
        await page.getByTestId('input-search-term').fill('T0');
        await page.waitForTimeout(800);
        await expect(page.locator('table')).toContainText('T0', { timeout: 15000 });
    });

    test('harus menampilkan data seeder Ninesoft - 30 hari', async ({ page }) => {
        await expect(page.locator('table')).toContainText('30', { timeout: 15000 });
    });

    test('harus menampilkan data seeder Ninesoft - T100', async ({ page }) => {
        await page.getByTestId('input-search-term').fill('T100');
        await page.waitForTimeout(800);
        await expect(page.locator('table')).toContainText('T100', { timeout: 15000 });
    });

    test('harus berhasil CRUD termin', async ({ page }) => {
        const ts   = Date.now();
        const name = `T${ts}`.slice(0, 20);
        const responsePromise = page.waitForResponse((resp) =>
            resp.url().includes('/masters/payment-terms') && resp.request().method() === 'POST'
        );
        await page.getByTestId('btn-create-term').click();
        await expect(page.locator('.modal-card')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-term-name').fill(name);
        await page.getByTestId('input-term-due-days').fill('14');
        await page.getByTestId('btn-submit-term').click();
        const resp = await responsePromise;
        expect(resp.status(), `API error: ${await resp.text()}`).toBeLessThan(400);
        await expect(page.locator('.modal-card')).not.toBeVisible({ timeout: 15000 });
        await page.getByTestId('input-search-term').fill(name);
        await page.waitForTimeout(800);
        await expect(page.locator('table')).toContainText(name, { timeout: 15000 });
        const row = page.locator('tr').filter({ hasText: name }).first();
        await row.getByTestId('btn-delete-term').click();
        await expect(page.locator('.swal2-confirm')).toBeVisible({ timeout: 10000 });
        await page.locator('.swal2-confirm').click();
        await page.waitForTimeout(2000);
        await page.getByTestId('input-search-term').fill(name);
        await page.waitForTimeout(800);
        await expect(page.locator('table')).not.toContainText(name, { timeout: 15000 });
    });

    test('harus bisa mencari termin dengan search', async ({ page }) => {
        await page.getByTestId('input-search-term').fill('T0');
        await page.waitForTimeout(800);
        await expect(page.locator('table')).toContainText('T0', { timeout: 10000 });
    });

    test.describe('Filter Status Aktif', () => {
        test('klik Aktif hanya menampilkan data is_active=true', async ({ page }) => {
            const responsePromise = page.waitForResponse(
                resp => resp.url().includes('/api/v1/masters/payment-terms') &&
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
                resp => resp.url().includes('/api/v1/masters/payment-terms') &&
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
                resp => resp.url().includes('/api/v1/masters/payment-terms') &&
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