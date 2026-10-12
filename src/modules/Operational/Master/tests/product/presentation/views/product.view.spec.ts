import { execFileSync } from 'node:child_process';
import { test, expect, type BrowserContext, type Page } from '@playwright/test';

type Option = { uuid: string; name?: string; label?: string };
type JsonObject = Record<string, any>;
type UploadPayload = { name: string; mimeType: string; buffer: Buffer };

const CODE_PREFIX = 'AAA-E2E-FILTER';

let context: BrowserContext;
let page: Page;
let apiToken = '';
let defaultCompanyUuid = '';
let currentUserUuid = '';
let fixtures: Record<string, any> = {};

test.describe('Product Filter E2E', () => {
    test.describe.configure({ mode: 'serial' });

    test.beforeAll(async ({ browser }) => {
        context = await browser.newContext();
        const setupPage = await context.newPage();

        await login(setupPage);
        await setupPage.goto('/master/products');
        await expect(setupPage.locator('table')).toBeVisible({ timeout: 30000 });

        const inertiaProps = await readInertiaProps(setupPage);
        apiToken = inertiaProps.props.auth.api_token;
        defaultCompanyUuid = inertiaProps.props.default_company_uuid
            ?? inertiaProps.props.app_context.company_uuid;
        currentUserUuid = inertiaProps.props.auth.user.uuid;

        if (!apiToken) {
            throw new Error('API token Inertia tidak tersedia.');
        }

        fixtures = await createFilterFixtures();
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
        await context.close();
    });

    test('top search menyaring produk real', async () => {
        const search = page.getByTestId('input-search-product');
        await expect(search).toHaveAttribute('placeholder', 'Cari kode, nama, atau barcode produk...');
        await search.fill(`${CODE_PREFIX}-A`);

        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);
    });

    test('filter memakai modal dengan 17 field berurutan', async () => {
        await page.getByTestId('btn-open-product-filter').click();
        const modal = page.getByTestId('product-filter-modal');
        await expect(modal).toBeVisible({ timeout: 15000 });

        await expect(modal.locator('.filter-field > span')).toHaveText([
            'Perusahaan',
            'Barcode',
            'Kode Produk',
            'Nama Produk',
            'Satuan',
            'Kelompok',
            'Keterangan',
            'Jenis',
            'Tipe Produk',
            'Merek',
            'Made-In',
            'Grade',
            'User Buat',
            'User Edit',
            'Created Date',
            'Last Update Date',
            'Periode',
        ]);
    });

    test('filter produk memakai empat kolom dalam satu baris', async () => {
        await page.getByTestId('btn-open-product-filter').click();
        const modal = page.getByTestId('product-filter-modal');
        await expect(modal).toBeVisible({ timeout: 15000 });

        const boxes = [];
        for (const index of [0, 1, 2, 3]) {
            const field = modal.locator('.filter-field').nth(index);
            await expect(field).toBeVisible();
            boxes.push(await field.boundingBox() as { x: number; y: number; width: number });
        }

        expect(Math.abs(boxes[0].y - boxes[3].y)).toBeLessThan(10);
        expect(boxes[1].x).toBeGreaterThan(boxes[0].x);
        expect(boxes[2].x).toBeGreaterThan(boxes[1].x);
        expect(boxes[3].x).toBeGreaterThan(boxes[2].x);
        expect(boxes[0].width).toBeGreaterThan(200);
    });

    test('filter perusahaan tersedia di modal untuk superadmin', async () => {
        await page.getByTestId('btn-open-product-filter').click();
        const company = page.getByTestId('filter-company');
        await expect(company).toBeVisible();
        await expect(company.locator('.vs__search')).toBeVisible();
        await expect(page.getByTestId('product-filter-modal')).toHaveCSS('max-width', '1140px');
    });

    test('semua select filter produk bisa dicari', async () => {
        await page.getByTestId('btn-open-product-filter').click();
        await expect(page.getByTestId('product-filter-modal')).toBeVisible({ timeout: 15000 });

        const searchableFilters = [
            'filter-company',
            'filter-unit',
            'filter-group',
            'filter-kind',
            'filter-type',
            'filter-brand',
            'filter-origin',
            'filter-grade',
            'filter-created-by',
            'filter-updated-by',
        ];

        for (const testId of searchableFilters) {
            await expect(page.getByTestId(testId).locator('.vs__search')).toHaveCount(1);
        }
    });

    test('select filter satuan memuat opsi dari server', async () => {
        await page.getByTestId('btn-open-product-filter').click();
        const select = page.getByTestId('filter-unit');
        await select.click();

        const request = page.waitForRequest((candidate) =>
            candidate.url().includes('/api/v1/products/options')
            && candidate.url().includes('type=unit')
            && candidate.url().includes('search='),
        );
        await select.locator('.vs__search').fill('E2E Unit A');
        await request;

        const option = page.locator('.vs__dropdown-option').filter({ hasText: 'E2E Unit A' }).first();
        await expect(option).toBeVisible();
    });

    test('filter perusahaan menyaring data', async () => {
        await applyFilter({ filter_company_uuid: fixtures.companyA });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_company_uuid: fixtures.companyB });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter barcode menyaring data', async () => {
        await applyFilter({ filter_barcode: 'E2E-BARCODE-A' });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_barcode: 'E2E-BARCODE-B' });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter kode produk menyaring data', async () => {
        await applyFilter({ filter_code: `${CODE_PREFIX}-A` });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_code: `${CODE_PREFIX}-B` });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter nama produk menyaring data', async () => {
        await applyFilter({ filter_name: 'E2E Filter Name A' });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_name: 'E2E Filter Name B' });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter satuan menyaring data', async () => {
        await applyFilter({ filter_unit: fixtures.unitA });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_unit: fixtures.unitB });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter kelompok menyaring data', async () => {
        await applyFilter({ filter_group_uuid: fixtures.groupA });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_group_uuid: fixtures.groupB });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter keterangan menyaring data', async () => {
        await applyFilter({ filter_keterangan: 'E2E Description A' });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_keterangan: 'E2E Description B' });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter jenis menyaring data', async () => {
        await applyFilter({ filter_kind: fixtures.kindA });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_kind: fixtures.kindB });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter tipe produk menyaring data', async () => {
        await applyFilter({ filter_type_uuid: fixtures.typeA });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_type_uuid: fixtures.typeB });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter merek menyaring data', async () => {
        await applyFilter({ filter_brand_uuid: fixtures.brandA });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_brand_uuid: fixtures.brandB });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter made-in menyaring data', async () => {
        await applyFilter({ filter_origin_uuid: fixtures.originA });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_origin_uuid: fixtures.originB });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter grade menyaring data', async () => {
        await applyFilter({ filter_grade_uuid: fixtures.gradeA });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_grade_uuid: fixtures.gradeB });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter user buat menyaring data', async () => {
        await applyFilter({ filter_created_by: fixtures.currentUser });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_created_by: fixtures.otherUser });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter user edit menyaring data', async () => {
        await applyFilter({ filter_updated_by: fixtures.currentUser });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', false);

        await applyFilter({ filter_updated_by: fixtures.otherUser });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', true);
    });

    test('filter created date menyaring data', async () => {
        await applyFilter({
            filter_created_from: fixtures.today,
            filter_created_to: fixtures.today,
        });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', true);

        await applyFilter({
            filter_created_from: fixtures.tomorrow,
            filter_created_to: fixtures.tomorrow,
        });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', false);
    });

    test('filter last update date menyaring data', async () => {
        await applyFilter({
            filter_updated_from: fixtures.today,
            filter_updated_to: fixtures.today,
        });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', true);

        await applyFilter({
            filter_updated_from: fixtures.tomorrow,
            filter_updated_to: fixtures.tomorrow,
        });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', false);
    });

    test('filter periode menyaring data', async () => {
        await applyFilter({ filter_periode: fixtures.currentPeriod });
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', true);

        await applyFilter({ filter_periode: fixtures.nextPeriod });
        await expectRow('FILTER-A', false);
        await expectRow('FILTER-B', false);
    });

    test('reset filter menampilkan data fixture lagi', async () => {
        await applyFilter({ filter_code: `${CODE_PREFIX}-A` });
        await expectRow('FILTER-B', false);

        await applyFilter({});
        await expectRow('FILTER-A', true);
        await expectRow('FILTER-B', true);
    });

    test('form create memakai select master yang bisa dicari', async () => {
        await page.goto('/master/products/create');
        const select = page.getByTestId('select-group');
        await select.click();

        const option = page.locator('.vs__dropdown-option').first();
        await expect(option).toBeVisible();
    });

    test('semua tab dan input produk dapat diisi lalu tersimpan berdasarkan produk', async () => {
        await openProductCreateForm(page);
        await ensureUnit('A');
        const fixtureSuffix = Date.now();
        const warehouse = await apiRequest('POST', '/master/warehouses', {
            code: `E2E-WH-${fixtureSuffix}`,
            name: `E2E Warehouse ${fixtureSuffix}`,
            is_active: true,
        });
        const priceLevel = await apiRequest('POST', '/masters/price-levels', {
            code: `E2E-PL-${fixtureSuffix}`,
            name: `E2E Price Level ${fixtureSuffix}`,
            is_active: true,
        });
        const account = await apiRequest('POST', '/accounts', {
            code: `E2E-ACC-${fixtureSuffix}`,
            name: `E2E Account ${fixtureSuffix}`,
            type: 'asset',
            is_active: true,
        });
        await page.reload();
        await expect(page.getByTestId('product-form')).toBeVisible();
        const code = `TAB-E2E-${Date.now()}`;

        await page.getByTestId('input-code').fill(code);
        await page.getByTestId('input-name').fill('Produk Semua Tab E2E');

        await page.getByTestId('tab-account').click();
        await page.getByTestId('account-row-select-sales').click();
        await page.locator('.vs__dropdown-option').filter({ hasText: `E2E-ACC-${fixtureSuffix}` }).click();
        await expect(page.getByTestId('account-row-label-sales')).toContainText(`E2E-ACC-${fixtureSuffix}`);
        await page.getByTestId('account-row-select-inventory').click();
        await page.locator('.vs__dropdown-option').filter({ hasText: `E2E-ACC-${fixtureSuffix}` }).click();
        await expect(page.getByTestId('account-row-label-inventory')).toContainText(`E2E-ACC-${fixtureSuffix}`);

        await page.getByTestId('tab-detail').click();
        await page.getByTestId('btn-add-unit-row').click();
        await selectFirstVueOption(page, 'unit-row-select');
        await page.getByTestId('unit-row-ratio').fill('12');
        await page.getByTestId('unit-row-note').fill('Satuan E2E');

        await page.getByTestId('tab-saldo').click();
        await page.getByTestId('btn-add-opening-balance-row').click();
        await selectFirstVueOption(page, 'opening-row-location');
        await page.getByTestId('opening-row-qty').fill('25');

        await page.getByTestId('tab-harga').click();
        await page.getByTestId('btn-add-price-row').click();
        await selectFirstVueOption(page, 'price-row-level');
        await page.getByTestId('price-row-buy').fill('10000');
        await page.getByTestId('price-row-sell').fill('15000');

        await page.getByTestId('tab-diskon').click();
        await page.getByTestId('btn-add-discount-row').click();
        await page.getByTestId('discount-row-quantity').fill('10');
        await selectFirstVueOption(page, 'discount-row-unit');
        await page.getByTestId('discount-row-percent').fill('5');

        const responsePromise = page.waitForResponse((response) =>
            response.url().includes('/api/v1/products') && response.request().method() === 'POST',
        );
        await page.getByTestId('btn-submit-product').click();
        const response = await responsePromise;
        expect(response.ok()).toBeTruthy();
        await expect(page).toHaveURL(/\/master\/products\/create$/, { timeout: 20000 });

        const created = await apiRequest('GET', `/products?search=${encodeURIComponent(code)}&per_page=10`);
        const product = (created.data ?? []).find((row: JsonObject) => row.code === code);
        expect(product).toBeTruthy();
        await apiRequest('DELETE', `/products/${product.uuid}`);
        await apiRequest('DELETE', `/master/warehouses/${warehouse.data.uuid}`);
        await apiRequest('DELETE', `/masters/price-levels/${priceLevel.data.uuid}`);
        try { await apiRequest('DELETE', `/accounts/${account.data.uuid}`); } catch {}
    });

    test('upload gambar dan pdf diterima di form produk', async () => {
        await page.goto('/master/products/create');
        const fileInput = page.getByTestId('input-image');

        await fileInput.setInputFiles({
            name: 'produk.png',
            mimeType: 'image/png',
            buffer: Buffer.from(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==',
                'base64',
            ),
        });
        await expect(page.locator('.upload-item')).toHaveCount(1);
        await expect(page.locator('.upload-preview')).toBeVisible();

        await fileInput.setInputFiles({
            name: 'dokumen-produk.pdf',
            mimeType: 'application/pdf',
            buffer: Buffer.from('%PDF-1.4\n1 0 obj\nendobj\n%%EOF'),
        });
        await expect(page.locator('.upload-item')).toHaveCount(2);
        await expect(page.locator('.upload-pdf-preview')).toBeVisible();
    });

    test('mengatur file utama dan membersihkan semua file', async () => {
        await openProductCreateForm(page);

        await uploadProductFiles(page, [
            productFilePayload('produk-pertama.png', 'image/png'),
            productFilePayload('produk-kedua.png', 'image/png'),
        ]);
        await expect(page.locator('.upload-item')).toHaveCount(2);
        await expect(page.locator('.upload-item').first().locator('.upload-file-info')).toContainText('produk-pertama.png');

        await page.locator('.upload-item').nth(1).getByTestId('btn-make-primary-image').click();
        await expect(page.locator('.upload-item').first().locator('.upload-file-info')).toContainText('produk-kedua.png');
        await expect(page.locator('.upload-item').first().locator('.upload-primary-badge')).toBeVisible();

        await page.getByTestId('btn-clear-image').click();
        await expect(page.locator('.upload-item')).toHaveCount(0);
    });

    test('upload file php ditolak dengan sweetalert', async () => {
        await page.goto('/master/products/create');

        await page.getByTestId('input-image').setInputFiles({
            name: 'payload.php',
            mimeType: 'application/x-httpd-php',
            buffer: Buffer.from('<?php echo 1;'),
        });

        const swalPopup = page.locator('.swal2-popup');
        await expect(swalPopup).toBeVisible({ timeout: 15000 });
        await expect(swalPopup).toContainText('Beberapa file ditolak');
        await expect(swalPopup).toContainText('Format tidak didukung');
        await expect(swalPopup).toContainText('payload.php');
        await expect(page.locator('.upload-item')).toHaveCount(0);
    });

    test('upload lebih dari 10 file ditolak dengan sweetalert', async () => {
        await page.goto('/master/products/create');
        const pngBuffer = Buffer.from(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==',
            'base64',
        );

        await page.getByTestId('input-image').setInputFiles(
            Array.from({ length: 11 }, (_, index) => ({
                name: `produk-${index + 1}.png`,
                mimeType: 'image/png',
                buffer: pngBuffer,
            })),
        );

        const swalPopup = page.locator('.swal2-popup');
        await expect(swalPopup).toBeVisible({ timeout: 15000 });
        await expect(swalPopup).toContainText('Melebihi batas 10 file');
        await expect(page.locator('.upload-item')).toHaveCount(10);
    });

    test('upload file lebih dari 2 MB ditolak dengan sweetalert', async () => {
        await page.goto('/master/products/create');

        await page.getByTestId('input-image').setInputFiles({
            name: 'terlalu-besar.png',
            mimeType: 'image/png',
            buffer: Buffer.alloc(2 * 1024 * 1024 + 1024, 1),
        });

        const swalPopup = page.locator('.swal2-popup');
        await expect(swalPopup).toBeVisible({ timeout: 15000 });
        await expect(swalPopup).toContainText('Ukuran melebihi 2 MB');
        await expect(page.locator('.upload-item')).toHaveCount(0);
    });
});

