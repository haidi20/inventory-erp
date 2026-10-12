<?php

namespace App\Livewire;

use App\Modules\Core\Platform\Models\KanbanCard;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.kanban')]
#[Title('Papan Kanban')]
class PublicKanbanBoard extends Component
{
    use WithFileUploads;

    public const LABELS = [
        'bug' => ['text' => 'Bug', 'color' => '#ef4444'],
        'optimalisasi' => ['text' => 'Optimalisasi', 'color' => '#f59e0b'],
        'fitur' => ['text' => 'Fitur', 'color' => '#3b82f6'],
        'dokumentasi' => ['text' => 'Dokumentasi', 'color' => '#8b5cf6'],
        'testing' => ['text' => 'Testing', 'color' => '#14b8a6'],
    ];

    public array $columns = [
        'todo' => ['id' => 'todo', 'title' => 'To Do', 'color' => '#6366f1'],
        'in-progress' => ['id' => 'in-progress', 'title' => 'In Progress', 'color' => '#f59e0b'],
        'done' => ['id' => 'done', 'title' => 'Done', 'color' => '#10b981'],
    ];

    public array $cards = [];

    public bool $showFormModal = false;

    public string $formMode = 'create';

    public string $formCardId = '';

    public string $formTitle = '';

    public string $formLabel = '';

    public string $formAssignee = '';

    public string $draggingCardId = '';

    public array $photos = [];

    public string $previewPhoto = '';

    public float $previewZoom = 1.0;

