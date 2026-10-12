import { test } from '@e2e-dev/web';
import { expect } from 'e2e';
import { clinicalRoutes } from './support/routes';

test.describe('Clinical module pages', { session: 'superadmin', tags: ['clinical', 'smoke'] }, () => {
for (const route of clinicalRoutes) {
    test(`${route} opens`, async ({ app, browser }) => {
        await app.open(`/${route}`);

        await expect(browser).not.toHaveURL(/\/login/);
        await expect(browser.locator('body')).toBeVisible();
    });
}
});

