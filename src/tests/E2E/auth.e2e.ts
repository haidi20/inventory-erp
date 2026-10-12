import { test } from '@e2e-dev/web';
import { credentials, expect } from 'e2e';

test.describe('Authentication', () => {
    test('login page renders', async ({ app, browser }) => {
        await app.open('/login');

        await expect(browser.locator('form[action=/login]')).toBeVisible();
        await expect(browser.locator('input[name=email]')).toBeVisible();
        await expect(browser.locator('input[name=password]')).toBeVisible();
    });

    test('login rejects invalid credentials', async ({ app, browser }) => {
        await app.open('/login');
        await browser.locator('input[name=email]').fill('invalid-user');
        await browser.locator('input[name=password]').fill('invalid-password');
        await browser.locator('button[type=submit]').click();

        await expect(browser.locator('.alert-error').first()).toBeVisible();
    });

    test('superadmin session reaches dashboard', { session: 'superadmin' }, async ({ app, browser }) => {
        await app.open('/');

        await expect(browser).not.toHaveURL(/\/login/);
        await expect(browser.locator('body')).toBeVisible();
    });

    test('forgot password page renders', async ({ app, browser }) => {
        await app.open('/forgot-password');

        await expect(browser.locator('input[name=email]')).toBeVisible();
    });

    test('register page renders', async ({ app, browser }) => {
        await app.open('/register');

        await expect(browser.locator('input[name=email]')).toBeVisible();
        await expect(browser.locator('input[name=username]')).toBeVisible();
    });
});

