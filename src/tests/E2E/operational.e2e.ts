import { test } from '@e2e-dev/web';
import { expect } from 'e2e';
import { operationalRoutes } from './support/routes';

test.describe('Operational module pages', { session: 'superadmin', tags: ['operational', 'smoke'] }, () => {
for (const route of operationalRoutes) {
    test(`${route} opens`, async ({ app, browser }) => {
        await app.open(route === '/' ? '/' : `/${route}`);

        await expect(browser).not.toHaveURL(/\/login/);
        await expect(browser.locator('body')).toBeVisible();
    });
}
});

