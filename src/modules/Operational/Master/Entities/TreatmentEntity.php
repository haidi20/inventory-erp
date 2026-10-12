<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * TreatmentEntity
 * 
 * Entitas untuk mengelola data tindakan atau layanan medis (Treatment/Service).
 */
class TreatmentEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?int $id = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?int $duration_minutes = 0,
        public float|int|null $price = 0.0,
        public float|int|null $doctor_fee = 0.0,
        public bool $is_active = true,
        public ?string $search = null,
        public ?string $search_column = null,
        public int $page = 1,
        public int $per_page = 20,
        public ?string $actor_uuid = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'code' => $this->code,
            'name' => $this->name,
            'duration_minutes' => $this->duration_minutes,
            'price' => $this->price,
            'doctor_fee' => $this->doctor_fee,
            'is_active' => $this->is_active,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            id: $model->id,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            code: $model->code,
            name: $model->name,
            duration_minutes: (int) $model->duration_minutes,
            price: (float) $model->price,
            doctor_fee: (float) $model->doctor_fee,
            is_active: (bool) $model->is_active,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            id: isset($data['id']) ? (int) $data['id'] : null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            search: isset($data['search']) ? trim((string) $data['search']) : null,
            search_column: $data['search_column'] ?? null,
            code: $data['code'] ?? null,
            name: $data['name'] ?? null,
            duration_minutes: isset($data['duration_minutes']) ? (int) $data['duration_minutes'] : 0,
            price: isset($data['price']) ? (float) $data['price'] : 0.0,
            doctor_fee: isset($data['doctor_fee']) ? (float) $data['doctor_fee'] : 0.0,
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 20),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}
