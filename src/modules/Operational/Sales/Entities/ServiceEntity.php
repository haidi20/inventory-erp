<?php

namespace App\Modules\Operational\Sales\Entities;

class ServiceEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?int $duration_minutes = null,
        public float|int|null $price = null,
        public float|int|null $doctor_fee = null,
        public ?bool $is_active = null,
        public ?string $actor_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            code: isset($data['code']) ? trim((string) $data['code']) : null,
            name: isset($data['name']) ? trim((string) $data['name']) : null,
            duration_minutes: isset($data['duration_minutes']) ? (int) $data['duration_minutes'] : null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            doctor_fee: isset($data['doctor_fee']) ? (float) $data['doctor_fee'] : null,
            is_active: array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            actor_uuid: $data['actor_uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'duration_minutes' => $this->duration_minutes,
            'price' => $this->price,
            'doctor_fee' => $this->doctor_fee,
            'is_active' => $this->is_active,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
        ];
    }
}