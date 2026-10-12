<main class="kanban-shell">
    <header class="kanban-header">
        <div>
            <h1>Papan Kanban</h1>
            <p>Tambah kartu, lampirkan foto, geser antar kolom, atau hapus jika sudah selesai.</p>
        </div>

        <button
            type="button"
            wire:click="openCreateModal"
            class="kanban-open-form-btn"
            data-testid="btn-open-create"
        >+ Tambah Kartu</button>
    </header>

    <datalist id="kanban-agent-suggestions">
        @foreach ($this->agentSuggestions() as $agent)
            <option value="{{ $agent }}"></option>
        @endforeach
    </datalist>

    <div class="kanban-board" data-testid="kanban-board">
        @foreach ($columns as $column)
            <section
                wire:drop.prevent="dropCard('{{ $column['id'] }}')"
                wire:dragover.prevent
                wire:dragenter.prevent
                class="kanban-column"
                data-testid="kanban-column-{{ $column['id'] }}"
                data-column-id="{{ $column['id'] }}"
            >
                <div class="kanban-column-head">
                    <span class="kanban-dot" style="background: {{ $column['color'] }}"></span>
                    <h2>{{ $column['title'] }}</h2>
                    <span class="kanban-count">{{ count($this->cardsFor($column['id'])) }}</span>
                </div>

                @forelse ($this->cardsFor($column['id']) as $card)
                    <article
                        draggable="true"
                        wire:dragstart="startDrag('{{ $card['id'] }}')"
                        wire:dragend="draggingCardId = ''"
                        class="kanban-card"
                    >
                        @if (! empty($card['photos']))
                            <div class="kanban-card-photos">
                                @foreach ($card['photos'] as $photo)
                                    <div class="kanban-card-photo-wrapper">
                                        <button
                                            type="button"
                                            wire:click="openPreview('{{ $photo }}')"
                                            class="kanban-photo-trigger"
                                            data-testid="btn-preview-photo"
                                            aria-label="Preview foto {{ $card['title'] }}"
                                        >
                                            <img
                                                src="{{ \Illuminate\Support\Facades\Storage::url($photo) }}"
                                                alt="{{ $card['title'] }}"
                                                class="kanban-card-photo"
                                                loading="lazy"
                                            >
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="deleteCardPhoto('{{ $card['id'] }}', '{{ $photo }}')"
                                            class="kanban-photo-delete"
                                            data-testid="btn-delete-photo"
                                            aria-label="Hapus foto dari {{ $card['title'] }}"
                                        >&times;</button>
                                    </div>
                                @endforeach
                        </div>
                        @endif

                            <div class="kanban-card-content">
                                <div class="kanban-card-text">
                                    @if (! empty($card['label']))
                                        @php($labelInfo = $this->labelOptions()[$card['label']] ?? null)
                                        @if ($labelInfo)
                                            <span class="kanban-label" style="background: {{ $labelInfo['color'] }}">{{ $labelInfo['text'] }}</span>
                                        @endif
                                    @endif
                                    <p>{{ $card['title'] }}</p>
                                </div>
                                @if (! empty($card['assignee']))
                                    <div class="kanban-assignee" data-testid="kanban-assignee">
                                        <span class="kanban-assignee-avatar">{{ strtoupper(substr($card['assignee'], 0, 1)) }}</span>
                                        <span class="kanban-assignee-name">{{ $card['assignee'] }}</span>
                                    </div>
                                @endif
                                <div class="kanban-card-actions">
                                    <button
                                        type="button"
                                        wire:click="openEditModal('{{ $card['id'] }}')"
                                        data-testid="btn-edit-card"
                                        aria-label="Edit {{ $card['title'] }}"
                                    >&#9998;</button>
                                    <button
                                        type="button"
                                        wire:click="confirmRemoveCard('{{ $card['id'] }}')"
                                        data-testid="btn-delete-card"
                                        aria-label="Hapus {{ $card['title'] }}"
                                    >&times;</button>
                                </div>
                            </div>
                    </article>
                @empty
                    <p class="kanban-empty" data-testid="kanban-empty-{{ $column['id'] }}">Belum ada kartu</p>
                @endforelse
            </section>
        @endforeach
    </div>

    @if ($showFormModal)
        <div class="kanban-modal-overlay kanban-form-overlay" data-testid="kanban-form-overlay">
            <button type="button" class="kanban-modal-backdrop" wire:click="closeFormModal" aria-label="Tutup form"></button>
            <form wire:submit="saveCard" class="kanban-form-modal" data-testid="kanban-form-modal">
                <div class="kanban-form-modal-head">
                    <h2 data-testid="kanban-form-title">{{ $formMode === 'edit' ? 'Edit Kartu' : 'Tambah Kartu' }}</h2>
                    <button type="button" wire:click="closeFormModal" data-testid="btn-close-form" aria-label="Tutup form">&times;</button>
                </div>

                @if ($errors->any())
                    <div data-testid="kanban-errors">
                        @foreach ($errors->all() as $error)
                            <p class="kanban-error">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <label class="kanban-form-field">
                    <span>Judul</span>
                    <input
                        type="text"
                        wire:model="formTitle"
                        aria-label="Judul kartu"
                        maxlength="120"
                        data-testid="input-form-title"
                    >
                </label>

                <label class="kanban-form-field">
                    <span>Label</span>
                    <select wire:model="formLabel" class="kanban-label-select" aria-label="Label kartu" data-testid="select-form-label">
                        <option value="">Tanpa label</option>
                        @foreach ($this->labelOptions() as $key => $info)
                            <option value="{{ $key }}">{{ $info['text'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="kanban-form-field">
                    <span>Delegasikan ke</span>
                    <input
                        type="text"
                        wire:model="formAssignee"
                        list="kanban-agent-suggestions"
                        placeholder="Delegasikan ke AI agent..."
                        aria-label="Delegasikan ke"
                        maxlength="120"
                        data-testid="input-form-assignee"
                    >
                </label>

                @if ($formMode === 'create')
                    <label class="kanban-form-field">
                        <span>Lampiran foto (maks 10)</span>
                        <input
                            type="file"
                            wire:model="photos"
                            multiple
                            accept="image/png,image/jpeg,image/jpg,image/webp"
                            aria-label="Lampiran foto"
                            id="kanban-photo-input"
                            class="kanban-photo-input"
                        >
                    </label>

                    @if (! empty($photos))
                        <p class="kanban-photo-count">{{ count($photos) }} foto siap diunggah</p>
                    @endif
                @endif

                <div class="kanban-form-actions">
                    <button type="button" wire:click="closeFormModal" class="kanban-btn-cancel" data-testid="btn-cancel-form">Batal</button>
                    <button type="submit" class="kanban-btn-save" data-testid="btn-save-form">
                        {{ $formMode === 'edit' ? 'Simpan' : 'Tambah' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if ($previewPhoto !== '')
        <div class="kanban-modal-overlay" data-testid="kanban-modal-overlay">
            <button type="button" class="kanban-modal-backdrop" wire:click="closePreview" aria-label="Tutup preview"></button>
            <div class="kanban-modal-content">
                <div class="kanban-modal-controls">
                    <span class="kanban-modal-zoom-label" data-testid="zoom-label">{{ round($previewZoom * 100) }}%</span>
                    <button type="button" wire:click="zoomOut" data-testid="btn-zoom-out" aria-label="Perkecil">&minus;</button>
                    <button type="button" wire:click="resetZoom" data-testid="btn-zoom-reset" aria-label="Reset zoom">1:1</button>
                    <button type="button" wire:click="zoomIn" data-testid="btn-zoom-in" aria-label="Perbesar">&plus;</button>
                    <button type="button" wire:click="closePreview" data-testid="btn-close-preview" aria-label="Tutup">&times;</button>
                </div>
                <img
                    src="{{ \Illuminate\Support\Facades\Storage::url($previewPhoto) }}"
                    alt="Preview foto"
                    class="kanban-modal-image"
                    data-testid="kanban-modal-image"
                    style="transform: scale({{ $previewZoom }})"
                >
            </div>
        </div>
    @endif
</main>

@script
<script>
    function addKanbanFiles(files) {
        const input = document.getElementById('kanban-photo-input');
        if (!input || files.length === 0) return;

        const transfer = new DataTransfer();
        Array.from(input.files).forEach((file) => transfer.items.add(file));
        files.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    document.addEventListener('paste', (event) => {
        const files = Array.from(event.clipboardData?.files || []).filter(
            (file) => file.type.startsWith('image/')
        );
        if (files.length === 0) return;

        event.preventDefault();
        addKanbanFiles(files);
    });

    document.addEventListener('wheel', (event) => {
        if (!event.target.closest('.kanban-modal-image')) return;
        event.preventDefault();
        if (event.deltaY < 0) {
            $wire.zoomIn();
        } else {
            $wire.zoomOut();
        }
    }, { passive: false });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.querySelector('[data-testid="kanban-modal-overlay"]')) {
            $wire.closePreview();
            return;
        }

        if (event.key === 'Escape' && document.querySelector('[data-testid="kanban-form-overlay"]')) {
            $wire.closeFormModal();
        }
    });
</script>
@endscript
