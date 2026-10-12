import { execFileSync } from 'node:child_process';
import { expect, test, type Page } from '@playwright/test';

type Option = { uuid: string; label?: string; name?: string };
let page: Page;
let token = '';
let productUuid = '';
const code = 'E2E-FRM-ALL-TABS';

test('form produk create dan update semua tab melalui UI', async ({ page: authenticatedPage }) => {
    page = authenticatedPage;
    execFileSync('php', ['artisan', 'e2e:product-form-fixtures'], { cwd: process.cwd(), stdio: 'inherit' });
    try {
        await page.goto('/master/products/create');
        await expect(page.getByTestId('product-form')).toBeVisible();

        const props = await page.evaluate(() => JSON.parse(document.querySelector('script[data-page="app"]')?.textContent ?? '{}'));
        token = props.props.auth.api_token;
        const unit = first(props.props.options.units, 'units');
        const warehouse = first(props.props.options.warehouses, 'warehouses');
        const priceLevel = first(props.props.options.priceLevels, 'priceLevels');
        const account = first(props.props.options.accounts, 'accounts');

        await page.getByTestId('input-code').fill(code);
        await page.getByTestId('input-name').fill('Produk E2E Semua Tab');
        await page.getByTestId('input-kemasan').fill('Kemasan E2E');
        await page.getByTestId('input-description').fill('Deskripsi E2E');
        await page.getByTestId('input-barcode').fill('899000000099');
        await selectVue('select-unit', unit);
        await page.getByTestId('input-location').fill('RAK-E2E');
        await page.getByTestId('input-min-stock').fill('5');
        await page.getByTestId('input-max-stock').fill('100');
        await page.getByTestId('input-max-order').fill('40');
        await page.getByTestId('input-opening-cost').fill('7250.25');
        await page.getByTestId('input-note').fill('Catatan E2E');

        await page.getByTestId('btn-add-unit-row').click();
        await selectVue('unit-row-select', unit);
        await page.getByTestId('unit-row-ratio').fill('7.5');
        await page.getByTestId('unit-row-note').fill('Catatan satuan E2E');
        await page.getByTestId('unit-row-default').check();

        await page.getByTestId('tab-account').click();
        await selectVue('account-row-select-sales', account);

        await page.getByTestId('tab-saldo').click();
        await page.getByTestId('btn-add-opening-balance-row').click();
        await selectVue('opening-row-location', warehouse);
        await page.getByTestId('opening-row-qty').fill('25');

        await page.getByTestId('tab-harga').click();
        await page.getByTestId('btn-add-price-row').click();
        await selectVue('price-row-level', priceLevel);
        await page.getByTestId('price-row-buy').fill('8250.5');
        await page.getByTestId('price-row-sell').fill('14250.75');
        await page.getByTestId('price-row-discount-amount').fill('950.25');
        await page.getByTestId('price-row-discount-percent').fill('8.5');
        await page.getByTestId('price-row-base-qty').fill('2.5');

        await page.getByTestId('tab-diskon').click();
        await page.getByTestId('btn-add-discount-row').click();
        await page.getByTestId('discount-row-quantity').fill('3');
        await selectVue('discount-row-unit', unit);
        await page.getByTestId('discount-row-percent').fill('12.5');
        await page.getByTestId('discount-row-base-qty').fill('4.5');

        const createResponsePromise = page.waitForResponse((response) =>
            response.url().includes('/api/v1/products') && response.request().method() === 'POST',
        );
        await page.getByTestId('btn-submit-product').click();
        const createResponse = await createResponsePromise;
        expect(createResponse.ok()).toBeTruthy();
        await expect(page).toHaveURL(/\/master\/products\/create$/, { timeout: 30000 });
        productUuid = (await createResponse.json()).data.uuid;
        let product = await getProduct();
        expect(product).toMatchObject({ code, name: 'Produk E2E Semua Tab' });
        expect(product.units[0]).toMatchObject({ unit_uuid: unit.uuid, conversion_ratio: 7.5 });

        await page.goto(`/master/products/create?edit=${productUuid}`);
        await page.getByTestId('input-name').fill('Produk E2E Semua Tab Updated');
        await page.getByTestId('tab-detail').click();
        await page.getByTestId('unit-row-ratio').fill('9.25');
        await page.getByTestId('tab-harga').click();
        await page.getByTestId('price-row-buy').fill('9375.25');
        await page.getByTestId('price-row-sell').fill('16875.5');
        await page.getByTestId('tab-diskon').click();
        await page.getByTestId('discount-row-percent').fill('0');
        await page.getByTestId('discount-row-amount').fill('2500');
        const updateResponsePromise = page.waitForResponse((response) =>
            response.url().includes('/api/v1/products') && response.request().method() === 'PUT',
        );
        await page.getByTestId('btn-submit-product').click();
        expect((await updateResponsePromise).ok()).toBeTruthy();
        await expect(page).toHaveURL(/\/master\/products\/create(\?.*)?$/, { timeout: 30000 });
        product = await getProduct();
        expect(product.name).toBe('Produk E2E Semua Tab Updated');
        expect(product.units[0].conversion_ratio).toBe(9.25);

        const deleteResponse = await page.request.delete(`/api/v1/products/${productUuid}`, {
            headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
        });
        expect(deleteResponse.status()).toBe(204);
        const deletedResponse = await page.request.get(`/api/v1/products/${productUuid}`, {
            headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
        });
        expect(deletedResponse.status()).toBe(404);
        productUuid = '';
    } finally {
        if (productUuid) await page.request.delete(`/api/v1/products/${productUuid}`, { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } });
        await page.close();
    }
});

