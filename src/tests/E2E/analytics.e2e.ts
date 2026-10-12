import { test } from '@e2e-dev/web';
import { expect } from 'e2e';
import { analyticsRoutes } from './support/routes';

test.describe('Analytics module pages', { session: 'superadmin', tags: ['analytics', 'smoke'] }, () => {
for (const route of analyticsRoutes) {
    test(`${route} opens`, async ({ app, browser }) => {
        await app.open(`/${route}`);

        await expect(browser).not.toHaveURL(/\/login/);
        await expect(browser.locator('body')).toBeVisible();
    });
}
});

