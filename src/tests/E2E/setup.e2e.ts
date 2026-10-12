import { test } from '@e2e-dev/web';
import { credentials, expect } from 'e2e';

test.setup(
    'authenticate as superadmin',
    { sessions: ['superadmin'] },
    async ({ app, browser, session }) => {
        const user = credentials.user('superadmin');

        await app.open('/login');
        await browser.locator('input[name=email]').fill(user.username);
        await browser.locator('input[name=password]').fill(user.password);
        await browser.locator('button[type=submit]').click();

        await expect(browser).not.toHaveURL(/\/login/);
        await session.save('superadmin');
    },
);

