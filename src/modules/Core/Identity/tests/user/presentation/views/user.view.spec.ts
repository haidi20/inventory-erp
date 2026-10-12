import { test, expect, type Page } from '@playwright/test';

test.describe('User View E2E', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'superadmin');
        await page.fill('input[name="password"]', 'superadmin');
        await page.click('button[type="submit"]');
        await page.waitForURL((url) => !url.pathname.includes('/login'));
        await page.goto('/settings/users');
        await expect(page.getByTestId('btn-create-user')).toBeVisible({ timeout: 20000 });
    });

    async function searchUser(page: Page, keyword: string) {
        const searchInput = page.getByTestId('input-search-user');
        await expect(searchInput).toBeVisible();
        await searchInput.fill(keyword);
        await page.waitForTimeout(1000);
    }

    test('harus memuat data pertama kali dengan token yang valid (Fix 401)', async ({ page }) => {
        const apiRequest = page.waitForResponse((resp) =>
            resp.url().includes('/api/v1/settings/users') &&
            resp.request().method() === 'GET',
        );

        await page.goto('/settings/users');

        const response = await apiRequest;
        expect(response.status()).toBe(200);
        await expect(page.locator('table')).toBeVisible();
        await expect(page.locator('body')).not.toContainText('Sesi API berakhir');
    });

    test('jika sesi API berakhir maka user diarahkan ke logout agar bisa login ulang', async ({ page }) => {
        await page.route('**/api/v1/settings/users**', async (route) => {
            await route.fulfill({
                status: 401,
                contentType: 'application/json',
                body: JSON.stringify({ message: 'Unauthorized' }),
            });
        });

        await page.goto('/settings/users');
        await page.waitForURL(/\/login$/, { timeout: 15000 });
    });

    test('harus menampilkan loading indicator saat memfilter data', async ({ page }) => {
        // Intercept API dan tunda response agar loading terlihat
        await page.route('**/api/v1/settings/users*', async (route) => {
            await new Promise(f => setTimeout(f, 1000));
            await route.continue();
        });

        const searchInput = page.getByTestId('input-search-user');
        await searchInput.fill('pw-' + Date.now());
        
        const loading = page.getByTestId('loading-indicator');
        await expect(loading).toBeVisible({ timeout: 5000 });
    });

    test('harus bisa menjalankan siklus CRUD lengkap', async ({ page }) => {
        const timestamp = Date.now();
        const testName = `PW CRUD ${timestamp}`;
        const testEmail = `pw_${timestamp}@mail.com`;

        await page.getByTestId('btn-create-user').click();
        const nameInput = page.getByTestId('input-user-name');
        await expect(nameInput).toBeVisible({ timeout: 10000 });

        await nameInput.fill(testName);
        await page.getByTestId('input-user-email').fill(testEmail);
        await page.getByTestId('input-user-password').fill('password123');
        await page.getByTestId('select-user-role').locator('input[type="checkbox"]').first().check();
        await page.getByTestId('btn-submit-user').click();

        await expect(nameInput).not.toBeVisible({ timeout: 15000 });
        await searchUser(page, testEmail);
        await expect(page.locator('table')).toContainText(testName, { timeout: 15000 });

        let row = page.locator('tr').filter({ hasText: testEmail }).first();
        await row.getByTestId('btn-edit-user').click();
        await expect(nameInput).toBeVisible();
        await nameInput.fill(`${testName} Updated`);
        await page.getByTestId('btn-submit-user').click();

        await expect(nameInput).not.toBeVisible({ timeout: 15000 });
        await searchUser(page, testEmail);
        await expect(page.locator('table')).toContainText(`${testName} Updated`);

        row = page.locator('tr').filter({ hasText: testEmail }).first();
        await row.getByTestId('btn-delete-user').click();
        await expect(page.locator('button:has-text("Ya, Hapus!")')).toBeVisible({ timeout: 10000 });
        await page.locator('button:has-text("Ya, Hapus!")').click();
        await expect(page.locator('table')).not.toContainText(testEmail, { timeout: 15000 });
    });

    test('harus bisa melakukan filter pencarian berdasarkan kolom', async ({ page }) => {
        const columnSelect = page.getByTestId('select-search-column-user');
        await expect(columnSelect).toBeVisible();
        await columnSelect.selectOption('email');

        await searchUser(page, 'superadmin');
        await expect(page.locator('table')).toContainText('superadmin');
    });

    test('harus bisa mengubah per-page dan memilih nomor halaman', async ({ page }) => {
        const perPageSelect = page.getByTestId('select-per-page-user');
        await expect(perPageSelect).toBeVisible();

        await Promise.all([
            page.waitForResponse((resp) => resp.url().includes('/api/v1/settings/users') && resp.url().includes('per_page=10') && resp.status() === 200),
            perPageSelect.selectOption('10'),
        ]);

        const pageTwoLink = page.locator('.pagination-link').filter({ hasText: '2' }).first();
        if (await pageTwoLink.count()) {
            await Promise.all([
                page.waitForURL(/settings\/users\?.*page=2/, { timeout: 15000 }),
                pageTwoLink.click(),
            ]);

            await expect(page.locator('.pagination-link.active')).toContainText('2');
        } else {
            await expect(page.locator('.footer-stats')).toContainText(/Menampilkan/i);
        }
    });

    test('harus bisa sorting data user saat header kolom diklik', async ({ page }) => {
        await Promise.all([
            page.waitForResponse((resp) => resp.url().includes('/api/v1/settings/users') && resp.url().includes('sort_by=email') && resp.url().includes('sort_direction=asc') && resp.status() === 200),
            page.getByTestId('sort-user-email').click(),
        ]);

        await Promise.all([
            page.waitForResponse((resp) => resp.url().includes('/api/v1/settings/users') && resp.url().includes('sort_by=email') && resp.url().includes('sort_direction=desc') && resp.status() === 200),
            page.getByTestId('sort-user-email').click(),
        ]);
    });

    test('harus bisa melakukan ekspor dan unduh template', async ({ page }) => {
        const exportPromise = page.waitForEvent('download');
        await page.getByTestId('btn-export-user').click();
        const exportDownload = await exportPromise;
        expect(exportDownload.suggestedFilename()).toBe('users-export.xlsx');

        await page.getByTestId('btn-import-menu').click();
        const templatePromise = page.waitForEvent('download');
        await page.getByTestId('btn-import-template').click();
        const templateDownload = await templatePromise;
        expect(templateDownload.suggestedFilename()).toBe('user-import-template.xlsx');
    });

    // ── Multi-Role ────────────────────────────────────────────────────────────

    test('search role harus memfilter daftar role yang tampil', async ({ page }) => {
        await page.getByTestId('btn-create-user').click();
        await expect(page.getByTestId('input-user-name')).toBeVisible({ timeout: 10000 });

        const searchInput = page.getByTestId('input-search-role');
        await expect(searchInput).toBeVisible();

        // Ambil jumlah checkbox sebelum search
        const beforeCount = await page.getByTestId('select-user-role').locator('input[type="checkbox"]').count();

        // Ketik keyword yang pasti tidak ada
        await searchInput.fill('zzz_tidak_ada_role');
        await expect(page.locator('.role-empty')).toBeVisible({ timeout: 3000 });
        const afterCount = await page.getByTestId('select-user-role').locator('input[type="checkbox"]').count();
        expect(afterCount).toBeLessThan(beforeCount);

        // Clear → semua role muncul lagi
        await page.getByTestId('input-search-role').clear();
        await expect(page.getByTestId('select-user-role').locator('input[type="checkbox"]').first()).toBeVisible();
    });

    test('form harus menampilkan checkbox role — bukan single select', async ({ page }) => {
        await page.getByTestId('btn-create-user').click();
        await expect(page.getByTestId('input-user-name')).toBeVisible({ timeout: 10000 });

        const roleSection = page.getByTestId('select-user-role');
        await expect(roleSection).toBeVisible();

        // Harus ada input checkbox, BUKAN elemen <select>
        const checkboxes = roleSection.locator('input[type="checkbox"]');
        await expect(checkboxes.first()).toBeVisible({ timeout: 5000 });

        const selectEl = roleSection.locator('select');
        await expect(selectEl).toHaveCount(0);
    });

    test('harus bisa memilih lebih dari satu role sekaligus', async ({ page }) => {
        const timestamp = Date.now();
        const testName = `PW MultiRole ${timestamp}`;
        const testEmail = `pw_mr_${timestamp}@mail.com`;

        await page.getByTestId('btn-create-user').click();
        await expect(page.getByTestId('input-user-name')).toBeVisible({ timeout: 10000 });

        await page.getByTestId('input-user-name').fill(testName);
        await page.getByTestId('input-user-email').fill(testEmail);
        await page.getByTestId('input-user-password').fill('password123');

        const roleSection = page.getByTestId('select-user-role');
        const checkboxes = roleSection.locator('input[type="checkbox"]');
        const checkboxCount = await checkboxes.count();

        // Centang semua checkbox yang tersedia (minimal 1, maksimal 2 pertama)
        await checkboxes.nth(0).check();
        await expect(checkboxes.nth(0)).toBeChecked();

        if (checkboxCount >= 2) {
            await checkboxes.nth(1).check();
            await expect(checkboxes.nth(1)).toBeChecked();
        }

        await page.getByTestId('btn-submit-user').click();
        await expect(page.getByTestId('input-user-name')).not.toBeVisible({ timeout: 15000 });

        // Cleanup: hapus user test
        await searchUser(page, testEmail);
        const row = page.locator('tr').filter({ hasText: testEmail }).first();
        await row.getByTestId('btn-delete-user').click();
        await page.locator('button:has-text("Ya, Hapus!")').click();
        await expect(page.locator('table')).not.toContainText(testEmail, { timeout: 15000 });
    });

    test('saat edit user — role yang sudah dimiliki harus sudah tercentang', async ({ page }) => {
        const timestamp = Date.now();
        const testName = `PW EditRole ${timestamp}`;
        const testEmail = `pw_er_${timestamp}@mail.com`;

        // Create user dulu dengan 1 role
        await page.getByTestId('btn-create-user').click();
        await expect(page.getByTestId('input-user-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-user-name').fill(testName);
        await page.getByTestId('input-user-email').fill(testEmail);
        await page.getByTestId('input-user-password').fill('password123');
        const roleSection = page.getByTestId('select-user-role');
        await roleSection.locator('input[type="checkbox"]').first().check();
        await page.getByTestId('btn-submit-user').click();
        await expect(page.getByTestId('input-user-name')).not.toBeVisible({ timeout: 15000 });

        // Buka edit — role lama harus sudah tercentang
        await searchUser(page, testEmail);
        const row = page.locator('tr').filter({ hasText: testEmail }).first();
        await row.getByTestId('btn-edit-user').click();
        await expect(page.getByTestId('input-user-name')).toBeVisible({ timeout: 10000 });

        const checkboxes = page.getByTestId('select-user-role').locator('input[type="checkbox"]');
        const checkedCount = await checkboxes.evaluateAll(
            (els: HTMLInputElement[]) => els.filter(el => el.checked).length
        );
        expect(checkedCount).toBeGreaterThan(0);

        await page.getByTestId('btn-cancel-user').click();

        // Cleanup
        await searchUser(page, testEmail);
        const row2 = page.locator('tr').filter({ hasText: testEmail }).first();
        await row2.getByTestId('btn-delete-user').click();
        await page.locator('button:has-text("Ya, Hapus!")').click();
    });

    // ── UIUX: Form Scrolling & Role Visibility ───────────────────────────────
    test('modal form tidak boleh melebihi viewport — harus bounded 90vh', async ({ page }) => {
        await page.getByTestId('btn-create-user').click();
        await expect(page.getByTestId('input-user-name')).toBeVisible({ timeout: 10000 });

        const modalCard = page.locator('.modal-card').first();
        const box = await modalCard.boundingBox();

        expect(box).not.toBeNull();
        const viewportHeight = page.viewportSize()!.height;
        // Modal harus tidak melebihi 90% tinggi viewport
        expect(box!.height).toBeLessThanOrEqual(viewportHeight * 0.92);
    });

    test('modal-body harus scrollable (overflow-y: auto)', async ({ page }) => {
        await page.getByTestId('btn-create-user').click();
        await expect(page.getByTestId('input-user-name')).toBeVisible({ timeout: 10000 });

        const overflowY = await page.locator('.modal-body').first().evaluate(
            (el) => window.getComputedStyle(el).overflowY
        );

        expect(['auto', 'scroll']).toContain(overflowY);
    });

    test('field role select harus terlihat dan dapat diklik setelah form dibuka', async ({ page }) => {
        await page.getByTestId('btn-create-user').click();
        await expect(page.getByTestId('input-user-name')).toBeVisible({ timeout: 10000 });

        const roleList = page.getByTestId('select-user-role');

        // Scroll ke dalam modal-body jika perlu
        await roleList.scrollIntoViewIfNeeded();

        // Harus visible
        await expect(roleList).toBeVisible();

        // Harus ada minimal 1 role checkbox
        const checkboxes = roleList.locator('input[type="checkbox"]');
        const checkboxCount = await checkboxes.count();
        expect(checkboxCount).toBeGreaterThan(0);

        // Harus bisa dipilih
        await checkboxes.first().check();
        await expect(checkboxes.first()).toBeChecked();
    });

    test('form container (form element) harus bersifat flex agar modal-body bisa scroll', async ({ page }) => {
        await page.getByTestId('btn-create-user').click();
        await expect(page.getByTestId('input-user-name')).toBeVisible({ timeout: 10000 });

        const formStyles = await page.locator('.modal-card > form').first().evaluate((el) => {
            const s = window.getComputedStyle(el);
            return {
                display: s.display,
                flexDirection: s.flexDirection,
                overflow: s.overflow,
            };
        });

        expect(formStyles.display).toBe('flex');
        expect(formStyles.flexDirection).toBe('column');
    });

    test('semua field form harus bisa diisi tanpa menutup modal — tidak ada clipping', async ({ page }) => {
        await page.getByTestId('btn-create-user').click();
        await expect(page.getByTestId('input-user-name')).toBeVisible({ timeout: 10000 });

        // Isi semua field dari atas ke bawah
        await page.getByTestId('input-user-name').fill('Test Scroll User');
        await page.getByTestId('input-user-email').fill('testscroll@klinik.local');
        await page.getByTestId('input-user-password').fill('password123');

        // Role checkboxes harus masih visible setelah mengisi field di atasnya
        const roleList = page.getByTestId('select-user-role');
        await roleList.scrollIntoViewIfNeeded();
        await expect(roleList).toBeVisible();
        await roleList.locator('input[type="checkbox"]').first().check();

        // Tombol simpan harus visible tanpa menutup modal
        const submitBtn = page.getByTestId('btn-submit-user');
        await submitBtn.scrollIntoViewIfNeeded();
        await expect(submitBtn).toBeVisible();
        await expect(submitBtn).toBeEnabled();
    });
});
