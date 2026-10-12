import { test, expect, type Page } from '@playwright/test';

test.describe('Farmacy View E2E', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'superadmin');
        await page.fill('input[name="password"]', 'superadmin');
        await page.click('button[type="submit"]');
        await page.waitForURL((url) => !url.pathname.includes('/login'));
        await page.goto('/settings/Farmacys');
        await expect(page.getByTestId('btn-create-Farmacy')).toBeVisible({ timeout: 20000 });
    });

    async function searchFarmacy(page: Page, keyword: string) {
        const searchInput = page.getByTestId('input-search-Farmacy');
        await expect(searchInput).toBeVisible();
        await searchInput.fill(keyword);
        await page.waitForTimeout(1000);
    }

    test('harus memuat data pertama kali dengan token yang valid (Fix 401)', async ({ page }) => {
        const apiRequest = page.waitForResponse((resp) =>
            resp.url().includes('/api/v1/settings/Farmacys') &&
            resp.request().method() === 'GET',
        );

        await page.goto('/settings/Farmacys');

        const response = await apiRequest;
        expect(response.status()).toBe(200);
        await expect(page.locator('table')).toBeVisible();
        await expect(page.locator('body')).not.toContainText('Sesi API berakhir');
    });

    test('jika sesi API berakhir maka Farmacy diarahkan ke logout agar bisa login ulang', async ({ page }) => {
        await page.route('**/api/v1/settings/Farmacys**', async (route) => {
            await route.fulfill({
                status: 401,
                contentType: 'application/json',
                body: JSON.stringify({ message: 'Unauthorized' }),
            });
        });

        await page.goto('/settings/Farmacys');
        await page.waitForURL(/\/login$/, { timeout: 15000 });
    });

    test('harus menampilkan loading indicator saat memfilter data', async ({ page }) => {
        // Intercept API dan tunda response agar loading terlihat
        await page.route('**/api/v1/settings/Farmacys*', async (route) => {
            await new Promise(f => setTimeout(f, 1000));
            await route.continue();
        });

        const searchInput = page.getByTestId('input-search-Farmacy');
        await searchInput.fill('pw-' + Date.now());
        
        const loading = page.getByTestId('loading-indicator');
        await expect(loading).toBeVisible({ timeout: 5000 });
    });

    test('harus bisa menjalankan siklus CRUD lengkap', async ({ page }) => {
        const timestamp = Date.now();
        const testName = `PW CRUD ${timestamp}`;
        const testEmail = `pw_${timestamp}@mail.com`;

        await page.getByTestId('btn-create-Farmacy').click();
        const nameInput = page.getByTestId('input-Farmacy-name');
        await expect(nameInput).toBeVisible({ timeout: 10000 });

        await nameInput.fill(testName);
        await page.getByTestId('input-Farmacy-email').fill(testEmail);
        await page.getByTestId('input-Farmacy-password').fill('password123');
        await page.getByTestId('select-Farmacy-role').locator('input[type="checkbox"]').first().check();
        await page.getByTestId('btn-submit-Farmacy').click();

        await expect(nameInput).not.toBeVisible({ timeout: 15000 });
        await searchFarmacy(page, testEmail);
        await expect(page.locator('table')).toContainText(testName, { timeout: 15000 });

        let row = page.locator('tr').filter({ hasText: testEmail }).first();
        await row.getByTestId('btn-edit-Farmacy').click();
        await expect(nameInput).toBeVisible();
        await nameInput.fill(`${testName} Updated`);
        await page.getByTestId('btn-submit-Farmacy').click();

        await expect(nameInput).not.toBeVisible({ timeout: 15000 });
        await searchFarmacy(page, testEmail);
        await expect(page.locator('table')).toContainText(`${testName} Updated`);

        row = page.locator('tr').filter({ hasText: testEmail }).first();
        await row.getByTestId('btn-delete-Farmacy').click();
        await expect(page.locator('button:has-text("Ya, Hapus!")')).toBeVisible({ timeout: 10000 });
        await page.locator('button:has-text("Ya, Hapus!")').click();
        await expect(page.locator('table')).not.toContainText(testEmail, { timeout: 15000 });
    });

    test('harus bisa melakukan filter pencarian berdasarkan kolom', async ({ page }) => {
        const columnSelect = page.getByTestId('select-search-column-Farmacy');
        await expect(columnSelect).toBeVisible();
        await columnSelect.selectOption('email');

        await searchFarmacy(page, 'superadmin');
        await expect(page.locator('table')).toContainText('superadmin');
    });

    test('harus bisa mengubah per-page dan memilih nomor halaman', async ({ page }) => {
        const perPageSelect = page.getByTestId('select-per-page-Farmacy');
        await expect(perPageSelect).toBeVisible();

        await Promise.all([
            page.waitForResponse((resp) => resp.url().includes('/api/v1/settings/Farmacys') && resp.url().includes('per_page=10') && resp.status() === 200),
            perPageSelect.selectOption('10'),
        ]);

        const pageTwoLink = page.locator('.pagination-link').filter({ hasText: '2' }).first();
        if (await pageTwoLink.count()) {
            await Promise.all([
                page.waitForURL(/settings\/Farmacys\?.*page=2/, { timeout: 15000 }),
                pageTwoLink.click(),
            ]);

            await expect(page.locator('.pagination-link.active')).toContainText('2');
        } else {
            await expect(page.locator('.footer-stats')).toContainText(/Menampilkan/i);
        }
    });

    test('harus bisa sorting data Farmacy saat header kolom diklik', async ({ page }) => {
        await Promise.all([
            page.waitForResponse((resp) => resp.url().includes('/api/v1/settings/Farmacys') && resp.url().includes('sort_by=email') && resp.url().includes('sort_direction=asc') && resp.status() === 200),
            page.getByTestId('sort-Farmacy-email').click(),
        ]);

        await Promise.all([
            page.waitForResponse((resp) => resp.url().includes('/api/v1/settings/Farmacys') && resp.url().includes('sort_by=email') && resp.url().includes('sort_direction=desc') && resp.status() === 200),
            page.getByTestId('sort-Farmacy-email').click(),
        ]);
    });

    test('harus bisa melakukan ekspor dan unduh template', async ({ page }) => {
        const exportPromise = page.waitForEvent('download');
        await page.getByTestId('btn-export-Farmacy').click();
        const exportDownload = await exportPromise;
        expect(exportDownload.suggestedFilename()).toBe('Farmacys-export.xlsx');

        await page.getByTestId('btn-import-menu').click();
        const templatePromise = page.waitForEvent('download');
        await page.getByTestId('btn-import-template').click();
        const templateDownload = await templatePromise;
        expect(templateDownload.suggestedFilename()).toBe('Farmacy-import-template.xlsx');
    });

    // ── Multi-Role ────────────────────────────────────────────────────────────

    test('search role harus memfilter daftar role yang tampil', async ({ page }) => {
        await page.getByTestId('btn-create-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).toBeVisible({ timeout: 10000 });

        const searchInput = page.getByTestId('input-search-role');
        await expect(searchInput).toBeVisible();

        // Ambil jumlah checkbox sebelum search
        const beforeCount = await page.getByTestId('select-Farmacy-role').locator('input[type="checkbox"]').count();

        // Ketik keyword yang pasti tidak ada
        await searchInput.fill('zzz_tidak_ada_role');
        await expect(page.locator('.role-empty')).toBeVisible({ timeout: 3000 });
        const afterCount = await page.getByTestId('select-Farmacy-role').locator('input[type="checkbox"]').count();
        expect(afterCount).toBeLessThan(beforeCount);

        // Clear → semua role muncul lagi
        await page.getByTestId('input-search-role').clear();
        await expect(page.getByTestId('select-Farmacy-role').locator('input[type="checkbox"]').first()).toBeVisible();
    });

    test('form harus menampilkan checkbox role — bukan single select', async ({ page }) => {
        await page.getByTestId('btn-create-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).toBeVisible({ timeout: 10000 });

        const roleSection = page.getByTestId('select-Farmacy-role');
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

        await page.getByTestId('btn-create-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).toBeVisible({ timeout: 10000 });

        await page.getByTestId('input-Farmacy-name').fill(testName);
        await page.getByTestId('input-Farmacy-email').fill(testEmail);
        await page.getByTestId('input-Farmacy-password').fill('password123');

        const roleSection = page.getByTestId('select-Farmacy-role');
        const checkboxes = roleSection.locator('input[type="checkbox"]');
        const checkboxCount = await checkboxes.count();

        // Centang semua checkbox yang tersedia (minimal 1, maksimal 2 pertama)
        await checkboxes.nth(0).check();
        await expect(checkboxes.nth(0)).toBeChecked();

        if (checkboxCount >= 2) {
            await checkboxes.nth(1).check();
            await expect(checkboxes.nth(1)).toBeChecked();
        }

        await page.getByTestId('btn-submit-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).not.toBeVisible({ timeout: 15000 });

        // Cleanup: hapus Farmacy test
        await searchFarmacy(page, testEmail);
        const row = page.locator('tr').filter({ hasText: testEmail }).first();
        await row.getByTestId('btn-delete-Farmacy').click();
        await page.locator('button:has-text("Ya, Hapus!")').click();
        await expect(page.locator('table')).not.toContainText(testEmail, { timeout: 15000 });
    });

    test('saat edit Farmacy — role yang sudah dimiliki harus sudah tercentang', async ({ page }) => {
        const timestamp = Date.now();
        const testName = `PW EditRole ${timestamp}`;
        const testEmail = `pw_er_${timestamp}@mail.com`;

        // Create Farmacy dulu dengan 1 role
        await page.getByTestId('btn-create-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).toBeVisible({ timeout: 10000 });
        await page.getByTestId('input-Farmacy-name').fill(testName);
        await page.getByTestId('input-Farmacy-email').fill(testEmail);
        await page.getByTestId('input-Farmacy-password').fill('password123');
        const roleSection = page.getByTestId('select-Farmacy-role');
        await roleSection.locator('input[type="checkbox"]').first().check();
        await page.getByTestId('btn-submit-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).not.toBeVisible({ timeout: 15000 });

        // Buka edit — role lama harus sudah tercentang
        await searchFarmacy(page, testEmail);
        const row = page.locator('tr').filter({ hasText: testEmail }).first();
        await row.getByTestId('btn-edit-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).toBeVisible({ timeout: 10000 });

        const checkboxes = page.getByTestId('select-Farmacy-role').locator('input[type="checkbox"]');
        const checkedCount = await checkboxes.evaluateAll(
            (els: HTMLInputElement[]) => els.filter(el => el.checked).length
        );
        expect(checkedCount).toBeGreaterThan(0);

        await page.getByTestId('btn-cancel-Farmacy').click();

        // Cleanup
        await searchFarmacy(page, testEmail);
        const row2 = page.locator('tr').filter({ hasText: testEmail }).first();
        await row2.getByTestId('btn-delete-Farmacy').click();
        await page.locator('button:has-text("Ya, Hapus!")').click();
    });

    // ── UIUX: Form Scrolling & Role Visibility ───────────────────────────────
    test('modal form tidak boleh melebihi viewport — harus bounded 90vh', async ({ page }) => {
        await page.getByTestId('btn-create-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).toBeVisible({ timeout: 10000 });

        const modalCard = page.locator('.modal-card').first();
        const box = await modalCard.boundingBox();

        expect(box).not.toBeNull();
        const viewportHeight = page.viewportSize()!.height;
        // Modal harus tidak melebihi 90% tinggi viewport
        expect(box!.height).toBeLessThanOrEqual(viewportHeight * 0.92);
    });

    test('modal-body harus scrollable (overflow-y: auto)', async ({ page }) => {
        await page.getByTestId('btn-create-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).toBeVisible({ timeout: 10000 });

        const overflowY = await page.locator('.modal-body').first().evaluate(
            (el) => window.getComputedStyle(el).overflowY
        );

        expect(['auto', 'scroll']).toContain(overflowY);
    });

    test('field role select harus terlihat dan dapat diklik setelah form dibuka', async ({ page }) => {
        await page.getByTestId('btn-create-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).toBeVisible({ timeout: 10000 });

        const roleList = page.getByTestId('select-Farmacy-role');

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
        await page.getByTestId('btn-create-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).toBeVisible({ timeout: 10000 });

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
        await page.getByTestId('btn-create-Farmacy').click();
        await expect(page.getByTestId('input-Farmacy-name')).toBeVisible({ timeout: 10000 });

        // Isi semua field dari atas ke bawah
        await page.getByTestId('input-Farmacy-name').fill('Test Scroll Farmacy');
        await page.getByTestId('input-Farmacy-email').fill('testscroll@klinik.local');
        await page.getByTestId('input-Farmacy-password').fill('password123');

        // Role checkboxes harus masih visible setelah mengisi field di atasnya
        const roleList = page.getByTestId('select-Farmacy-role');
        await roleList.scrollIntoViewIfNeeded();
        await expect(roleList).toBeVisible();
        await roleList.locator('input[type="checkbox"]').first().check();

        // Tombol simpan harus visible tanpa menutup modal
        const submitBtn = page.getByTestId('btn-submit-Farmacy');
        await submitBtn.scrollIntoViewIfNeeded();
        await expect(submitBtn).toBeVisible();
        await expect(submitBtn).toBeEnabled();
    });
});


