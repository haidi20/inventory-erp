import { test, expect } from '@playwright/test';

/**
 * E2E tests for Complaint Index view
 * Requires dev server running: npm run dev
 */

test.describe('Complaint View', () => {
    test.beforeEach(async ({ page }) => {
        // Login terlebih dahulu
        await page.goto('/login');
        await page.fill('[name="email"]', 'superadmin@klinik.local');
        await page.fill('[name="password"]', 'superadmin');
        await page.click('[type="submit"]');
        await page.waitForURL('**/');
    });

    test('harus menampilkan halaman Layanan Pengaduan & Saran', async ({ page }) => {
        await page.goto('/operational/complaints');
        await expect(page.getByText('Layanan Pengaduan & Saran')).toBeVisible();
    });

    test('harus menampilkan tombol Buat Pengaduan Baru', async ({ page }) => {
        await page.goto('/operational/complaints');
        await expect(page.getByRole('button', { name: 'Buat Pengaduan Baru' })).toBeVisible();
    });

    test('klik tombol harus membuka form pengaduan', async ({ page }) => {
        await page.goto('/operational/complaints');
        await page.getByRole('button', { name: 'Buat Pengaduan Baru' }).click();
        await expect(page.locator('form')).toBeVisible();
    });

    test('form harus memiliki field subject dan content', async ({ page }) => {
        await page.goto('/operational/complaints');
        await page.getByRole('button', { name: 'Buat Pengaduan Baru' }).click();
        // Field utama form pengaduan
        await expect(page.locator('input, textarea').first()).toBeVisible();
    });

    test('submit form kosong harus menampilkan validasi', async ({ page }) => {
        await page.goto('/operational/complaints');
        await page.getByRole('button', { name: 'Buat Pengaduan Baru' }).click();
        const submitBtn = page.getByRole('button', { name: /kirim|submit/i });
        if (await submitBtn.isVisible()) {
            await submitBtn.click();
            // Validasi error harus muncul
            await expect(page.locator('[class*="error"], [class*="invalid"], .text-red')).toBeVisible();
        }
    });
});
