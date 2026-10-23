import { execSync } from 'node:child_process';
import { expect, test as setup } from '@playwright/test';

const statePath = 'playwright/.auth/user.json';

setup('authenticate', async ({ page }) => {
    execSync('php artisan e2e:cleanup', { stdio: 'inherit' });
    await page.goto('/login');
    await page.locator('input[name="email"]').fill('superadmin@klinik.local');
    await page.locator('input[name="password"]').fill('superadmin');
    await page.getByRole('button', { name: /Masuk/i }).click();

    await expect(page).not.toHaveURL(/\/login/, { timeout: 20_000 });
    await page.context().storageState({ path: statePath });
});
