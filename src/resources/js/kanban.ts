import Swal from 'sweetalert2';

type DeletePayload = { id?: string; title?: string };

let registered = false;

function registerDeleteConfirmation(): void {
    if (registered) {
        return;
    }

    const livewire = (window as unknown as { Livewire?: any }).Livewire;

    if (!livewire) {
        return;
    }

    registered = true;

    livewire.on('kanban-confirm-delete', ({ id, title }: DeletePayload) => {
        void Swal.fire({
            title: 'Hapus kartu ini?',
            text: title ? `"${title}" akan dihapus permanen.` : 'Kartu akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
        }).then((result) => {
            if (result.isConfirmed && id) {
                livewire.first().call('removeCard', id);
            }
        });
    });
}

registerDeleteConfirmation();
document.addEventListener('livewire:init', registerDeleteConfirmation);
document.addEventListener('livewire:initialized', registerDeleteConfirmation);
