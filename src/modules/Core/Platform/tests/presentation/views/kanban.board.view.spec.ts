import { test, expect, type BrowserContext, type Page } from '@playwright/test';

// Prefix unik agar data testing mudah diidentifikasi dan dibersihkan,
// tidak bentrok dengan data nyata maupun test lain (product pakai AAA-E2E-FILTER).
const CARD_PREFIX = 'E2E-SORA-KANBAN';

test.describe('Kanban Board E2E (real CRUD, tanpa mock)', () => {
    // Board /kanban publik (Livewire) — semua aksi lewat UI nyata:
    // create/edit/drag/delete, persistensi diverifikasi dengan reload,
    // dan seluruh data testing dihapus di akhir (afterAll + test penjaga).

    test.describe.configure({ mode: 'serial' });

    let context: BrowserContext;
    let page: Page;

    test.beforeAll(async ({ browser }) => {
        context = await browser.newContext();
        const setupPage = await context.newPage();
        // Bersihkan sisa run sebelumnya (mis. run crash) agar re-run selalu pass.
        await removeAllPrefixedCards(setupPage);
        await context.close();
    });

    test.beforeEach(async ({ browser }) => {
        context = await browser.newContext();
        page = await context.newPage();
        await page.goto('/kanban');
        await expect(page.getByTestId('kanban-board')).toBeVisible({ timeout: 30000 });
    });

    test.afterEach(async () => {
        await context.close();
    });

    test.afterAll(async ({ browser }) => {
        // Pengaman terakhir: tidak boleh ada data testing yang tertinggal.
        const cleanupContext = await browser.newContext();
        const cleanupPage = await cleanupContext.newPage();
        await removeAllPrefixedCards(cleanupPage);
        await cleanupContext.close();
    });

    test('papan kanban tampil tanpa login dengan 3 kolom', async () => {
        await expect(page.getByRole('heading', { name: 'Papan Kanban' })).toBeVisible();
        await expect(page.getByTestId('btn-open-create')).toBeVisible();

        for (const column of ['todo', 'in-progress', 'done']) {
            await expect(page.getByTestId(`kanban-column-${column}`)).toBeVisible();
        }
    });

    test('membuat kartu baru lewat form dan tersimpan permanen (real create)', async () => {
        const title = `${CARD_PREFIX} Create ${Date.now().toString(36)}`;

        await page.getByTestId('btn-open-create').click();
        await expect(page.getByTestId('kanban-form-modal')).toBeVisible();
        await expect(page.getByTestId('kanban-form-title')).toHaveText('Tambah Kartu');

        await page.getByTestId('input-form-title').fill(title);
        await page.getByTestId('select-form-label').selectOption('testing');
        await page.getByTestId('input-form-assignee').fill('Sora');
        await page.getByTestId('btn-save-form').click();

        await expect(page.getByTestId('kanban-form-modal')).toHaveCount(0);

        const created = page.locator('article.kanban-card', { hasText: title });
        await expect(created).toHaveCount(1);
        await expect(page.getByTestId('kanban-column-todo').locator('article.kanban-card', { hasText: title })).toHaveCount(1);
        await expect(created.locator('.kanban-label')).toContainText('Testing');
        await expect(created.locator('.kanban-assignee-name')).toHaveText('Sora');

        // Bukti real (bukan mock): data persist di server, bukan hanya state JS.
        await page.reload();
        await expect(page.getByTestId('kanban-column-todo').locator('article.kanban-card', { hasText: title })).toHaveCount(1);

        // Bersihkan kartu ini sendiri lewat UI (bagian dari real CRUD).
        await deleteCardViaUi(page, title);
    });

    test('mengedit judul dan label kartu lewat modal (real update)', async () => {
        const original = `${CARD_PREFIX} Edit ${Date.now().toString(36)}`;
        await createCard(page, original, 'bug');

        const beforeEdit = page.locator('article.kanban-card', { hasText: original });
        await expect(beforeEdit.locator('.kanban-label')).toContainText('Bug');

        await beforeEdit.getByTestId('btn-edit-card').click();
        await expect(page.getByTestId('kanban-form-title')).toHaveText('Edit Kartu');
        await expect(page.getByTestId('input-form-title')).toHaveValue(original);

        const updated = `${original} (diperbaiki)`;
        await page.getByTestId('input-form-title').fill(updated);
        await page.getByTestId('select-form-label').selectOption('optimalisasi');
        await page.getByTestId('btn-save-form').click();

        await expect(page.getByTestId('kanban-form-modal')).toHaveCount(0);
        await expect(page.locator('article.kanban-card', { hasText: original })).toHaveCount(0);

        const edited = page.locator('article.kanban-card', { hasText: updated });
        await expect(edited).toHaveCount(1);
        await expect(edited.locator('.kanban-label')).toContainText('Optimalisasi');

        await page.reload();
        await expect(page.locator('article.kanban-card', { hasText: updated })).toHaveCount(1);

        await deleteCardViaUi(page, updated);
    });

    test('memindahkan kartu antar kolom lewat drag (real move)', async () => {
        const title = `${CARD_PREFIX} Move ${Date.now().toString(36)}`;
        await createCard(page, title);

        const card = page.locator('article.kanban-card', { hasText: title });
        await card.dragTo(page.getByTestId('kanban-column-in-progress'));

        await expect(
            page.getByTestId('kanban-column-in-progress').locator('article.kanban-card', { hasText: title }),
        ).toHaveCount(1);
        await expect(
            page.getByTestId('kanban-column-todo').locator('article.kanban-card', { hasText: title }),
        ).toHaveCount(0);

        await page.reload();
        await expect(
            page.getByTestId('kanban-column-in-progress').locator('article.kanban-card', { hasText: title }),
        ).toHaveCount(1);

        await deleteCardViaUi(page, title);
    });

    test('menghapus kartu dengan konfirmasi SweetAlert (real delete)', async () => {
        const title = `${CARD_PREFIX} Delete ${Date.now().toString(36)}`;
        await createCard(page, title);

        const card = page.locator('article.kanban-card', { hasText: title });

        // Batal dulu: kartu tidak boleh hilang.
        await card.getByTestId('btn-delete-card').click();
        const popup = page.locator('.swal2-popup');
        await expect(popup).toBeVisible({ timeout: 15000 });
        await expect(popup).toContainText('Hapus kartu ini?');
        await expect(popup).toContainText(title);
        await page.getByRole('button', { name: 'Batal' }).click();
        await expect(card).toHaveCount(1);

        // Konfirmasi hapus: kartu hilang dan tetap hilang setelah reload.
        await card.getByTestId('btn-delete-card').click();
        await expect(page.locator('.swal2-popup')).toBeVisible({ timeout: 15000 });
        await page.getByRole('button', { name: 'Ya, Hapus' }).click();
        await expect(card).toHaveCount(0, { timeout: 15000 });

        await page.reload();
        await expect(page.locator('article.kanban-card', { hasText: title })).toHaveCount(0);
    });

    test('papan bersih dari semua data testing di akhir run', async () => {
        await expect(page.locator('article.kanban-card', { hasText: CARD_PREFIX })).toHaveCount(0);
    });
});

