<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * PriceLevelEntity
 * 
 * Entitas untuk mengelola data tingkatan harga (Price Level).
 */
class PriceLevelEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?string $description = null,
        public bool $is_active = true,
        public bool $is_primary = false,
        public ?string $search = null,
        public int $page = 1,
        public int $per_page = 15,
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
            'description' => $this->description,
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
            code: $model->code,
            name: $model->name,
            description: $model->description,
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
            code: $data['code'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            is_primary: filter_var($data['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            search: $data['search'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 15),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}
