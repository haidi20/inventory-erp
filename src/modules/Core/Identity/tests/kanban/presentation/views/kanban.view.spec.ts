import { test, expect } from '@playwright/test';

const TINY_PNG = Buffer.from(
  'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
  'base64'
);

test.describe('Kanban View E2E', () => {
  test('menampilkan papan kanban tanpa login', async ({ page }) => {
    await page.goto('/kanban');
    await expect(page.getByRole('heading', { name: 'Papan Kanban' })).toBeVisible();
    await expect(page.getByText('To Do')).toBeVisible();
  });

  test('form create dan edit memakai modal', async ({ page }) => {
    await page.goto('/kanban');

    const uniqueTs = Date.now();
    const title = `Modal Test ${uniqueTs}`;
    const updatedTitle = `Modal Updated ${uniqueTs}`;

    // -- CREATE via modal --
    await page.getByTestId('btn-open-create').click();
    const formModal = page.getByTestId('kanban-form-modal');
    await expect(formModal).toBeVisible({ timeout: 15000 });
    await expect(page.getByTestId('kanban-form-title')).toContainText('Tambah Kartu');

    await page.getByLabel('Judul kartu').fill(title);
    await page.getByLabel('Label kartu').selectOption('fitur');
    await page.getByTestId('btn-save-form').click();

    const todoColumn = page.getByTestId('kanban-column-todo');
    const card = todoColumn.locator('.kanban-card').filter({ hasText: title });
    await expect(card).toBeVisible({ timeout: 15000 });
    await expect(page.getByTestId('kanban-form-overlay')).not.toBeVisible();

    // -- EDIT via modal --
    await card.getByTestId('btn-edit-card').click();
    await expect(page.getByTestId('kanban-form-title')).toContainText('Edit Kartu');
    await expect(page.getByLabel('Judul kartu')).toHaveValue(title);

    await page.getByLabel('Judul kartu').fill(updatedTitle);
    await page.getByLabel('Label kartu').selectOption('optimalisasi');
    await page.getByLabel('Delegasikan ke').fill('Claude');
    await page.getByTestId('btn-save-form').click();

    const updatedCard = todoColumn.locator('.kanban-card').filter({ hasText: updatedTitle });
    await expect(updatedCard).toBeVisible({ timeout: 15000 });
    await expect(updatedCard.locator('.kanban-label')).toContainText('Optimalisasi');
    await expect(updatedCard.getByTestId('kanban-assignee')).toContainText('Claude');
    await expect(page.getByTestId('kanban-form-overlay')).not.toBeVisible();
  });

  test('tombol batal menutup modal form', async ({ page }) => {
    await page.goto('/kanban');

    await page.getByTestId('btn-open-create').click();
    await expect(page.getByTestId('kanban-form-modal')).toBeVisible({ timeout: 15000 });

    await page.getByTestId('btn-cancel-form').click();
    await expect(page.getByTestId('kanban-form-overlay')).not.toBeVisible({ timeout: 15000 });
  });

  test('bisa upload banyak foto dan paste dari clipboard', async ({ page }) => {
    await page.goto('/kanban');

    const cardTitle = `Kartu uji foto ${Date.now()}`;
    await page.getByTestId('btn-open-create').click();
    await page.getByLabel('Judul kartu').fill(cardTitle);
    await page.getByLabel('Label kartu').selectOption('bug');

    await page.locator('#kanban-photo-input').setInputFiles([
      { name: 'satu.png', mimeType: 'image/png', buffer: TINY_PNG },
      { name: 'dua.png', mimeType: 'image/png', buffer: TINY_PNG },
    ]);
    await expect(page.getByText('foto siap diunggah')).toBeVisible({ timeout: 15000 });

    await page.evaluate(() => {
      const file = new File([new Uint8Array([137, 80, 78, 71])], 'paste.png', { type: 'image/png' });
      const pasteEvent = new Event('paste') as ClipboardEvent;
      Object.defineProperty(pasteEvent, 'clipboardData', {
        value: { files: [file] },
      });
      document.dispatchEvent(pasteEvent);
    });

    await page.getByTestId('btn-save-form').click();

    await expect(page.getByText(cardTitle).first()).toBeVisible({ timeout: 15000 });
  });

  test('klik foto membuka modal preview dengan zoom', async ({ page }) => {
    await page.goto('/kanban');

    const uniqueTitle = `Test preview ${Date.now()}`;
    await page.getByTestId('btn-open-create').click();
    await page.getByLabel('Judul kartu').fill(uniqueTitle);
    await page.locator('#kanban-photo-input').setInputFiles([
      { name: 'preview.png', mimeType: 'image/png', buffer: TINY_PNG },
    ]);
    await expect(page.getByText('foto siap diunggah')).toBeVisible({ timeout: 15000 });
    await page.getByTestId('btn-save-form').click();

    const card = page.locator('.kanban-card').filter({ hasText: uniqueTitle }).first();
    await expect(card).toBeVisible({ timeout: 15000 });

    const photo = card.getByTestId('btn-preview-photo');
    await expect(photo).toBeVisible({ timeout: 15000 });
    await photo.click();

    const overlay = page.getByTestId('kanban-modal-overlay');
    await expect(overlay).toBeVisible({ timeout: 15000 });
    await expect(page.getByTestId('kanban-modal-image')).toBeVisible({ timeout: 15000 });
    await expect(overlay).toHaveCSS('background-color', 'rgba(0, 0, 0, 0.75)');

    await page.getByTestId('btn-zoom-in').click();
    await expect(page.getByTestId('zoom-label')).toContainText('125%');

    await page.getByTestId('btn-zoom-out').click();
    await expect(page.getByTestId('zoom-label')).toContainText('100%');

    await page.getByTestId('btn-close-preview').click();
    await expect(overlay).not.toBeVisible();
  });

  test('crud lengkap kartu: create, edit, geser kolom, hapus', async ({ page }) => {
    await page.goto('/kanban');

    const uniqueTs = Date.now();
    const title = `CRUD Test ${uniqueTs}`;
    const updatedTitle = `CRUD Updated ${uniqueTs}`;

    // -- CREATE: tambah kartu dengan label bug (modal) --
    await page.getByTestId('btn-open-create').click();
    await page.getByLabel('Judul kartu').fill(title);
    await page.getByLabel('Label kartu').selectOption('bug');
    await page.getByTestId('btn-save-form').click();

    const todoColumn = page.getByTestId('kanban-column-todo');
    const card = todoColumn.locator('.kanban-card').filter({ hasText: title });
    await expect(card).toBeVisible({ timeout: 15000 });
    await expect(card.locator('.kanban-label')).toContainText('Bug');

    // -- READ: kartu muncul di kolom To Do --
    await expect(card).toBeVisible();

    // -- UPDATE: edit judul + ganti label (modal) --
    await card.getByTestId('btn-edit-card').click();
    await expect(page.getByTestId('kanban-form-modal')).toBeVisible({ timeout: 15000 });
    await page.getByLabel('Judul kartu').fill(updatedTitle);
    await page.getByTestId('select-form-label').selectOption('optimalisasi');
    await page.getByLabel('Delegasikan ke').fill('Claude');
    await page.getByTestId('btn-save-form').click();

    const updatedCard = todoColumn.locator('.kanban-card').filter({ hasText: updatedTitle });
    await expect(updatedCard).toBeVisible({ timeout: 15000 });
    await expect(updatedCard.locator('.kanban-label')).toContainText('Optimalisasi');
    await expect(updatedCard.getByTestId('kanban-assignee')).toContainText('Claude');

    // -- UPDATE: geser kartu ke kolom Done (panggil moveCard via Livewire) --
    await page.evaluate(() => {
        const wireEl = document.querySelector('[wire\\:id]') ?? document.querySelector('[wire\\:snapshot]');
        if (wireEl && window.Livewire) {
            const id = wireEl.getAttribute('wire:id');
            if (window.Livewire.first) {
                const comp = window.Livewire.first();
                if (comp) {
                    const cards = comp.get('cards') ?? [];
                    const target = [...cards].reverse().find((c: any) => c.title?.includes('CRUD Updated'));
                    if (target) {
                        (window as any).__kanbanTargetId = target.id;
                    }
                }
            }
        }
    });
    const cardIdToMove = await page.evaluate(() => (window as any).__kanbanTargetId ?? '');
    await page.evaluate((cid: string) => {
        const wireEl = document.querySelector('[wire\\:id]');
        if (wireEl && window.Livewire && cid) {
            window.Livewire.find(wireEl.getAttribute('wire:id')!).call('moveCard', cid, 'done');
        }
    }, cardIdToMove);
    await page.waitForTimeout(2000);

    const doneColumn = page.getByTestId('kanban-column-done');
    await expect(doneColumn.locator('.kanban-card').filter({ hasText: updatedTitle }).last()).toBeVisible({ timeout: 15000 });
    const movedCard = doneColumn.locator('.kanban-card').filter({ hasText: updatedTitle }).last();
    await expect(movedCard).toBeVisible({ timeout: 15000 });
    await expect(todoColumn.locator('.kanban-card').filter({ hasText: updatedTitle })).toHaveCount(0);

    // -- DELETE: konfirmasi Swal dengan judul kartu --
    await movedCard.getByTestId('btn-delete-card').click();
    const swalPopup = page.locator('.swal2-popup');
    await expect(swalPopup).toBeVisible({ timeout: 15000 });
    await expect(swalPopup).toContainText(`"${updatedTitle}" akan dihapus permanen.`);
    await swalPopup.getByRole('button', { name: 'Ya, Hapus' }).click();
    await expect(doneColumn.locator('.kanban-card').filter({ hasText: updatedTitle })).toHaveCount(0, { timeout: 15000 });
  });

  test('batal di Swal tidak menghapus kartu', async ({ page }) => {
    await page.goto('/kanban');

    const title = `Swal batal ${Date.now()}`;
    await page.getByTestId('btn-open-create').click();
    await page.getByLabel('Judul kartu').fill(title);
    await page.getByTestId('btn-save-form').click();

    const card = page.locator('.kanban-card').filter({ hasText: title }).first();
    await expect(card).toBeVisible({ timeout: 15000 });

    await card.getByTestId('btn-delete-card').click();
    const swalPopup = page.locator('.swal2-popup');
    await expect(swalPopup).toBeVisible({ timeout: 15000 });
    await expect(swalPopup).toContainText(title);
    await swalPopup.getByRole('button', { name: 'Batal' }).click();

    await expect(swalPopup).not.toBeVisible({ timeout: 15000 });
    await expect(page.locator('.kanban-card').filter({ hasText: title }).first()).toBeVisible();
  });

  test('validasi: tidak bisa tambah kartu tanpa judul', async ({ page }) => {
    await page.goto('/kanban');

    await page.getByTestId('btn-open-create').click();
    await page.getByTestId('btn-save-form').click();

    const errors = page.getByTestId('kanban-errors');
    await expect(errors).toBeVisible({ timeout: 15000 });
    await expect(errors).toContainText('form title');
  });

  test('upload foto invalid (bukan gambar) ditolak', async ({ page }) => {
    await page.goto('/kanban');

    const fakePdf = Buffer.from('%PDF-1.4 fake pdf content');
    await page.getByTestId('btn-open-create').click();
    await page.getByLabel('Judul kartu').fill('Invalid file test');
    await page.locator('#kanban-photo-input').setInputFiles([
      { name: 'fake.pdf', mimeType: 'application/pdf', buffer: fakePdf },
    ]);
    await expect(page.getByText('foto siap diunggah')).toBeVisible({ timeout: 15000 });
    await page.getByTestId('btn-save-form').click();

    const errors = page.getByTestId('kanban-errors');
    await expect(errors).toBeVisible({ timeout: 15000 });
    await expect(errors).toContainText('must be an image');
  });

  test('hapus satu foto dari kartu multi-foto', async ({ page }) => {
    await page.goto('/kanban');

    const title = `Multi-foto ${Date.now()}`;
    await page.getByTestId('btn-open-create').click();
    await page.getByLabel('Judul kartu').fill(title);
    await page.locator('#kanban-photo-input').setInputFiles([
      { name: 'satu.png', mimeType: 'image/png', buffer: TINY_PNG },
      { name: 'dua.png', mimeType: 'image/png', buffer: TINY_PNG },
    ]);
    await expect(page.getByText('foto siap diunggah')).toBeVisible({ timeout: 15000 });
    await page.getByTestId('btn-save-form').click();

    const card = page.locator('.kanban-card').filter({ hasText: title }).first();
    await expect(card).toBeVisible({ timeout: 15000 });
    await expect(card.locator('img.kanban-card-photo')).toHaveCount(2, { timeout: 15000 });

    await card.getByTestId('btn-delete-photo').first().click();
    await expect(card.locator('img.kanban-card-photo')).toHaveCount(1, { timeout: 15000 });
  });
});
