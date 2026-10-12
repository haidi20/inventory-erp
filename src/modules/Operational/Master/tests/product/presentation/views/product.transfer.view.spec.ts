import fs from 'node:fs';
import { test, expect, type BrowserContext, type Page } from '@playwright/test';

type JsonObject = Record<string, any>;

const CODE_PREFIX = `E2E-TRANSFER-${Date.now()}`;

let context: BrowserContext;
let page: Page;
let apiToken = '';
let defaultCompanyUuid = '';
let branchUuid = '';

test.describe('Product Transfer E2E', () => {
    test.describe.configure({ mode: 'serial' });

    test.beforeAll(async ({ browser }) => {
        context = await browser.newContext();
        const setupPage = await context.newPage();

        await login(setupPage);
        await setupPage.goto('/master/products');
        await expect(setupPage.locator('table')).toBeVisible({ timeout: 30000 });

        const inertiaProps = await readInertiaProps(setupPage);
        apiToken = inertiaProps.props.auth.api_token;
        defaultCompanyUuid = inertiaProps.props.auth.user.company_uuid
            ?? inertiaProps.props.default_company_uuid
            ?? inertiaProps.props.app_context.company_uuid;
        branchUuid = inertiaProps.props.auth.user.branch_uuid ?? inertiaProps.props.app_context.branch_uuid;

        if (!apiToken) {
            throw new Error('API token Inertia tidak tersedia.');
        }

        await apiRequest('POST', '/products', {
            code: `${CODE_PREFIX}-A`,
            name: 'Export Filter A',
            barcode: `${CODE_PREFIX}-BARCODE-A`,
            is_active: true,
        });
        await apiRequest('POST', '/products', {
            code: `${CODE_PREFIX}-B`,
            name: 'Export Filter B',
            barcode: `${CODE_PREFIX}-BARCODE-B`,
            is_active: true,
        });

        await setupPage.close();
    });

    test.beforeEach(async () => {
        page = await context.newPage();
        await page.goto('/master/products');
        await expect(page.locator('table')).toBeVisible({ timeout: 30000 });
    });

    test.afterEach(async () => {
        await page.close();
    });

    test.afterAll(async () => {
        const response = await context.request.fetch(`/api/v1/products?filter_code=${encodeURIComponent(CODE_PREFIX)}&per_page=100`, {
            headers: apiHeaders(),
        });
        const rows = ((await response.json())?.data ?? []) as JsonObject[];

        for (const row of rows) {
            await apiRequest('DELETE', `/products/${row.uuid}`);
        }

        await context.close();
    });

    test('template produk dapat diunduh dengan header CSV', async () => {
        const responsePromise = page.waitForResponse((response) => response.url().includes('/api/v1/products/import/template'));
        await page.getByTestId('btn-download-product-template').click();
        const response = await responsePromise;

        expect(response.status()).toBe(200);
        expect(response.headers()['content-type']).toBe('text/csv; charset=UTF-8');
        expect(response.headers()['content-disposition']).toBe('attachment; filename=products-import-template.csv');
        const content = await response.text();
        expect(content).toContain('code,name,barcode');
    });

    test('import CSV menambahkan produk dan menampilkan hasilnya', async () => {
        const importedCode = `${CODE_PREFIX}-IMPORT`;
        await page.locator('#product-import-file').setInputFiles({
            name: 'products-import.csv',
            mimeType: 'text/csv',
            buffer: Buffer.from(
                'code,name,category,unit,price_buy,price_sell,min_stock\n'
                + `${importedCode},Imported Transfer Product,Medicine,Tablet,1000,1500,20\n`,
            ),
        });

        const swalPopup = page.locator('.swal2-popup');
        await expect(swalPopup).toBeVisible({ timeout: 15000 });
        await expect(swalPopup).toContainText('Import selesai');
        await expect(swalPopup).toContainText('Berhasil menambah 1');
        await page.locator('.swal2-confirm').click();

        const importedResponse = await context.request.fetch(`/api/v1/products?filter_code=${encodeURIComponent(importedCode)}&per_page=10`, {
            headers: {
                Authorization: `Bearer ${apiToken}`,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const importedRows = ((await importedResponse.json())?.data ?? []) as JsonObject[];
        expect(importedRows).toHaveLength(1);
        expect(importedRows[0].name).toBe('Imported Transfer Product');

        await applyFilter({ filter_code: importedCode });
        const row = page.locator('tbody tr').filter({ hasText: importedCode });
        await expect(row).toHaveCount(1, { timeout: 15000 });
        await expect(row).toContainText('Imported Transfer Product');
    });

    test('export produk memakai filter modal', async () => {
        await applyFilter({ filter_code: `${CODE_PREFIX}-A` });

        const requestPromise = page.waitForRequest((request) => {
            const url = new URL(request.url());
            return url.pathname.endsWith('/api/v1/products/export')
                && url.searchParams.get('filter_code') === `${CODE_PREFIX}-A`;
        });
        const downloadPromise = page.waitForEvent('download');
        await page.getByTestId('btn-export-product').click();

        const [, download] = await Promise.all([requestPromise, downloadPromise]);
        expect(download.suggestedFilename()).toBe('products-export.csv');

        const filePath = await download.path();
        const content = fs.readFileSync(filePath!, 'utf8');
        const rows = content.trim().split(/\r?\n/).slice(1);
        expect(rows).toHaveLength(1);
        const exportedCode = rows[0].split(',')[0];
        expect(exportedCode).toBe(`${CODE_PREFIX}-A`);
    });
});

async function login(activePage: Page): Promise<void> {
    await activePage.goto('/login');
    await activePage.waitForSelector('input[name="email"]', { timeout: 20000 });
    await activePage.fill('input[name="email"]', 'superadmin@klinik.local');
    await activePage.fill('input[name="password"]', 'superadmin');
    await activePage.click('button[type="submit"]');
    await activePage.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 20000 });
}

async function readInertiaProps(activePage: Page): Promise<JsonObject> {
    const raw = await activePage.evaluate(() => {
        const script = document.querySelector('script[data-page="app"]');
        if (script?.textContent) {
            return JSON.parse(script.textContent);
        }

        const app = document.querySelector('#app[data-page]');
        return app?.getAttribute('data-page') ? JSON.parse(app.getAttribute('data-page')!) : null;
    });

    if (!raw) {
        throw new Error('Payload Inertia tidak ditemukan.');
    }

    return raw as JsonObject;
}

function apiHeaders(): JsonObject {
    return {
        Authorization: `Bearer ${apiToken}`,
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-Klinik-Company-Uuid': defaultCompanyUuid,
        'X-Klinik-Branch-Uuid': branchUuid,
    };
}

async function apiRequest(method: 'GET' | 'POST' | 'DELETE', path: string, payload?: JsonObject): Promise<JsonObject> {
    const response = await context.request.fetch(`/api/v1${path}`, {
        method,
        data: payload,
        headers: apiHeaders(),
    });

    if (!response.ok()) {
        throw new Error(`${method} ${path} gagal (${response.status()}): ${await response.text()}`);
    }

    if (response.status() === 204) {
        return {};
    }

    return await response.json() as JsonObject;
}

async function applyFilter(values: JsonObject): Promise<void> {
    await page.getByTestId('btn-open-product-filter').click();
    await expect(page.getByTestId('product-filter-modal')).toBeVisible({ timeout: 15000 });
    await page.getByTestId('btn-reset-product-filter').click();

    for (const [key, value] of Object.entries(values)) {
        const testId = key === 'filter_code' ? 'filter-code' : key;
        await page.getByTestId(testId).fill(String(value));
    }

    await page.getByTestId('btn-apply-product-filter').click();
    await expect(page.getByTestId('product-filter-modal')).toHaveCount(0);
}
