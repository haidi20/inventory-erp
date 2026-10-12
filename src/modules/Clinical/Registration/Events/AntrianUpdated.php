<?php

namespace App\Modules\Clinical\Registration\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AntrianUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $branchUuid,
        public readonly string $companyUuid,
        public readonly string $registrationUuid,
        public readonly string $status,
        public readonly ?string $doctorName = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('branch.'.$this->branchUuid),
        ];
    }

    public function broadcastAs(): string
    {
        return 'antrian.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'branch_uuid' => $this->branchUuid,
            'company_uuid' => $this->companyUuid,
            'registration_uuid' => $this->registrationUuid,
            'status' => $this->status,
            'doctor_name' => $this->doctorName,
        ];
    }
}