async function createCard(activePage: Page, title: string, label = ''): Promise<void> {
    await activePage.getByTestId('btn-open-create').click();
    await expect(activePage.getByTestId('kanban-form-modal')).toBeVisible();
    await activePage.getByTestId('input-form-title').fill(title);

    if (label) {
        await activePage.getByTestId('select-form-label').selectOption(label);
    }

    await activePage.getByTestId('btn-save-form').click();
    await expect(activePage.getByTestId('kanban-form-modal')).toHaveCount(0);
    await expect(activePage.locator('article.kanban-card', { hasText: title })).toHaveCount(1);
}

async function deleteCardViaUi(activePage: Page, title: string): Promise<void> {
    const card = activePage.locator('article.kanban-card', { hasText: title }).first();
    await card.getByTestId('btn-delete-card').click();
    await expect(activePage.locator('.swal2-popup')).toBeVisible({ timeout: 15000 });
    await activePage.getByRole('button', { name: 'Ya, Hapus' }).click();
    await expect(activePage.locator('article.kanban-card', { hasText: title })).toHaveCount(0, { timeout: 15000 });
}

async function removeAllPrefixedCards(activePage: Page): Promise<void> {
    await activePage.goto('/kanban');
    await expect(activePage.getByTestId('kanban-board')).toBeVisible({ timeout: 30000 });

    // Batas 40 iterasi = pengaman loop; board tidak mungkin punya lebih banyak
    // kartu testing dari satu prefix dalam kondisi wajar.
    for (let guard = 0; guard < 40; guard++) {
        const remaining = activePage.locator('article.kanban-card', { hasText: CARD_PREFIX });
        if ((await remaining.count()) === 0) {
            return;
        }

        const target = remaining.first();
        const title = (await target.locator('.kanban-card-text p').innerText()).trim();
        await deleteCardViaUi(activePage, title);
    }
}
