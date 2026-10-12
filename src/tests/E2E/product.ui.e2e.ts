import { test } from '@e2e-dev/web';
import { expect } from 'e2e';
import { apiResponse, firstResourceItem, resourceUuid, silentDelete, uniqueCode } from './support/api';

test.describe('Product master UI', { session: 'superadmin', tags: ['operational', 'ui'] }, () => {
    test('opens product list and create form', async ({ app, browser }) => {
        await app.open('/master/products');
        await expect(browser.locator('[data-testid="btn-create-product"]')).toBeVisible();
        await expect(browser.locator('[data-testid="btn-export-product"]')).toBeVisible();

        await app.open('/master/products/create');
        await expect(browser.locator('[data-testid="product-form"]')).toBeVisible();
        await expect(browser.locator('[data-testid="input-code"]')).toHaveValue('');
    });

    test('loads an existing product and every edit tab', async ({ app, browser }) => {
        const code = uniqueCode('UI');
        const unit = await firstResourceItem(app, '/products/options?type=unit');
        const warehouse = await firstResourceItem(app, '/products/options?type=warehouse');
        const account = await firstResourceItem(app, '/products/options?type=account');

        const priceLevel = await apiResponse(app, '/masters/price-levels', {
            method: 'POST',
            body: JSON.stringify({ code, name: `E2E Level UI ${code}`, is_active: true }),
        });
        expect(priceLevel.status).toBe(200);
        const priceLevelUuid = resourceUuid(priceLevel.body);

        const product = await apiResponse(app, '/products', {
            method: 'POST',
            body: JSON.stringify({
                code,
                name: `E2E Produk UI ${code}`,
                description: 'Produk verifikasi form E2E',
                barcode: code,
                base_unit_uuid: unit.uuid,
                price_buy: 15000,
                price_sell: 20000,
                min_stock: 3,
                max_stock: 90,
                is_active: true,
                units: [{ unit_uuid: unit.uuid, conversion_ratio: 1, is_default: true, is_active: true }],
                accounts: [{ account_role: 'sales', account_uuid: account.uuid }],
                opening_balances: [{ location_uuid: warehouse.uuid, period: '2026-01', opening_qty: 12 }],
                prices: [{ price_level_uuid: priceLevelUuid, price_buy: 15000, price_sell: 20000, base_qty: 1, is_active: true }],
                discounts: [{ quantity: 10, unit_uuid: unit.uuid, discount_percent: 5, base_qty: 1 }],
            }),
        });
        expect(product.status).toBe(200);
        const productUuid = resourceUuid(product.body);

        try {
            await app.open(`/master/products/${productUuid}/edit`);
            await expect(browser.locator('[data-testid="product-form"]')).toBeVisible();
            await expect(browser.locator('[data-testid="input-code"]')).toHaveValue(code);
            await expect(browser.locator('[data-testid="input-name"]')).toHaveValue(`E2E Produk UI ${code}`);
            await expect(browser.locator('[data-testid="input-barcode"]')).toHaveValue(code);

            for (const tab of ['detail', 'account', 'saldo', 'harga', 'diskon']) {
                await browser.locator(`[data-testid="tab-${tab}"]`).click();
                await expect(browser.locator(`[data-testid="tab-${tab}"]`)).toBeVisible();
            }

            await browser.locator('[data-testid="tab-detail"]').click();
            await expect(browser.locator('[data-testid="unit-row-ratio"]').first()).toHaveValue('1');
            await browser.locator('[data-testid="tab-saldo"]').click();
            await expect(browser.locator('[data-testid="opening-row-qty"]').first()).toHaveValue('12');
            await browser.locator('[data-testid="tab-harga"]').click();
            await expect(browser.locator('[data-testid="price-row-sell"]').first()).toHaveValue('20000');
            await browser.locator('[data-testid="tab-diskon"]').click();
            await expect(browser.locator('[data-testid="discount-row-percent"]').first()).toHaveValue('5');
        } finally {
            await silentDelete(app, `/products/${productUuid}`);
            await silentDelete(app, `/masters/price-levels/${priceLevelUuid}`);
        }
    });
});
