<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * LocationEntity
 * 
 * Entitas untuk mengelola data gudang (Location).
 */
class LocationEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?string $description = null,
        public bool $is_active = true,
        public bool $can_sell = true,
        public bool $is_primary = false,
        public ?string $address_uuid = null,
        public ?string $jenis_uuid = null,
        public ?string $search = null,
        public ?string $search_column = null,
        public int $page = 1,
        public int $per_page = 15,
        public int $start = 0,
        public int $length = 15,
        public ?string $actor_uuid = null,
        public ?bool $filter_active = null,
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
            'can_sell' => $this->can_sell,
            'is_primary' => $this->is_primary,
            'address_uuid' => $this->address_uuid,
            'jenis_uuid' => $this->jenis_uuid,
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
            can_sell: (bool) $model->can_sell,
            is_primary: (bool) ($model->is_primary ?? false),
            address_uuid: (string) $model->address_uuid,
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
            can_sell: filter_var($data['can_sell'] ?? true, FILTER_VALIDATE_BOOLEAN),
            is_primary: filter_var($data['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            address_uuid: isset($data['address_uuid']) ? (string) $data['address_uuid'] : null,
            jenis_uuid: isset($data['jenis_uuid']) ? (string) $data['jenis_uuid'] : null,
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 15),
            actor_uuid: $data['actor_uuid'] ?? null,
            filter_active: array_key_exists('filter_active', $data) && $data['filter_active'] !== null ? filter_var($data['filter_active'], FILTER_VALIDATE_BOOLEAN) : null,
        );
    }
}
