<?php

namespace App\Modules\Clinical\Registration\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatientRegistered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $patientUuid,
        public readonly string $branchUuid,
        public readonly string $companyUuid,
        public readonly string $patientName,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('branch.'.$this->branchUuid),
        ];
    }

    public function broadcastAs(): string
    {
        return 'patient.registered';
    }

    public function broadcastWith(): array
    {
        return [
            'patient_uuid' => $this->patientUuid,
            'branch_uuid' => $this->branchUuid,
            'company_uuid' => $this->companyUuid,
            'patient_name' => $this->patientName,
        ];
    }
}
