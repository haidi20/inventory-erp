import { expect, test } from '@playwright/test';

test.describe.serial('Inventory real-DB UI', () => {
    test('inventory menu dapat dibuka superadmin', async ({ page }) => {
        await page.goto('/inventory');

        await expect(page).toHaveTitle(/Inventory/i);
        await expect(page.getByText('Menu Inventory')).toBeVisible();
    });

    test('stock value list dapat dirender tanpa fatal error', async ({ page }) => {
        await page.goto('/stock/value-list');

        await expect(page).toHaveTitle(/Nilai Stok/i);
        await expect(page.getByText('Daftar nilai stok produk per gudang.')).toBeVisible();
        await expect(page.locator('.dt-table')).toBeVisible();
    });

    test('BOM formula dapat dirender tanpa fatal error', async ({ page }) => {
        await page.goto('/stock/bom-formula');

        await expect(page).toHaveTitle(/Formula \(BOM\)/i);
        await expect(page.getByText('Kelola bill of materials / formula produksi.')).toBeVisible();
        await expect(page.locator('.dt-table')).toBeVisible();
    });

    test('opening balance dapat dirender tanpa fatal error', async ({ page }) => {
        await page.goto('/stock/opening-balance');

        await expect(page).toHaveTitle(/Saldo Awal Stok/i);
        await expect(page.getByText('Set saldo awal stok produk per gudang.')).toBeVisible();
        await expect(page.locator('.dt-table')).toBeVisible();
    });

    test('barcode print dapat dirender tanpa fatal error', async ({ page }) => {
        await page.goto('/print-barcode');

        await expect(page).toHaveTitle(/Cetak Barcode/i);
        await expect(page.getByText('Cetak label barcode produk.')).toBeVisible();
        await expect(page.locator('.dt-table')).toBeVisible();
    });
});