const PRODUCT_PNG_BUFFER = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFAAH/q842iQAAAABJRU5ErkJggg==',
    'base64',
);

function productFilePayload(name: string, mimeType: string, buffer: Buffer = PRODUCT_PNG_BUFFER): UploadPayload {
    return { name, mimeType, buffer };
}

async function openProductCreateForm(activePage: Page): Promise<void> {
    await activePage.goto('/master/products/create');
    await expect(activePage.getByTestId('product-form')).toBeVisible();
}

async function selectFirstVueOption(activePage: Page, testId: string): Promise<void> {
    const control = activePage.getByTestId(testId);
    await control.locator('.vs__dropdown-toggle').click();
    await activePage.locator('.vs__dropdown-option').first().waitFor({ state: 'visible' });
    await activePage.locator('.vs__dropdown-option').first().click();
}

async function uploadProductFiles(activePage: Page, files: UploadPayload | UploadPayload[]): Promise<void> {
    await activePage.getByTestId('input-image').setInputFiles(files);
}

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

async function apiRequest(method: 'GET' | 'POST' | 'PUT' | 'DELETE', path: string, payload?: JsonObject): Promise<JsonObject> {
    const response = await context.request.fetch(`/api/v1${path}`, {
        method,
        data: payload,
        headers: {
            Authorization: `Bearer ${apiToken}`,
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-Klinik-Company-Uuid': defaultCompanyUuid,
        },
    });

    if (!response.ok()) {
        throw new Error(`${method} ${path} gagal (${response.status()}): ${await response.text()}`);
    }

    if (response.status() === 204) {
        return {};
    }

    return await response.json() as JsonObject;
}

