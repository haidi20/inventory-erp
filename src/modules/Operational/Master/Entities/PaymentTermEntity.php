<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * PaymentTermEntity
 * 
 * Entitas untuk mengelola termin pembayaran (Payment Term).
 */
class PaymentTermEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $name = null,
        public ?string $description = null,
        public int $due_days = 0,
        public bool $is_active = true,
        public bool $is_primary = false,
        public ?string $search = null,
        public ?string $search_column = null,
        public int $page = 1,
        public int $per_page = 15,
        public ?bool $filter_active = null,
        public ?string $actor_uuid = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'name' => $this->name,
            'description' => $this->description,
            'due_days' => $this->due_days,
            'is_active' => $this->is_active,
            'is_primary' => $this->is_primary,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            name: $model->name,
            description: $model->description,
            due_days: (int) $model->due_days,
            is_active: (bool) $model->is_active,
            is_primary: (bool) ($model->is_primary ?? false),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            due_days: (int) ($data['due_days'] ?? 0),
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            is_primary: filter_var($data['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 15),
            actor_uuid: $data['actor_uuid'] ?? null,
            filter_active: array_key_exists('filter_active', $data) && $data['filter_active'] !== null ? filter_var($data['filter_active'], FILTER_VALIDATE_BOOLEAN) : null,
        );
    }
}
