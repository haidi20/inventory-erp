import { execSync } from 'node:child_process';
import { writeFileSync } from 'node:fs';
import { expect, test } from '@playwright/test';

test.describe.serial('Inventory real-data flow', () => {
    test.beforeAll(() => {
        execSync('php artisan e2e:inventory-real-data-fixtures', { stdio: 'inherit' });
    });

    test.afterAll(() => {
        execSync('php artisan e2e:inventory-real-data-fixtures --cleanup', { stdio: 'inherit' });
    });

    test('opening balance menampilkan data nyata', async ({ page }) => {
        await page.goto('/stock/opening-balance');

        writeFileSync('test-results/opening.txt', await page.locator('body').innerText());
        await page.screenshot({ path: 'test-results/opening.png', fullPage: true });

        await expect(page).toHaveTitle(/Saldo Awal Stok/i);
        await expect(page.getByText('E2E-INV-REAL-ITEM')).toBeVisible();
        await expect(page.getByText('E2E Real Data Opening Barcode')).toBeVisible();
        await expect(page.getByText('25')).toBeVisible();
    });

    test('BOM formula menampilkan paket dan komponen nyata', async ({ page }) => {
        await page.goto('/stock/bom-formula');

        await expect(page).toHaveTitle(/Formula \(BOM\)/i);
        await expect(page.getByText('E2E-INV-REAL-BOM')).toBeVisible();
        await expect(page.getByText('E2E Real Data BOM Formula')).toBeVisible();
        await expect(page.getByText('2')).toBeVisible();
    });

    test('barcode print menampilkan barcode item nyata', async ({ page }) => {
        await page.goto('/print-barcode');

        await expect(page).toHaveTitle(/Cetak Barcode/i);
        await expect(page.getByText('E2E-INV-REAL-ITEM')).toBeVisible();
        await expect(page.getByText('E2E-INV-REAL-BARCODE')).toBeVisible();
    });
});
