<?php

namespace App\Modules\Core\Organization\Entities;

/**
 * BranchEntity
 * 
 * Entitas untuk mengelola data master Cabang (Branch).
 */
class BranchEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $company_uuid = null,
        public ?string $name = null,
        public ?string $short_name = null,
        public ?string $description = null,
        public bool $is_active = true,
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
            'company_uuid' => $this->company_uuid,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            company_uuid: $model->company_uuid,
            name: $model->name,
            short_name: $model->short_name,
            description: $model->description,
            is_active: (bool) $model->is_active,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            name: $data['name'] ?? null,
            short_name: $data['short_name'] ?? null,
            description: $data['description'] ?? null,
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 15),
            actor_uuid: $data['actor_uuid'] ?? null,
            filter_active: array_key_exists('filter_active', $data) && $data['filter_active'] !== null ? filter_var($data['filter_active'], FILTER_VALIDATE_BOOLEAN) : null,
        );
    }
}
