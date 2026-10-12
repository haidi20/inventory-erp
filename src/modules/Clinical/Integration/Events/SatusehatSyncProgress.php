<?php

namespace App\Modules\Clinical\Integration\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SatusehatSyncProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $companyUuid,
        public readonly string $syncBatchUuid,
        public readonly string $status,
        public readonly int $percentage,
        public readonly array $errors = [],
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('company.'.$this->companyUuid),
        ];
    }

    public function broadcastAs(): string
    {
        return 'satusehat.sync.progress';
    }

    public function broadcastWith(): array
    {
        return [
            'company_uuid' => $this->companyUuid,
            'sync_batch_uuid' => $this->syncBatchUuid,
            'status' => $this->status,
            'percentage' => $this->percentage,
            'errors' => $this->errors,
        ];
    }
}