async function optionRows(type: string, search = ''): Promise<Option[]> {
    const query = new URLSearchParams({ type, search });
    const response = await apiRequest('GET', `/products/options?${query.toString()}`);
    return (response.data ?? []) as Option[];
}

async function optionLabel(option: Option): Promise<string> {
    return String(option.label ?? option.name ?? '');
}

async function ensureUnit(suffix: 'A' | 'B'): Promise<Option> {
    const name = `E2E Unit ${suffix}`;
    let rows = await optionRows('unit', name);
    let option = rows.find((row) => row.name === name);

    if (!option) {
        await apiRequest('POST', '/masters/units', {
            code: `E2E-UNIT-${suffix}`,
            name,
            symbol: suffix,
            is_active: true,
        });
        rows = await optionRows('unit', name);
        option = rows.find((row) => row.name === name);
    }

    if (!option) {
        throw new Error(`Satuan E2E ${suffix} tidak tersedia.`);
    }

    return option;
}

async function ensureLookupOption(setCode: string, key: string, suffix: 'A' | 'B'): Promise<Option> {
    const rows = await optionRows(setCode);
    const option = suffix === 'A' ? rows[0] : rows[1];

    if (!option) {
        throw new Error(`Lookup ${setCode} membutuhkan minimal dua nilai untuk E2E filter ${key}.`);
    }

    return option;
}

