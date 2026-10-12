<?php

namespace App\Modules\Clinical\MedicalRecord\Entities;

/**
 * DoctorTaskFilterEntity
 * 
 * Entitas untuk mengelola filter parameter pada tugas dokter.
 */
class DoctorTaskFilterEntity
{
    public function __construct(
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $doctor_uuid = null,
        public ?string $status = null,
        public ?string $date = null,
        public ?string $actor_uuid = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            doctor_uuid: $data['doctor_uuid'] ?? null,
            status: $data['status'] ?? null,
            date: $data['date'] ?? null,
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}

