import { test } from '@e2e-dev/web';
import { expect } from 'e2e';
import { coreRoutes } from './support/routes';

test.describe('Core module pages', { session: 'superadmin', tags: ['core', 'smoke'] }, () => {
for (const route of coreRoutes) {
    test(`${route} opens`, async ({ app, browser }) => {
        await app.open(`/${route}`);

        await expect(browser).not.toHaveURL(/\/login/);
        await expect(browser.locator('body')).toBeVisible();
    });
}
});

