import { test, expect } from '@playwright/test';

test.describe.serial('Change Password E2E', () => {
    const unique = Date.now();
    const company = `Klinik Password E2E ${unique}`;
    const email = `password.${unique}@e2e.test`;
    const username = `password_e2e_${unique}`;
    const oldPassword = 'password123';
    const newPassword = 'newPassword123';

    test('mempertahankan login dengan remember me lalu mengubah password', async ({ page }) => {
        await page.goto('/register');
        await page.getByTestId('input-company-name').fill(company);
        await page.getByTestId('select-timezone').selectOption('Asia/Makassar');
        await page.getByTestId('input-name').fill('Owner Password E2E');
        await page.getByTestId('input-email').fill(email);
        await page.getByTestId('input-username').fill(username);
        await page.getByTestId('input-password').fill(oldPassword);
        await page.getByTestId('input-password-confirmation').fill(oldPassword);
        await page.getByTestId('btn-register').click();

        await page.waitForURL((url) => !url.pathname.includes('/register') && !url.pathname.includes('/login'), {
            timeout: 30000,
        });

        await page.context().clearCookies();
        await page.goto('/login');
        await page.locator('input[name="email"]').fill(username);
        await page.locator('input[name="password"]').fill(oldPassword);
        await page.locator('input[name="remember"]').check();
        await page.getByRole('button', { name: /Masuk/i }).click();

        await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 30000 });
        await expect(page.locator('.user-name')).toContainText('Owner Password E2E');

        const cookies = await page.context().cookies();
        const recallerCookie = cookies.find((cookie) => cookie.name.startsWith('remember_web_'));
        expect(recallerCookie).toBeTruthy();

        await page.context().clearCookies();
        await page.context().addCookies([recallerCookie!]);
        await page.goto('/');
        await expect(page.locator('.user-name')).toContainText('Owner Password E2E');

        await page.locator('#userDropdownToggle').click();
        await page.locator('#changePasswordTrigger').click();
        await page.locator('input[name="password"]').fill(newPassword);
        await page.locator('input[name="password_confirmation"]').fill(newPassword);
        await page.locator('#changePasswordForm button[type="submit"]').click();

        await page.waitForLoadState('domcontentloaded');
        await page.locator('#userDropdownToggle').click();
        await page.locator('#changePasswordTrigger').click();
        await expect(page.locator('.cp-alert-success')).toContainText('Password berhasil diubah.');

        await page.locator('#changePasswordCancel').click();
        await page.locator('#userDropdownToggle').click();
        await page.locator('.user-dropdown-menu form button[type="submit"]').click();
        await page.waitForURL(/\/login/, { timeout: 30000 });

        await page.locator('input[name="email"]').fill(username);
        await page.locator('input[name="password"]').fill(oldPassword);
        await page.getByRole('button', { name: /Masuk/i }).click();
        await expect(page.locator('.alert-error')).toBeVisible({ timeout: 15000 });

        await page.locator('input[name="email"]').fill(username);
        await page.locator('input[name="password"]').fill(newPassword);
        await page.getByRole('button', { name: /Masuk/i }).click();
        await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 30000 });
        await expect(page.locator('.user-name')).toContainText('Owner Password E2E');
    });
});
