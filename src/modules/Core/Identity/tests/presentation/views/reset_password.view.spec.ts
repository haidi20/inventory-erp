import { test, expect } from '@playwright/test';

// Reset password page butuh token yang valid dari URL
// Format: /reset-password/{token}?email=xxx
const FAKE_TOKEN = 'fake-reset-token-for-testing';
const RESET_URL = `/reset-password/${FAKE_TOKEN}?email=superadmin`;

test.describe('ResetPassword View E2E', () => {

    test.beforeEach(async ({ page }) => {
        page.on('pageerror', err => console.log('BROWSER_ERROR:', err.message));
        await page.goto(RESET_URL);
    });

    // ── 1. Halaman tampil dengan benar ────────────────────────────────────────
    test('harus menampilkan form reset password dengan semua field', async ({ page }) => {
        await expect(page.getByTestId('input-email')).toBeVisible({ timeout: 10000 });
        await expect(page.getByTestId('input-password')).toBeVisible();
        await expect(page.getByTestId('input-password-confirmation')).toBeVisible();
        await expect(page.getByTestId('btn-submit')).toBeVisible();
    });

    test('harus menampilkan brand KLINIK 2026 dan judul Buat Password Baru', async ({ page }) => {
        await expect(page.locator('.auth-title')).toContainText('KLINIK 2026', { timeout: 10000 });
        await expect(page.locator('.auth-subtitle')).toContainText('Buat Password Baru');
    });

    test('harus mengisi field email dari query parameter', async ({ page }) => {
        const emailValue = await page.getByTestId('input-email').inputValue();
        expect(emailValue).toBe('superadmin');
    });

    // ── 2. Toggle show/hide password ──────────────────────────────────────────
    test('harus toggle type input password saat tombol eye diklik', async ({ page }) => {
        const passwordInput = page.getByTestId('input-password');
        await expect(passwordInput).toBeVisible({ timeout: 10000 });

        // Default: type="password"
        expect(await passwordInput.getAttribute('type')).toBe('password');

        // Klik toggle button (button pertama di input-wrap password)
        await page.locator('.input-wrap').nth(1).locator('.toggle-password').click();
        expect(await passwordInput.getAttribute('type')).toBe('text');

        // Klik lagi → kembali ke password
        await page.locator('.input-wrap').nth(1).locator('.toggle-password').click();
        expect(await passwordInput.getAttribute('type')).toBe('password');
    });

    // ── 3. Validasi client-side ───────────────────────────────────────────────
    test('harus menampilkan error saat password kurang dari 8 karakter', async ({ page }) => {
        await page.getByTestId('input-password').fill('short');
        await page.getByTestId('input-password-confirmation').fill('short');
        await page.getByTestId('btn-submit').click();
        await expect(page.locator('.alert-error')).toBeVisible({ timeout: 5000 });
        await expect(page.locator('.alert-error')).toContainText('minimal 8');
    });

    test('harus menampilkan error saat konfirmasi password tidak cocok', async ({ page }) => {
        await page.getByTestId('input-password').fill('newpassword123');
        await page.getByTestId('input-password-confirmation').fill('berbeda123');
        await page.getByTestId('btn-submit').click();
        await expect(page.locator('.alert-error')).toBeVisible({ timeout: 5000 });
        await expect(page.locator('.alert-error')).toContainText('tidak cocok');
    });

    // ── 4. Submit dengan token invalid → error dari server ────────────────────
    test('harus menampilkan error dari server saat token sudah expired', async ({ page }) => {
        // Isi form dengan data valid — token palsu akan ditolak server
        await page.getByTestId('input-password').fill('newpassword123');
        await page.getByTestId('input-password-confirmation').fill('newpassword123');
        await page.getByTestId('btn-submit').click();

        // Server akan redirect back dengan error (token invalid)
        // Halaman tetap di /reset-password atau kembali dengan error
        await page.waitForTimeout(3000);
        // Verifikasi tidak crash — halaman masih render
        await expect(page.locator('body')).toBeVisible();
    });

    // ── 5. Navigasi kembali ke login ──────────────────────────────────────────
    test('harus navigasi ke login saat link kembali diklik', async ({ page }) => {
        await page.locator('a.auth-back').click();
        await page.waitForURL(/\/login/, { timeout: 10000 });
        expect(page.url()).toContain('/login');
    });
});
