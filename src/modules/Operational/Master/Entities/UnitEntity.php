<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * UnitEntity
 * 
 * Entitas untuk mengelola data satuan (Unit) di seluruh sistem.
 */
class UnitEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?string $symbol = null,
        public ?string $description = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public bool $is_active = true,
        public bool $is_primary = false,
        public bool $is_base_global = false,
        public ?string $search = null,
        public ?string $search_column = null,
        public ?string $sort_by = 'code',
        public ?string $sort_direction = 'asc',
        public int $page = 1,
        public int $per_page = 20,
        public ?bool $filter_active = null,
        public ?string $actor_uuid = null
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'description' => $this->description,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'is_active' => $this->is_active,
            'is_primary' => $this->is_primary,
            'is_base_global' => $this->is_base_global,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            code: $model->code,
            name: $model->name,
            symbol: $model->symbol,
            description: $model->description,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            is_active: (bool) $model->is_active,
            is_primary: (bool) ($model->is_primary ?? false),
            is_base_global: (bool) ($model->is_base_global ?? false),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            code: $data['code'] ?? null,
            name: $data['name'] ?? null,
            symbol: $data['symbol'] ?? null,
            description: $data['description'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            is_primary: filter_var($data['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            is_base_global: filter_var($data['is_base_global'] ?? false, FILTER_VALIDATE_BOOLEAN),
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? null,
            sort_by: $data['sort_by'] ?? 'code',
            sort_direction: $data['sort_direction'] ?? 'asc',
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 20),
            actor_uuid: $data['actor_uuid'] ?? null,
            filter_active: array_key_exists('filter_active', $data) && $data['filter_active'] !== null ? filter_var($data['filter_active'], FILTER_VALIDATE_BOOLEAN) : null,
        );
    }
}
