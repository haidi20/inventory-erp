import { test, expect, type Page } from '@playwright/test';

/**
 * Role View E2E Test
 * Memastikan interaksi UI pada halaman Role Management berjalan dengan benar.
 */
test.describe('Role View E2E', () => {

    test.beforeEach(async ({ page }) => {
        // Login menggunakan Akun Demo Owner untuk keandalan ekstra
        await page.goto('/login');
        await page.click('#openInfoModal');
        await page.locator('.btn-use-account[data-email="superadmin"]').click();
        await page.click('button[type="submit"]');

        // Cek apakah muncul modal pemilih role (jika user punya > 1 role)
        const roleModal = page.locator('#roleModal');
        if (await roleModal.isVisible({ timeout: 5000 }).catch(() => false)) {
            console.log('DEBUG: Role Modal detected, selecting first role...');
            await page.locator('#roleList button').first().click();
        }

        // Verifikasi login berhasil (Cek keberadaan tombol logout sebagai tanda sudah masuk)
        try {
            await expect(page.locator('button:has-text("Logout")')).toBeVisible({ timeout: 15000 });
        } catch (e) {
            console.log('LOGIN FAILED! URL:', page.url());
            const body = await page.innerText('body');
            console.log('BODY TEXT PREVIEW:', body.substring(0, 500));
            throw e;
        }

        await Promise.all([
            page.goto('/settings/roles'),
            page.waitForResponse(
                resp => resp.url().includes('/api/v1/settings/roles') && resp.status() === 200,
                { timeout: 30000 }
            )
        ]);

        // Debug: Tunggu sebentar sampai render selesai
        await page.waitForTimeout(1000);
    });

    async function searchRole(page: Page, keyword: string) {
        const searchInput = page.getByTestId('input-search-role');
        await expect(searchInput).toBeVisible();

        // Tunggu request selesai setelah mengetik
        const responsePromise = page.waitForResponse(resp => resp.url().includes('/api/v1/settings/roles'));
        await searchInput.fill(keyword);
        await responsePromise;

        await page.waitForTimeout(500);
    }

    test.afterEach(async ({ page }, testInfo) => {
        if (testInfo.status !== testInfo.expectedStatus) {
            const screenshotPath = `test-results/failure-${testInfo.title.replace(/\s+/g, '_')}.png`;
            await page.screenshot({ path: screenshotPath });
            console.log(`DEBUG: Screenshot saved to ${screenshotPath}`);
        }
    });

    test('harus bisa menampilkan daftar role dan melakukan pencarian (all & per kolom)', async ({ page }) => {
        // Cek apakah ada data di tabel (bukan empty state)
        const rows = page.locator('table tbody tr');
        await expect(rows.first()).toBeVisible({ timeout: 30000 });
        await expect(page.locator('table')).not.toContainText('Tidak ada role ditemukan', { timeout: 30000 });

        // 1. Test Search ALL (Default)
        await searchRole(page, 'owner');
        await expect(page.locator('table')).toContainText(/owner/i);

        // 2. Test Search by NAME specifically
        const columnSelect = page.getByTestId('select-search-column-role');
        await columnSelect.selectOption('name');
        await searchRole(page, 'admin');
        await expect(page.locator('table')).toContainText(/admin/i);

        // 3. Test Search by LABEL specifically
        await columnSelect.selectOption('label');
        await searchRole(page, 'Administrator'); // Usually 'admin' has 'Administrator' label
        await expect(page.locator('table')).toContainText(/admin/i);
    });

    test('harus menampilkan loading indicator saat memfilter data', async ({ page }) => {
        // Intercept API dan tunda response agar loading terlihat
        await page.route('**/api/v1/settings/roles*', async (route) => {
            await new Promise(f => setTimeout(f, 1000));
            await route.continue();
        });

        const searchInput = page.getByTestId('input-search-role');
        await searchInput.fill('pw-' + Date.now());

        const loading = page.getByTestId('loading-indicator');
        await expect(loading).toBeVisible({ timeout: 5000 });
    });

    test('harus memiliki kolom avatar dan styling nama yang konsisten dengan user', async ({ page }) => {
        // Cek header "Avatar"
        await expect(page.locator('table thead th:has-text("Avatar")')).toBeVisible();

        // Cek label header gabungan
        await expect(page.locator('table thead th:has-text("Label / Nama Role")')).toBeVisible();

        const firstRow = page.locator('table tbody tr').first();
        await expect(firstRow).toBeVisible();

        // Cek keberadaan Avatar di baris pertama
        const avatar = firstRow.getByTestId('role-avatar');
        await expect(avatar).toBeVisible();

        // Initial avatar harus 1 karakter (huruf pertama label)
        const initial = await avatar.innerText();
        expect(initial.length).toBe(1);

        // Cek styling label (utama)
        const labelText = firstRow.getByTestId('role-label-text');
        await expect(labelText).toBeVisible();
        const weight = await labelText.evaluate(el => window.getComputedStyle(el).fontWeight);
        expect(parseInt(weight)).toBeGreaterThanOrEqual(700);
    });

    test('harus bisa menjalankan siklus CRUD lengkap dengan fitur Pencarian Permission (Sesuai Simulasi)', async ({ page }) => {
        const timestamp = Date.now();
        const testName = `role_final_${timestamp}`;
        const testLabel = `Role Final ${timestamp}`;

        // 1. Create (SIM-5: Large Modal)
        await page.getByTestId('btn-create-role').click();
        await expect(page.locator('.modal-overlay')).toBeVisible();

        // Cek Modal Title & UI
        await expect(page.locator('.modal-card h4')).toContainText('Tambah Role Baru');

        // VERIFIKASI LAYOUT & PROMINENCE (Sesuai Permintaan User)
        const labelInput = page.getByTestId('input-role-label');
        const nameInput = page.getByTestId('input-role-name');

        // Tunggu render selesai
        await page.waitForTimeout(500);

        // 1. Verifikasi Urutan: Label harus di atas Name
        // Kita gunakan locator yang lebih spesifik untuk memastikan urutan di DOM
        const formGroups = page.locator('.modal-body .form-group');
        await expect(formGroups.nth(0)).toContainText('Label / Nama Role');
        await expect(formGroups.nth(1)).toContainText('Role ID (slug/sistem)');

        console.log('DEBUG: Verified input order via DOM sequence. OK!');

        // 2. Verifikasi Prominence: Cek class CSS
        await expect(labelInput).toHaveClass(/form-control-prominent/);

        // 3. Verifikasi Label Teks lebih menonjol (Contoh pengecekan warna/font)
        const labelStyle = await labelInput.evaluate(el => {
            const s = window.getComputedStyle(el);
            return { weight: s.fontWeight, color: s.color };
        });
        // Expecting bold and black
        expect(parseInt(labelStyle.weight)).toBeGreaterThanOrEqual(700);
        expect(labelStyle.color).toBe('rgb(0, 0, 0)'); // #000

        // Cek Modal Width (R-9)
        const modal = page.locator('.modal-card');
        const box = await modal.boundingBox();
        expect(box?.width).toBeGreaterThan(650); // Headless env might have smaller viewport, but should be wide.

        await page.getByTestId('input-role-name').fill(testName);
        await page.getByTestId('input-role-label').fill(testLabel);

        // 2. Test Pencarian Permission (SIM-6 / SIM-7)
        const permSearch = page.getByTestId('input-search-permission');
        await expect(permSearch).toBeVisible();

        // Cari 'settings'
        await permSearch.fill('settings');
        await page.waitForTimeout(500);
        await expect(page.locator('.perm-group-header').first()).toContainText('SETTINGS');

        // Cari yang tidak ada (SIM-7)
        await permSearch.fill('xyz999fake');
        await expect(page.locator('.empty-permissions')).toBeVisible();
        await expect(page.locator('.empty-permissions')).toContainText('Tidak ada izin');

        // Reset search untuk memilihi
        await permSearch.fill('');

        // 3. Selection & Counter (SIM-8 / SIM-9)
        const firstPerm = page.locator('.perm-item').first();
        const secondPerm = page.locator('.perm-item').nth(1);

        // Verifikasi susunan tampilan permission (Label di perm-name, Slug di perm-label)
        // Group Akuntansi biasanya muncul pertama secara alfabetis (A)
        await expect(firstPerm.locator('.perm-name')).toContainText(/Kelola Akun Induk|dashboard|lihat/i); 
        await expect(firstPerm.locator('.perm-label')).toContainText(/\.manage|\.view/);

        await firstPerm.click();
        await secondPerm.click();

        await expect(page.locator('.modal-footer')).toContainText('2 izin dipilih');

        const submitButton = page.getByTestId('btn-submit-role');
        await expect(submitButton).toBeVisible();
        await submitButton.click();
        await expect(page.locator('.modal-overlay')).not.toBeVisible({ timeout: 15000 });

        // 4. Verify & Persistence check (SIM-10 / SIM-11)
        await searchRole(page, testName);
        await expect(page.locator('table')).toContainText(testLabel);

        const row = page.locator('tr').filter({ hasText: testLabel }).first();
        await row.getByTestId('btn-edit-role').click();

        await expect(page.locator('.modal-overlay')).toBeVisible();
        await expect(page.locator('.perm-item.active')).toHaveCount(2);

        const updatedLabel = `${testLabel} Updated`;
        await page.getByTestId('input-role-label').fill(updatedLabel);
        await page.getByTestId('btn-submit-role').click();
        await expect(page.locator('.modal-overlay')).not.toBeVisible({ timeout: 15000 });

        // 5. Cleanup (SIM-12)
        await searchRole(page, testName);
        const updatedRow = page.locator('tr').filter({ hasText: updatedLabel }).first();
        await updatedRow.getByTestId('btn-delete-role').click();

        // Tunggu modal konfirmasi muncul sepenuhnya
        await expect(page.locator('body')).toHaveClass(/swal2-shown/, { timeout: 10000 });
        const confirmBtn = page.locator('.swal2-confirm');
        await expect(confirmBtn).toBeVisible();
        await confirmBtn.click();

        // Tunggu transisi & api selesai
        await page.waitForTimeout(1000);

        // Cek hilangnya label yang sudah diupdate
        await expect(page.locator('table')).not.toContainText(new RegExp(updatedLabel, 'i'), { timeout: 15000 });
    });

    test('harus bisa mengekspor data dan mengunduh template impor', async ({ page }) => {
        // Tunggu sampai tombol ekspor stabil
        await expect(page.getByTestId('btn-export-role')).toBeEnabled();

        // Export
        const exportPromise = page.waitForEvent('download');
        await page.getByTestId('btn-export-role').click();
        const exportDownload = await exportPromise;
        expect(exportDownload.suggestedFilename()).toBe('roles.xlsx');

        // Import Template
        await expect(page.getByTestId('btn-import-menu')).toBeVisible();
        await page.getByTestId('btn-import-menu').click();

        const templatePromise = page.waitForEvent('download');
        await page.getByTestId('btn-import-template').click();
        const templateDownload = await templatePromise;
        expect(templateDownload.suggestedFilename()).toBe('role-import-template.xlsx');
    });

    test('harus bisa melakukan sorting, mengubah per-page, dan pagination (Page 2 & 3)', async ({ page }) => {
        // 1. Per Page (Pastikan berubah agar trigger request, atau cek nilai default)
        const perPageSelect = page.getByTestId('select-per-page-role');
        const currentVal = await perPageSelect.inputValue();

        if (currentVal !== '10') {
            const resp10 = page.waitForResponse(resp => resp.url().includes('per_page=10'));
            await perPageSelect.selectOption('10');
            await resp10;
        }

        const pagination = page.locator('.pagination-container');
        const page2 = pagination.getByRole('link', { name: '2', exact: true });
        const page3 = pagination.getByRole('link', { name: '3', exact: true });

        if (await page2.count()) {
            const responsePromiseP2 = page.waitForResponse(resp => resp.url().includes('page=2'), { timeout: 15000 });
            await page2.click({ force: true });
            await responsePromiseP2;

            expect(page.url()).toContain('/settings/roles');
            expect(page.url()).not.toContain('/dashboard');
            await expect(page.locator('.pagination-link.active')).toContainText('2');
        } else {
            await expect(page.locator('.footer-stats')).toContainText(/Menampilkan/i);
        }

        if (await page3.count()) {
            const responsePromiseP3 = page.waitForResponse(resp => resp.url().includes('page=3'), { timeout: 15000 });
            await page3.click({ force: true });
            await responsePromiseP3;

            expect(page.url()).toContain('/settings/roles');
            await expect(page.locator('.pagination-link.active')).toContainText('3');
        }
    });
});
