<?php

namespace App\Modules\Operational\Sales\Entities;

class ExaminationEntity
{
    public function __construct(
        public ?string $registration_uuid = null,
        public ?string $doctor_uuid = null,
        public ?string $complaint = null,
        public ?string $diagnosis = null,
        public array $services = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            registration_uuid: $data['registration_uuid'] ?? null,
            doctor_uuid: $data['doctor_uuid'] ?? null,
            complaint: $data['complaint'] ?? null,
            diagnosis: $data['diagnosis'] ?? null,
            services: $data['services'] ?? [],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'registration_uuid' => $this->registration_uuid,
            'doctor_uuid'       => $this->doctor_uuid,
            'complaint'         => $this->complaint,
            'diagnosis'         => $this->diagnosis,
            'services'          => $this->services,
        ], fn($v) => $v !== null);
    }
}

