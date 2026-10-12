import { test, expect, type Page } from '@playwright/test';

test.describe('Lookup View E2E', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.waitForSelector('input[name="username"]', { timeout: 20000 });
        await page.fill('input[name="username"]', 'superadmin');
        await page.fill('input[name="password"]', 'superadmin');
        await page.click('button[type="submit"]');
        await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 20000 });
        await page.goto('/masters/lookups');
        await page.waitForLoadState('domcontentloaded', { timeout: 20000 });
        await expect(page.getByTestId('btn-create-lookup')).toBeVisible({ timeout: 20000 });
    });

    async function searchLookup(page: Page, keyword: string) {
        const searchInput = page.getByTestId('input-search-lookup');
        await expect(searchInput).toBeVisible();
        const responsePromise = page.waitForResponse((resp) =>
            resp.url().includes('/api/v1/lov/sets') && resp.request().method() === 'GET' && resp.status() === 200
        );
        await searchInput.fill(keyword);
        await responsePromise;
    }

    test('harus memuat halaman daftar nilai dan menampilkan tabel', async ({ page }) => {
        await expect(page.locator('table')).toBeVisible();
    });

    test('harus menampilkan data seeder Ninesoft - GENDER', async ({ page }) => {
        await searchLookup(page, 'GENDER');
        await expect(page.locator('table')).toContainText('GENDER', { timeout: 15000 });
    });

    test('harus menampilkan data seeder Ninesoft - RELIGION', async ({ page }) => {
        await searchLookup(page, 'RELIGION');
        await expect(page.locator('table')).toContainText('RELIGION', { timeout: 15000 });
    });

    test('harus menampilkan data seeder Ninesoft - BLOOD_TYPE', async ({ page }) => {
        await searchLookup(page, 'BLOOD_TYPE');
        await expect(page.locator('table')).toContainText('BLOOD_TYPE', { timeout: 15000 });
    });

    test('harus berhasil CRUD daftar nilai', async ({ page }) => {
        const ts       = Date.now();
        const testName = `LOOKUP TEST ${ts}`.slice(0, 50);
        const testCode = `LOOKUP-${ts}`.slice(0, 30);
        await page.getByTestId('btn-create-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-lookup-name').fill(testName);
        await page.getByTestId('input-lookup-program-code').fill(testCode);
        await page.getByTestId('btn-submit-lookup').click();
        await expect(page.getByTestId('input-lookup-name')).not.toBeVisible({ timeout: 15000 });
        await searchLookup(page, testCode);
        await expect(page.locator('table')).toContainText(testName, { timeout: 15000 });
        const row = page.locator('tr').filter({ hasText: testCode }).first();
        await row.hover();
        await row.getByTestId('btn-delete-lookup').click({ force: true });
        await expect(page.locator('.swal2-confirm')).toBeVisible({ timeout: 10000 });
        await page.locator('.swal2-confirm').click();
        await page.waitForTimeout(1500);
        await searchLookup(page, testCode);
        await expect(page.locator('table')).not.toContainText(testName, { timeout: 15000 });
    });

    test('harus bisa mencari daftar nilai dengan search', async ({ page }) => {
        await searchLookup(page, 'GENDER');
        await expect(page.locator('table')).toContainText('GENDER', { timeout: 10000 });
    });

    test.describe('Filter Status Aktif', () => {
        test('klik Aktif hanya menampilkan data is_active=true', async ({ page }) => {
            const responsePromise = page.waitForResponse(
                resp => resp.url().includes('/api/v1/lov/sets') &&
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
                resp => resp.url().includes('/api/v1/lov/sets') &&
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
                resp => resp.url().includes('/api/v1/lov/sets') &&
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