import { test, expect } from '@playwright/test';

/**
 * Backup UI/UX E2E Test
 * Standar Klinik 2026: Desktop-Grade Productivity & Informative UI
 */

test.describe('Backup & Restore UI/UX', () => {
    test.setTimeout(60000);

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.click('#openInfoModal');
        await page.locator('.btn-use-account[data-email="superadmin"]').click();
        await page.click('button[type="submit"]');
        await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 20000 });
        await page.goto('/settings/backup', { waitUntil: 'networkidle' });
    });

    test('harus menampilkan layout dashboard backup yang informatif (Hero Section)', async ({ page }) => {
        const hero = page.locator('.db-hero');
        await expect(hero).toBeVisible({ timeout: 15000 });
        await expect(hero.locator('.db-title')).toContainText('Backup Data Klinik');

        const chips = hero.locator('.db-chip');
        await expect(chips).toHaveCount(3);
        await expect(hero).toContainText('Status Sistem');
        await expect(hero).toContainText('Penyimpanan');
    });

    test('interaksi modal restore dan side help drawer harus berjalan baik', async ({ page }) => {
        const btnOpenRestore = page.getByTestId('btn-open-restore');
        await btnOpenRestore.click();

        const modal = page.locator('.modal-card');
        await expect(modal).toBeVisible();
        await expect(modal).toContainText('Restore Cadangan');
        await expect(page.getByTestId('input-backup-file')).toBeVisible();
        await expect(page.getByTestId('checkbox-pre-restore-backup')).toBeChecked();

        const infoIcon = modal.locator('.info-icon');
        await infoIcon.click();

        const drawer = page.locator('.help-drawer');
        await expect(drawer).toBeVisible();
        await expect(drawer).toContainText('Bantuan: Restore Data');

        await page.keyboard.press('Escape');
        await expect(drawer).not.toBeVisible();
        await expect(modal).toBeVisible();
    });

    test('shortcut F1 harus bisa memicu toggle bantuan di dalam modal', async ({ page }) => {
        await page.getByTestId('btn-open-restore').click();
        const modal = page.locator('.modal-card');
        await expect(modal).toBeVisible();

        await page.keyboard.press('F1');
        const drawer = page.locator('.help-drawer');
        await expect(drawer).toBeVisible();

        await page.keyboard.press('F1');
        await expect(drawer).not.toBeVisible();
    });

    test('tampilan harus responsif pada viewport mobile', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 667 });
        await page.goto('/settings/backup', { waitUntil: 'networkidle' });

        await page.getByTestId('btn-open-restore').click();

        const modal = page.locator('.modal-card');
        await expect(modal).toBeVisible();
        const box = await modal.boundingBox();
        expect(box).not.toBeNull();
        expect(box!.width).toBeLessThanOrEqual(375);
    });
});