    public function mount(): void
    {
        $this->cards = KanbanCard::query()
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn (KanbanCard $card): array => [
                'id' => (string) $card->id,
                'title' => $card->title,
                'label' => $card->label,
                'assignee' => $card->assignee,
                'photos' => $card->photos ?? [],
                'column' => $card->column_name,
            ])
            ->all();
    }

    public function openCreateModal(): void
    {
        $this->reset('formCardId', 'formTitle', 'formLabel', 'formAssignee', 'photos');
        $this->formMode = 'create';
        $this->showFormModal = true;
    }

    public function openEditModal(string $cardId): void
    {
        $card = collect($this->cards)->firstWhere('id', $cardId);

        if ($card === null) {
            return;
        }

        $this->reset('photos');
        $this->formMode = 'edit';
        $this->formCardId = $cardId;
        $this->formTitle = $card['title'];
        $this->formLabel = $card['label'] ?? '';
        $this->formAssignee = $card['assignee'] ?? '';
        $this->showFormModal = true;
    }

    public function closeFormModal(): void
    {
        $this->reset('showFormModal', 'formCardId', 'formTitle', 'formLabel', 'formAssignee', 'photos');
        $this->formMode = 'create';
    }

    public function saveCard(): void
    {
        $this->validate([
            'formTitle' => ['required', 'string', 'max:120'],
            'formLabel' => ['nullable', 'string', 'in:' . implode(',', array_keys(self::LABELS))],
            'formAssignee' => ['nullable', 'string', 'max:120'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'max:2048'],
        ]);

        if ($this->formMode === 'edit') {
            $this->persistUpdatedCard();
        } else {
            $this->persistCreatedCard();
        }

        $this->closeFormModal();
    }

    private function persistCreatedCard(): void
    {
        $storedPhotos = collect($this->photos)
            ->map(fn ($file) => $file->store('kanban-photos', 'public'))
            ->all();

        $card = KanbanCard::create([
            'title' => trim($this->formTitle),
            'label' => $this->formLabel ?: null,
            'assignee' => trim($this->formAssignee) ?: null,
            'photos' => ! empty($storedPhotos) ? $storedPhotos : null,
            'column_name' => 'todo',
            'position' => (int) KanbanCard::where('column_name', 'todo')->max('position') + 1,
        ]);

        $this->cards[] = [
            'id' => (string) $card->id,
            'title' => $card->title,
            'label' => $card->label,
            'assignee' => $card->assignee,
            'photos' => $card->photos ?? [],
            'column' => $card->column_name,
        ];
    }

    private function persistUpdatedCard(): void
    {
        KanbanCard::whereKey($this->formCardId)->update([
            'title' => trim($this->formTitle),
            'label' => $this->formLabel ?: null,
            'assignee' => trim($this->formAssignee) ?: null,
        ]);

        foreach ($this->cards as $index => $card) {
            if ($card['id'] === $this->formCardId) {
                $this->cards[$index]['title'] = trim($this->formTitle);
                $this->cards[$index]['label'] = $this->formLabel ?: null;
                $this->cards[$index]['assignee'] = trim($this->formAssignee) ?: null;
                break;
            }
        }
    }

    public function moveCard(string $cardId, string $columnId): void
    {
        if (! array_key_exists($columnId, $this->columns)) {
            return;
        }

        KanbanCard::query()
            ->whereKey($cardId)
            ->update([
                'column_name' => $columnId,
                'position' => (int) KanbanCard::where('column_name', $columnId)->max('position') + 1,
            ]);

        foreach ($this->cards as $index => $card) {
            if ($card['id'] === $cardId) {
                $this->cards[$index]['column'] = $columnId;
                break;
            }
        }
    }

    public function startDrag(string $cardId): void
    {
        $this->draggingCardId = $cardId;
    }

    public function dropCard(string $columnId): void
    {
        if ($this->draggingCardId !== '') {
            $this->moveCard($this->draggingCardId, $columnId);
        }

        $this->draggingCardId = '';
    }

    public function removeCard(string $cardId): void
    {
        $card = KanbanCard::query()->find($cardId);

        if ($card !== null) {
            foreach ($card->photos ?? [] as $photo) {
                Storage::disk('public')->delete($photo);
            }

            $card->delete();
        }

        $this->cards = array_values(array_filter(
            $this->cards,
            fn (array $card): bool => $card['id'] !== $cardId
        ));
    }

    public function confirmRemoveCard(string $cardId): void
    {
        $card = KanbanCard::query()->find($cardId);

        if ($card === null) {
            return;
        }

        $this->dispatch('kanban-confirm-delete', id: $cardId, title: $card->title);
    }

    public function labelOptions(): array
    {
        return self::LABELS;
    }

    public function agentSuggestions(): array
    {
        return [
            'Claude',
            'ChatGPT',
            'Gemini',
            'Copilot',
            'Cursor Agent',
            'Codex',
            'DeepSeek',
        ];
    }

    public function deleteCardPhoto(string $cardId, string $photoPath): void
    {
        $card = KanbanCard::query()->find($cardId);

        if ($card === null || ! in_array($photoPath, $card->photos ?? [], true)) {
            return;
        }

        Storage::disk('public')->delete($photoPath);

        $remaining = array_values(array_filter(
            $card->photos,
            fn (string $path): bool => $path !== $photoPath
        ));

        $card->update(['photos' => ! empty($remaining) ? $remaining : null]);

        foreach ($this->cards as $index => $cardItem) {
            if ($cardItem['id'] === $cardId) {
                $this->cards[$index]['photos'] = $remaining;
                break;
            }
        }
    }

    public function cardsFor(string $columnId): array
    {
        return array_values(array_filter(
            $this->cards,
            fn (array $card): bool => $card['column'] === $columnId
        ));
    }

    public function openPreview(string $photoPath): void
    {
        $this->previewPhoto = $photoPath;
        $this->previewZoom = 1.0;
    }

    public function closePreview(): void
    {
        $this->reset('previewPhoto', 'previewZoom');
    }

    public function zoomIn(): void
    {
        $this->previewZoom = min(round($this->previewZoom + 0.25, 2), 4.0);
    }

    public function zoomOut(): void
    {
        $this->previewZoom = max(round($this->previewZoom - 0.25, 2), 0.5);
    }

    public function resetZoom(): void
    {
        $this->previewZoom = 1.0;
    }

    public function render()
    {
        return view('livewire.public-kanban-board');
    }
}
