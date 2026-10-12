<?php

namespace Tests\Feature\Livewire;

use App\Livewire\PublicKanbanBoard;
use App\Modules\Core\Platform\Models\KanbanCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PublicKanbanBoardTest extends TestCase
{
    use RefreshDatabase;

    // -- CREATE ---------------------------------------------------------------

    public function test_shows_the_kanban_board_without_authentication(): void
    {
        $this->get('/kanban')
            ->assertOk()
            ->assertSee('Papan Kanban')
            ->assertSee('To Do');
    }

    public function test_adds_a_new_card_to_the_todo_column(): void
    {
        Livewire::test(PublicKanbanBoard::class)
            ->call('openCreateModal')
            ->assertSet('showFormModal', true)
            ->assertSet('formMode', 'create')
            ->set('formTitle', 'Tugas baru')
            ->call('saveCard')
            ->assertSee('Tugas baru')
            ->assertSet('showFormModal', false)
            ->assertViewHas('cards', function (array $cards): bool {
                return collect($cards)->contains(
                    fn (array $card): bool => $card['title'] === 'Tugas baru' && $card['column'] === 'todo'
                );
            });

        $this->assertDatabaseHas('kanban_cards', ['title' => 'Tugas baru']);
    }

    public function test_uploads_multiple_photos_when_adding_card(): void
    {
        Storage::fake('public');

        Livewire::test(PublicKanbanBoard::class)
            ->call('openCreateModal')
            ->set('formTitle', 'Dengan foto')
            ->set('photos', [
                UploadedFile::fake()->image('satu.png'),
                UploadedFile::fake()->image('dua.png'),
            ])
            ->call('saveCard');

        $card = KanbanCard::query()->where('title', 'Dengan foto')->first();

        $this->assertNotNull($card);
        $this->assertCount(2, $card->photos);
        Storage::disk('public')->assertExists($card->photos);
    }

    public function test_adds_card_with_label(): void
    {
        Livewire::test(PublicKanbanBoard::class)
            ->call('openCreateModal')
            ->set('formTitle', 'Fix crash')
            ->set('formLabel', 'bug')
            ->call('saveCard')
            ->assertSee('Bug');

        $this->assertDatabaseHas('kanban_cards', [
            'title' => 'Fix crash',
            'label' => 'bug',
        ]);
    }

    public function test_delegates_card_to_ai_agent(): void
    {
        Livewire::test(PublicKanbanBoard::class)
            ->call('openCreateModal')
            ->set('formTitle', 'Tugas AI')
            ->set('formAssignee', 'Claude')
            ->call('saveCard')
            ->assertSee('Claude');

        $this->assertDatabaseHas('kanban_cards', [
            'title' => 'Tugas AI',
            'assignee' => 'Claude',
        ]);
    }

    public function test_rejects_non_image_file_upload(): void
    {
        Livewire::test(PublicKanbanBoard::class)
            ->call('openCreateModal')
            ->set('formTitle', 'File invalid')
            ->set('photos', [UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf')])
            ->call('saveCard')
            ->assertHasErrors(['photos.0']);
    }

    public function test_rejects_more_than_ten_photos(): void
    {
        $files = array_map(
            fn (int $i) => UploadedFile::fake()->image("foto-{$i}.png"),
            range(1, 11)
        );

        Livewire::test(PublicKanbanBoard::class)
            ->call('openCreateModal')
            ->set('formTitle', 'Terlalu banyak')
            ->set('photos', $files)
            ->call('saveCard')
            ->assertHasErrors(['photos']);
    }

    // -- READ -----------------------------------------------------------------

    public function test_mounts_existing_cards_with_photos_from_database(): void
    {
        Storage::fake('public');

        $card = KanbanCard::create([
            'title' => 'Kartu tersimpan',
            'photos' => ['kanban-photos/tersimpan.png'],
            'column_name' => 'in-progress',
            'position' => 1,
        ]);

        Storage::disk('public')->put('kanban-photos/tersimpan.png', 'fake-image');

        Livewire::test(PublicKanbanBoard::class)
            ->assertSee('Kartu tersimpan')
            ->assertViewHas('cards', function (array $cards) use ($card): bool {
                $found = collect($cards)->firstWhere('id', (string) $card->id);

                return $found !== null
                    && $found['column'] === 'in-progress'
                    && $found['photos'] === ['kanban-photos/tersimpan.png'];
            });
    }

    // -- UPDATE ---------------------------------------------------------------

    public function test_moves_a_card_between_columns(): void
    {
        $card = KanbanCard::create([
            'title' => 'Kartu uji',
            'column_name' => 'todo',
            'position' => 1,
        ]);

        Livewire::test(PublicKanbanBoard::class)
            ->call('moveCard', (string) $card->id, 'done')
            ->assertViewHas('cards', function (array $cards) use ($card): bool {
                $moved = collect($cards)->firstWhere('id', (string) $card->id);

                return $moved['column'] === 'done';
            });

        $this->assertDatabaseHas('kanban_cards', [
            'id' => $card->id,
            'column_name' => 'done',
        ]);
    }

    public function test_updates_card_title_and_label(): void
    {
        $card = KanbanCard::create([
            'title' => 'Judul lama',
            'label' => 'bug',
            'column_name' => 'todo',
            'position' => 1,
        ]);

        Livewire::test(PublicKanbanBoard::class)
            ->call('openEditModal', (string) $card->id)
            ->assertSet('showFormModal', true)
            ->assertSet('formMode', 'edit')
            ->assertSet('formTitle', 'Judul lama')
            ->assertSet('formLabel', 'bug')
            ->set('formTitle', 'Judul baru')
            ->set('formLabel', 'optimalisasi')
            ->set('formAssignee', 'ChatGPT')
            ->call('saveCard')
            ->assertSee('Judul baru')
            ->assertSee('Optimalisasi')
            ->assertSee('ChatGPT')
            ->assertSet('showFormModal', false);

        $this->assertDatabaseHas('kanban_cards', [
            'id' => $card->id,
            'title' => 'Judul baru',
            'label' => 'optimalisasi',
            'assignee' => 'ChatGPT',
        ]);
    }

    public function test_rejects_invalid_label(): void
    {
        Livewire::test(PublicKanbanBoard::class)
            ->call('openCreateModal')
            ->set('formTitle', 'Invalid label')
            ->set('formLabel', 'hack')
            ->call('saveCard')
            ->assertHasErrors(['formLabel']);
    }

    public function test_closes_form_modal_and_resets_fields(): void
    {
        Livewire::test(PublicKanbanBoard::class)
            ->call('openCreateModal')
            ->set('formTitle', 'Draft')
            ->set('formAssignee', 'Claude')
            ->call('closeFormModal')
            ->assertSet('showFormModal', false)
            ->assertSet('formTitle', '')
            ->assertSet('formAssignee', '');
    }

    public function test_delete_confirmation_dispatches_card_title(): void
    {
        $card = KanbanCard::create([
            'title' => 'Kartu konfirmasi',
            'column_name' => 'todo',
            'position' => 1,
        ]);

        Livewire::test(PublicKanbanBoard::class)
            ->call('confirmRemoveCard', (string) $card->id)
            ->assertDispatched('kanban-confirm-delete', id: (string) $card->id, title: 'Kartu konfirmasi');

        $this->assertDatabaseHas('kanban_cards', ['id' => $card->id]);
    }

    // -- DELETE ---------------------------------------------------------------

    public function test_removes_card_and_deletes_photos_from_storage(): void
    {
        Storage::fake('public');

        $card = KanbanCard::create([
            'title' => 'Hapus saya',
            'photos' => ['kanban-photos/hapus.png'],
            'column_name' => 'todo',
            'position' => 1,
        ]);

        Storage::disk('public')->put('kanban-photos/hapus.png', 'fake-image');

        Livewire::test(PublicKanbanBoard::class)
            ->call('removeCard', (string) $card->id);

        $this->assertDatabaseMissing('kanban_cards', ['id' => $card->id]);
        Storage::disk('public')->assertMissing('kanban-photos/hapus.png');
    }

    public function test_deletes_single_photo_from_card(): void
    {
        Storage::fake('public');

        $card = KanbanCard::create([
            'title' => 'Dua foto',
            'photos' => ['kanban-photos/keep.png', 'kanban-photos/remove.png'],
            'column_name' => 'todo',
            'position' => 1,
        ]);

        Storage::disk('public')->put('kanban-photos/keep.png', 'keep');
        Storage::disk('public')->put('kanban-photos/remove.png', 'remove');

        Livewire::test(PublicKanbanBoard::class)
            ->call('deleteCardPhoto', (string) $card->id, 'kanban-photos/remove.png');

        $card->refresh();
        $this->assertSame(['kanban-photos/keep.png'], $card->photos);
        Storage::disk('public')->assertMissing('kanban-photos/remove.png');
        Storage::disk('public')->assertExists('kanban-photos/keep.png');
    }

    // -- PREVIEW MODAL ---------------------------------------------------------

    public function test_opens_photo_preview_modal(): void
    {
        Storage::fake('public');

        $card = KanbanCard::create([
            'title' => 'Preview foto',
            'photos' => ['kanban-photos/preview.png'],
            'column_name' => 'todo',
            'position' => 1,
        ]);

        Storage::disk('public')->put('kanban-photos/preview.png', 'fake-image');

        Livewire::test(PublicKanbanBoard::class)
            ->call('openPreview', 'kanban-photos/preview.png')
            ->assertSet('previewPhoto', 'kanban-photos/preview.png')
            ->assertSet('previewZoom', 1.0)
            ->assertSee('kanban-modal-overlay')
            ->assertSee('kanban-modal-image');
    }

    public function test_closes_photo_preview_modal(): void
    {
        Livewire::test(PublicKanbanBoard::class)
            ->call('openPreview', 'kanban-photos/test.png')
            ->assertSet('previewPhoto', 'kanban-photos/test.png')
            ->call('closePreview')
            ->assertSet('previewPhoto', '')
            ->assertSet('previewZoom', 1.0)
            ->assertDontSee('kanban-modal-overlay');
    }

    public function test_zooms_photo_in_preview(): void
    {
        Livewire::test(PublicKanbanBoard::class)
            ->call('openPreview', 'kanban-photos/test.png')
            ->call('zoomIn')
            ->assertSet('previewZoom', 1.25)
            ->call('zoomIn')
            ->assertSet('previewZoom', 1.5)
            ->call('zoomOut')
            ->assertSet('previewZoom', 1.25)
            ->call('resetZoom')
            ->assertSet('previewZoom', 1.0);
    }

    public function test_zoom_respects_bounds(): void
    {
        $component = Livewire::test(PublicKanbanBoard::class)
            ->call('openPreview', 'kanban-photos/test.png');

        for ($i = 0; $i < 20; $i++) {
            $component->call('zoomIn');
        }
        $component->assertSet('previewZoom', 4.0);

        for ($i = 0; $i < 25; $i++) {
            $component->call('zoomOut');
        }
        $component->assertSet('previewZoom', 0.5);
    }
}
