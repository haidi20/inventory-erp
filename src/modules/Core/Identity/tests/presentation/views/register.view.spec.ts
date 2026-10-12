import { test, expect } from '@playwright/test';

test.describe.serial('Register E2E', () => {
    const unique = Date.now();
    const company = `Klinik E2E ${unique}`;
    const email = `owner.${unique}@e2e.test`;
    const username = `owner_e2e_${unique}`;
    const password = 'password123';

    test('menampilkan form register perusahaan', async ({ page }) => {
        await page.goto('/register');

        await expect(page.getByTestId('input-company-name')).toBeVisible();
        await expect(page.getByTestId('select-timezone')).toBeVisible();
        await expect(page.getByTestId('select-timezone')).toHaveValue('Asia/Makassar');
        await expect(page.getByTestId('input-name')).toBeVisible();
        await expect(page.getByTestId('input-email')).toBeVisible();
        await expect(page.getByTestId('input-username')).toBeVisible();
        await expect(page.getByTestId('input-password')).toBeVisible();
        await expect(page.getByTestId('input-password-confirmation')).toBeVisible();

        const timezoneOptionCount = await page.getByTestId('select-timezone').locator('option').count();
        expect(timezoneOptionCount).toBeGreaterThan(10);
    });

    test('mendaftarkan perusahaan lalu login ulang', async ({ page }) => {
        await page.goto('/register');

        await page.getByTestId('input-company-name').fill(company);
        await page.getByTestId('select-timezone').selectOption('Asia/Makassar');
        await page.getByTestId('input-name').fill('Owner E2E');
        await page.getByTestId('input-email').fill(email);
        await page.getByTestId('input-username').fill(username);
        await page.getByTestId('input-password').fill(password);
        await page.getByTestId('input-password-confirmation').fill(password);
        await page.getByTestId('btn-register').click();

        await page.waitForURL((url) => !url.pathname.includes('/register') && !url.pathname.includes('/login'), {
            timeout: 30000,
        });
        await expect(page.locator('.page-title')).toContainText('DASHBOARD INVENTORY');

        await page.context().clearCookies();
        await page.goto('/login');
        await page.locator('input[name="email"]').fill(username);
        await page.locator('input[name="password"]').fill(password);
        await page.locator('input[name="remember"]').check();
        await page.getByRole('button', { name: /Masuk/i }).click();

        await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 30000 });
        await expect(page.locator('.user-name')).toContainText('Owner E2E');
    });
});
