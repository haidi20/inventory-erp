<?php

namespace App\Modules\Clinical\MedicalRecord\Entities;

class EncounterEntity
{
    /**
     * @param array<int,array> $items
     */
    public function __construct(
        public ?string $uuid = null,
        public ?string $registration_uuid = null,
        public ?string $patient_uuid = null,
        public ?string $doctor_uuid = null,
        public ?string $complaint = null,
        public ?string $diagnosis = null,
        public ?string $notes = null,
        public ?string $status = null,
        public array $items = [],
        public ?string $actor_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            registration_uuid: $data['registration_uuid'] ?? null,
            patient_uuid: $data['patient_uuid'] ?? null,
            doctor_uuid: $data['doctor_uuid'] ?? null,
            complaint: $data['complaint'] ?? null,
            diagnosis: $data['diagnosis'] ?? null,
            notes: $data['notes'] ?? null,
            status: isset($data['status']) ? trim((string) $data['status']) : null,
            items: array_values(array_filter($data['items'] ?? [], fn ($item) => is_array($item))),
            actor_uuid: $data['actor_uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'registration_uuid' => $this->registration_uuid,
            'patient_uuid' => $this->patient_uuid,
            'doctor_uuid' => $this->doctor_uuid,
            'complaint' => $this->complaint,
            'diagnosis' => $this->diagnosis,
            'notes' => $this->notes,
            'status' => $this->status,
            'items' => $this->items,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
        ];
    }
}