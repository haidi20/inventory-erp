<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * LookupTypeEntity
 *
 * Entitas untuk mengelola tipe lookup (kategori data referensi).
 */
class LookupTypeEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $company_uuid = null,
        public bool $is_active = true,
        public ?string $search = null,
        public ?string $search_column = null,
        public int $page = 1,
        public int $per_page = 20,
        public ?string $actor_uuid = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            code: $data['code'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            is_active: (bool) ($data['is_active'] ?? true),
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 20),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }

    public static function fromModel(object $model): self
    {
        return new self(
            uuid: $model->uuid ?? null,
            code: $model->code ?? null,
            name: $model->name ?? null,
            description: $model->description ?? null,
            company_uuid: $model->company_uuid ?? null,
            is_active: (bool) ($model->is_active ?? true),
        );
    }
}
