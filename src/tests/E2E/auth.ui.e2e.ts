import { test } from '@e2e-dev/web';
import { expect } from 'e2e';

test.describe('Login experience', { tags: ['core', 'ui'] }, () => {
    test('renders an empty centered login form', async ({ app, browser }) => {
        await app.open('/login');
        await expect(browser.locator('.login-box')).toBeVisible();
    });

    test('rejects invalid login without exposing raw validation arrays', async ({ app, browser }) => {
        await app.open('/login');
        await browser.locator('input[name=email]').fill('invalid@klinik.local');
        await browser.locator('input[name=password]').fill('wrong-password');
        await browser.locator('button[type=submit]').click();

        await expect(browser).not.toHaveURL(/dashboard/);
        await expect(browser.locator('.alert-error')).toBeVisible();
        await expect(browser.locator('.alert-error')).not.toContainText('["');
    });

    test('keeps protected pages behind authentication', async ({ app, browser }) => {
        await app.open('/settings/users');
        await expect(browser).toHaveURL(/login/);
    });
});
