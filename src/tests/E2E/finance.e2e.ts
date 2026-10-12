import { test } from '@e2e-dev/web';
import { expect } from 'e2e';
import { financeRoutes } from './support/routes';

test.describe('Finance module pages', { session: 'superadmin', tags: ['finance', 'smoke'] }, () => {
for (const route of financeRoutes) {
    test(`${route} opens`, async ({ app, browser }) => {
        await app.open(`/${route}`);

        await expect(browser).not.toHaveURL(/\/login/);
        await expect(browser.locator('body')).toBeVisible();
    });
}
});

