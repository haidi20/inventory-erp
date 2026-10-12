<?php

namespace App\Modules\Clinical\Registration\Entities;

class RegistrationEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $queue_no = null,
        public ?string $patient_uuid = null,
        public ?string $doctor_uuid = null,
        public ?string $status = null,
        public ?string $registered_at = null,
        public ?string $notes = null,
        public ?string $actor_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            queue_no: $data['queue_no'] ?? null,
            patient_uuid: $data['patient_uuid'] ?? null,
            doctor_uuid: $data['doctor_uuid'] ?? null,
            status: isset($data['status']) ? trim((string) $data['status']) : null,
            registered_at: $data['registered_at'] ?? null,
            notes: $data['notes'] ?? null,
            actor_uuid: $data['actor_uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'queue_no' => $this->queue_no,
            'patient_uuid' => $this->patient_uuid,
            'doctor_uuid' => $this->doctor_uuid,
            'status' => $this->status,
            'registered_at' => $this->registered_at,
            'notes' => $this->notes,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
        ];
    }
}