async function createFilterFixtures(): Promise<JsonObject> {
    const companies = await optionRows('company');
    const companyA = companies.find((row) => row.uuid === defaultCompanyUuid) ?? companies[0];
    const companyB = companies.find((row) => row.uuid !== companyA?.uuid);
    if (!companyA || !companyB) {
        throw new Error('Butuh minimal dua perusahaan untuk E2E filter perusahaan.');
    }

    const users = await optionRows('user');
    const currentUser = users.find((row) => row.uuid === currentUserUuid);
    const otherUser = users.find((row) => row.uuid !== currentUserUuid);
    if (!currentUser || !otherUser) {
        throw new Error('Butuh minimal dua user untuk E2E filter user.');
    }

    const [unitA, unitB] = await Promise.all([ensureUnit('A'), ensureUnit('B')]);
    const [kindA, kindB] = await Promise.all([
        ensureLookupOption('JENIS', 'Kind', 'A'),
        ensureLookupOption('JENIS', 'Kind', 'B'),
    ]);
    const [groupA, groupB] = await Promise.all([
        ensureLookupOption('KELOMPOK', 'Group', 'A'),
        ensureLookupOption('KELOMPOK', 'Group', 'B'),
    ]);
    const [typeA, typeB] = await Promise.all([
        ensureLookupOption('TIPE', 'Type', 'A'),
        ensureLookupOption('TIPE', 'Type', 'B'),
    ]);
    const [brandA, brandB] = await Promise.all([
        ensureLookupOption('MERK', 'Brand', 'A'),
        ensureLookupOption('MERK', 'Brand', 'B'),
    ]);
    const [originA, originB] = await Promise.all([
        ensureLookupOption('MADE_IN', 'Origin', 'A'),
        ensureLookupOption('MADE_IN', 'Origin', 'B'),
    ]);
    const [gradeA, gradeB] = await Promise.all([
        ensureLookupOption('GRADE', 'Grade', 'A'),
        ensureLookupOption('GRADE', 'Grade', 'B'),
    ]);

    const fixtureConfig = {
        companyA: companyA.uuid,
        companyB: companyB.uuid,
        currentUser: currentUser.uuid,
        otherUser: otherUser.uuid,
        unitA: unitA.uuid,
        unitB: unitB.uuid,
        kindA: kindA.uuid,
        kindB: kindB.uuid,
        groupA: groupA.uuid,
        groupB: groupB.uuid,
        typeA: typeA.uuid,
        typeB: typeB.uuid,
        brandA: brandA.uuid,
        brandB: brandB.uuid,
        originA: originA.uuid,
        originB: originB.uuid,
        gradeA: gradeA.uuid,
        gradeB: gradeB.uuid,
    };
    const encodedConfig = Buffer.from(JSON.stringify(fixtureConfig), 'utf8').toString('base64');
    execFileSync('php', ['artisan', 'e2e:product-filter-fixtures', encodedConfig], {
        cwd: process.cwd(),
        stdio: 'inherit',
    });

    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const nextPeriodDate = new Date(today.getFullYear(), today.getMonth() + 1, 1);
    const isoDate = (value: Date) => value.toISOString().slice(0, 10);
    const period = (value: Date) => `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(2, '0')}`;

    return {
        companyA,
        companyB,
        currentUser,
        otherUser,
        unitA,
        unitB,
        kindA,
        kindB,
        groupA,
        groupB,
        typeA,
        typeB,
        brandA,
        brandB,
        originA,
        originB,
        gradeA,
        gradeB,
        today: isoDate(today),
        tomorrow: isoDate(tomorrow),
        currentPeriod: period(today),
        nextPeriod: period(nextPeriodDate),
    };
}