function first(options: Option[] | undefined, name: string): Option { if (!options?.[0]) throw new Error(`Opsi ${name} tidak tersedia`); return options[0]; }
async function selectVue(testId: string, option: Option): Promise<void> {
    const control = page.getByTestId(testId);
    await control.locator('.vs__dropdown-toggle').click();
    await page.locator('.vs__dropdown-option').first().waitFor({ state: 'visible' });
    const label = option.label ?? option.name ?? '';
    await control.locator('.vs__search').fill(label);
    await page.locator('.vs__dropdown-option').filter({ hasText: label }).first().click();
}
async function findUuid(): Promise<string> { const response = await page.request.get(`/api/v1/products?filter_code=${code}`, { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } }); expect(response.ok()).toBeTruthy(); return (await response.json()).data[0].uuid; }
async function getProduct(): Promise<any> { const response = await page.request.get(`/api/v1/products/${productUuid}`, { headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' } }); expect(response.ok()).toBeTruthy(); return (await response.json()).data; }
test('form produk menangani upload, PDF, reorder, batas jumlah, dan ukuran file', async ({ page: authenticatedPage }) => {
    page = authenticatedPage;
    execFileSync('php', ['artisan', 'e2e:product-form-fixtures'], { cwd: process.cwd(), stdio: 'inherit' });
    try {
        await page.goto('/master/products/create');
        await expect(page.getByTestId('product-form')).toBeVisible();

        const png = Buffer.from('not-a-real-png');
        const pdf = Buffer.from('%PDF-1.4\nfixture');
        await page.getByTestId('input-image').setInputFiles([
            { name: 'upload-one.png', mimeType: 'image/png', buffer: png },
            { name: 'upload-two.pdf', mimeType: 'application/pdf', buffer: pdf },
        ]);
        await expect(page.locator('.upload-item')).toHaveCount(2);
        await expect(page.locator('.upload-pdf-preview')).toHaveCount(1);

        await page.getByTestId('btn-make-primary-image').click();
        await expect(page.locator('.upload-item').first()).toContainText('upload-two.pdf');
        await page.getByTestId('btn-remove-image').last().click();
        await expect(page.locator('.upload-item')).toHaveCount(1);
        await page.getByTestId('btn-clear-image').click();
        await expect(page.locator('.upload-item')).toHaveCount(0);

        await page.getByTestId('input-image').setInputFiles(Array.from({ length: 11 }, (_, index) => ({
            name: `limit-${index + 1}.png`,
            mimeType: 'image/png',
            buffer: png,
        })));
        await expect(page.locator('.upload-item')).toHaveCount(10);
        await expect(page.locator('.swal2-popup')).toContainText('Melebihi batas 10 file');
        await page.keyboard.press('Escape');
        await expect(page.locator('.swal2-popup')).toHaveCount(0);
        await page.getByTestId('btn-clear-image').click();

        const oversized = Buffer.alloc(2 * 1024 * 1024 + 1, 1);
        await page.getByTestId('input-image').setInputFiles({ name: 'oversized.png', mimeType: 'image/png', buffer: oversized });
        await expect(page.locator('.upload-item')).toHaveCount(0);
    } finally {
        await page.close();
    }
});
