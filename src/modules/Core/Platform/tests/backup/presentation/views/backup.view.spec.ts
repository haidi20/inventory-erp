import { test, expect } from '@playwright/test';

test.describe('Backup View E2E', () => {
    test.setTimeout(90000); // Increase timeout to 90s

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.click('#openInfoModal');
        await page.locator('.btn-use-account[data-email="superadmin"]').click();
        await page.click('button[type="submit"]');
        await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 30000 });
        await page.goto('/settings/backup');
    });

    test('harus memuat halaman Backup & Restore', async ({ page }) => {
        await page.goto('/settings/backup', { waitUntil: 'networkidle' });
        await expect(page.locator('.db-hero')).toBeVisible({ timeout: 30000 });
        await expect(page.locator('.db-title')).toContainText('Backup Data Klinik');
        await expect(page.getByTestId('input-search-history')).toBeVisible({ timeout: 30000 });
        await expect(page.locator('table')).toBeVisible({ timeout: 30000 });
    });

    test('harus bisa melakukan filter pencarian riwayat', async ({ page }) => {
        const searchInput = page.getByTestId('input-search-history');
        await expect(searchInput).toBeVisible({ timeout: 30000 });
        
        await searchInput.fill('test-search');
        await page.waitForTimeout(2000); 
        await expect(page.locator('table')).toBeVisible();
    });

    test('harus bisa membuka modal restore', async ({ page }) => {
        const btnOpenRestore = page.getByTestId('btn-open-restore');
        await expect(btnOpenRestore).toBeVisible({ timeout: 30000 });
        
        await btnOpenRestore.click();
        
        await expect(page.locator('.modal-card')).toBeVisible({ timeout: 15000 });
        await expect(page.locator('body')).toContainText('Restore Cadangan');
    });

    test('tombol Export Backup SQL harus memicu download file backup', async ({ page }) => {
        const btnManualBackup = page.getByTestId('btn-manual-backup');
        await expect(btnManualBackup).toBeVisible({ timeout: 30000 });

        const downloadPromise = page.waitForEvent('download');
        await btnManualBackup.click();
        const download = await downloadPromise;
        expect(download.suggestedFilename()).toMatch(/backup_.*\.sql$/);
    });

    test('harus menampilkan konfirmasi saat restore dari file cadangan', async ({ page }) => {
        const btnOpenRestore = page.getByTestId('btn-open-restore');
        await btnOpenRestore.click();
        
        const fileInput = page.getByTestId('input-backup-file');
        
        const minimalZip = Buffer.from([
            0x50, 0x4b, 0x03, 0x04, 0x0a, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 
            0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00
        ]);

        await fileInput.setInputFiles({
            name: 'test_backup.zip',
            mimeType: 'application/zip',
            buffer: minimalZip
        });

        const btnSubmit = page.getByTestId('btn-submit-restore');
        await btnSubmit.click();

        await expect(page.locator('.swal2-popup')).toBeVisible({ timeout: 15000 });
        await expect(page.locator('.swal2-popup')).toContainText('Konfirmasi Restore');
    });
});