async function removeFixtureProducts(): Promise<void> {
    const response = await apiRequest(
        'GET',
        `/products?filter_code=${encodeURIComponent(CODE_PREFIX)}&per_page=100`,
    );
    const rows = (response.data ?? []) as JsonObject[];

    for (const row of rows) {
        await apiRequest('DELETE', `/products/${row.uuid}`);
    }
}

async function selectAsync(testId: string, option: Option): Promise<void> {
    const control = page.getByTestId(testId);
    const label = await optionLabel(option);
    await control.click();

    const search = control.locator('.vs__search');
    await search.fill(label);
    const dropdownOption = page.locator('.vs__dropdown-option').filter({ hasText: label }).first();
    await expect(dropdownOption).toBeVisible({ timeout: 15000 });
    await dropdownOption.click();
}

async function fillFilterField(key: string, value: unknown): Promise<void> {
    const asyncFields: Record<string, string> = {
        filter_company_uuid: 'filter-company',
        filter_unit: 'filter-unit',
        filter_group_uuid: 'filter-group',
        filter_kind: 'filter-kind',
        filter_type_uuid: 'filter-type',
        filter_brand_uuid: 'filter-brand',
        filter_origin_uuid: 'filter-origin',
        filter_grade_uuid: 'filter-grade',
        filter_created_by: 'filter-created-by',
        filter_updated_by: 'filter-updated-by',
    };

    if (asyncFields[key]) {
        await selectAsync(asyncFields[key], value as Option);
        return;
    }

    const textFieldTestIds: Record<string, string> = {
        filter_barcode: 'filter-barcode',
        filter_code: 'filter-code',
        filter_name: 'filter-name',
        filter_keterangan: 'filter-keterangan',
        filter_created_from: 'filter-created-from',
        filter_created_to: 'filter-created-to',
        filter_updated_from: 'filter-updated-from',
        filter_updated_to: 'filter-updated-to',
        filter_periode: 'filter-periode',
    };

    await page.getByTestId(textFieldTestIds[key] ?? key).fill(String(value));
}

async function applyFilter(values: JsonObject): Promise<void> {
    await page.getByTestId('btn-open-product-filter').click();
    await expect(page.getByTestId('product-filter-modal')).toBeVisible({ timeout: 15000 });
    await page.getByTestId('btn-reset-product-filter').click();

    for (const [key, value] of Object.entries(values)) {
        await fillFilterField(key, value);
    }

    await page.getByTestId('btn-apply-product-filter').click();
    await expect(page.getByTestId('product-filter-modal')).toHaveCount(0);
}

async function expectRow(code: string, visible: boolean): Promise<void> {
    const row = page.locator('tbody tr').filter({ hasText: code });

    if (visible) {
        await expect(row).toHaveCount(1, { timeout: 15000 });
    } else {
        await expect(row).toHaveCount(0, { timeout: 15000 });
    }
}
