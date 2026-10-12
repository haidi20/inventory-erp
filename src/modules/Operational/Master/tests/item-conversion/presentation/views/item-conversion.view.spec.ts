import { test, expect } from '@playwright/test';

/**
 * E2E tests for ItemConversionIndex view
 * Requires dev server running: npm run dev
 */

test.describe('ItemConversion View', () => {
    test.beforeEach(async ({ page }) => {
        // Login terlebih dahulu
        await page.goto('/login');
        await page.fill('[name="email"]', 'admin@klinik.local');
        await page.fill('[name="password"]', 'password');
        await page.click('[type="submit"]');
        await page.waitForURL('**/');
    });

    test('harus menampilkan halaman Konversi Satuan Barang', async ({ page }) => {
        await page.goto('/masters/item-conversions');
        await expect(page.getByText('Konversi Satuan Barang')).toBeVisible();
    });

    test('harus menampilkan tombol Tambah Konversi', async ({ page }) => {
        await page.goto('/masters/item-conversions');
        await expect(page.getByRole('button', { name: 'Tambah Konversi' })).toBeVisible();
    });

    test('klik Tambah Konversi harus membuka form', async ({ page }) => {
        await page.goto('/masters/item-conversions');
        await page.getByRole('button', { name: 'Tambah Konversi' }).click();
        // Form modal/panel harus muncul
        await expect(page.locator('form')).toBeVisible();
    });

    test('harus menampilkan tabel data konversi', async ({ page }) => {
        await page.goto('/masters/item-conversions');
        await expect(page.locator('table')).toBeVisible();
    });

    test('harus bisa melakukan pencarian', async ({ page }) => {
        await page.goto('/masters/item-conversions');
        const searchInput = page.locator('input[type="text"], input[type="search"]').first();
        await searchInput.fill('tablet');
        await expect(searchInput).toHaveValue('tablet');
    });
});
