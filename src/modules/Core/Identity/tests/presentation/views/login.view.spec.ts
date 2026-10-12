import { test, expect, chromium, devices, type Page } from '@playwright/test';
import { mkdtemp, rm } from 'node:fs/promises';
import { join } from 'node:path';
import { tmpdir } from 'node:os';

test.describe('Login E2E', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
    });

    test('menampilkan form login Vue', async ({ page }) => {
        await expect(page.locator('input[name="email"]')).toBeVisible();
        await expect(page.locator('input[name="password"]')).toBeVisible();
        await expect(page.locator('input[name="remember"]')).toBeVisible();
        await expect(page.getByRole('button', { name: /Masuk/i })).toBeVisible();
        await expect(page.getByRole('link', { name: /Daftar di sini/i })).toBeVisible();
    });

    test('menampilkan dan menyembunyikan password dengan tombol mata', async ({ page }) => {
        const password = page.locator('input[name="password"]');

        await password.fill('superadmin');
        await expect(password).toHaveAttribute('type', 'password');

        await page.locator('.password-toggle').click();
        await expect(password).toHaveAttribute('type', 'text');

        await page.locator('.password-toggle').click();
        await expect(password).toHaveAttribute('type', 'password');
    });

    test('checkbox remember me dapat dipilih', async ({ page }) => {
        const remember = page.locator('input[name="remember"]');

        await remember.check();
        await expect(remember).toBeChecked();

        await remember.uncheck();
        await expect(remember).not.toBeChecked();
    });

    test('navigasi ke halaman register', async ({ page }) => {
        await page.getByRole('link', { name: /Daftar di sini/i }).click();
        await expect(page).toHaveURL(/\/register$/);
        await expect(page.getByTestId('input-company-name')).toBeVisible();
    });

    test('menampilkan error saat kredensial salah', async ({ page }) => {
        await page.locator('input[name="email"]').fill('tidak-ada@e2e.test');
        await page.locator('input[name="password"]').fill('wrong-password');
        await page.getByRole('button', { name: /Masuk/i }).click();

        await expect(page.locator('.alert-error')).toBeVisible({ timeout: 15000 });
        await expect(page).toHaveURL(/\/login/);
    });

    // ── Remember me ──────────────────────────────────────────────────────────

    const REMEMBER_COOKIE_PREFIX = 'remember_web_';

    async function loginWithRemember(page: Page) {
        await page.locator('input[name="email"]').fill('superadmin@klinik.local');
        await page.locator('input[name="password"]').fill('superadmin');
        await page.locator('input[name="remember"]').check();
        await page.getByRole('button', { name: /Masuk/i }).click();
        await expect(page).toHaveURL((url) => !url.pathname.includes('/login'), { timeout: 20_000 });
    }

    async function loginWithoutRemember(page: Page) {
        await page.locator('input[name="email"]').fill('superadmin@klinik.local');
        await page.locator('input[name="password"]').fill('superadmin');
        await page.getByRole('button', { name: /Masuk/i }).click();
        await expect(page).toHaveURL((url) => !url.pathname.includes('/login'), { timeout: 20_000 });
    }

    test('login dengan remember menyimpan cookie recaller', async ({ page }) => {
        await loginWithRemember(page);

        const cookies = await page.context().cookies();
        expect(cookies.some((c) => c.name.startsWith(REMEMBER_COOKIE_PREFIX))).toBeTruthy();
    });

    test('login tanpa remember tidak menyimpan cookie recaller', async ({ page }) => {
        await loginWithoutRemember(page);

        const cookies = await page.context().cookies();
        expect(cookies.some((c) => c.name.startsWith(REMEMBER_COOKIE_PREFIX))).toBeFalsy();
    });

    test('sesi baru hanya dengan cookie recaller tetap login', async ({ page, browser }) => {
        await loginWithRemember(page);

        const rememberCookie = (await page.context().cookies())
            .find((c) => c.name.startsWith(REMEMBER_COOKIE_PREFIX));
        expect(rememberCookie).toBeDefined();

        // Simulasi browser ditutup: context baru = semua session cookie hilang,
        // hanya cookie recaller yang dipindahkan.
        const ctx2 = await browser.newContext();
        await ctx2.addCookies([rememberCookie!]);
        const page2 = await ctx2.newPage();
        await page2.goto('/');

        await expect(page2).not.toHaveURL(/\/login/);
        await expect(page2.locator('input[name="email"]')).toHaveCount(0);
        await ctx2.close();
    });

    test('logout menghapus cookie recaller', async ({ page }) => {
        await loginWithRemember(page);

        await page.locator('#userDropdownToggle').click();
        await page.getByRole('button', { name: /Keluar/i }).click();

        await expect(page).toHaveURL(/\/login/, { timeout: 20_000 });
        const cookies = await page.context().cookies();
        expect(cookies.some((c) => c.name.startsWith(REMEMBER_COOKIE_PREFIX))).toBeFalsy();
    });

    test('sesi login remember bertahan setelah browser di-restart', async ({}) => {
        const profileDir = await mkdtemp(join(tmpdir(), 'remember-me-profile-'));
        const context = await chromium.launchPersistentContext(profileDir, {
            ...devices['Desktop Chrome'],
            baseURL: process.env.KLINIK_TEST_BASE_URL ?? 'http://127.0.0.1:8000',
        });

        try {
            const page = await context.newPage();
            await page.goto('/login');
            await loginWithRemember(page);

            const cookiesBeforeRestart = await context.cookies();
            const rememberCookieBefore = cookiesBeforeRestart.find((cookie) =>
                cookie.name.startsWith(REMEMBER_COOKIE_PREFIX),
            );
            expect(rememberCookieBefore).toBeDefined();
            expect(rememberCookieBefore?.httpOnly).toBe(true);
            expect(rememberCookieBefore?.sameSite).toBe('Lax');

            await context.close();
            const restartedContext = await chromium.launchPersistentContext(profileDir, {
                ...devices['Desktop Chrome'],
                baseURL: process.env.KLINIK_TEST_BASE_URL ?? 'http://127.0.0.1:8000',
            });

            try {
                const pageAfterRestart = await restartedContext.newPage();
                await pageAfterRestart.goto('/');

                await expect(pageAfterRestart).not.toHaveURL(/\/login/, { timeout: 20_000 });
                await expect(pageAfterRestart.locator('input[name=email]')).toHaveCount(0);

                const cookiesAfterRestart = await restartedContext.cookies();
                const rememberCookieAfter = cookiesAfterRestart.find((cookie) =>
                    cookie.name.startsWith(REMEMBER_COOKIE_PREFIX),
                );
                expect(rememberCookieAfter?.name).toBe(rememberCookieBefore?.name);
                expect(rememberCookieAfter?.value).toBe(rememberCookieBefore?.value);

            } finally {
                await restartedContext.close();
            }
        } finally {
            await rm(profileDir, { recursive: true, force: true, maxRetries: 5, retryDelay: 100 });
        }
    });
});
