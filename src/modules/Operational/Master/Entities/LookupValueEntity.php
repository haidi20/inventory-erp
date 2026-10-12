<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * LookupValueEntity
 *
 * Entitas untuk mengelola nilai lookup (item dalam kategori tipe lookup).
 */
class LookupValueEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $lookup_type_uuid = null,
        public ?string $code = null,
        public ?string $value = null,
        public ?string $description = null,
        public int $order_priority = 0,
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
        $lookupTypeUuid = $data['lookup_type_uuid'] ?? null;

        return new self(
            uuid: $data['uuid'] ?? null,
            lookup_type_uuid: $lookupTypeUuid,
            code: $data['code'] ?? null,
            value: $data['value'] ?? ($data['display_name'] ?? null),
            description: $data['description'] ?? null,
            order_priority: (int) ($data['order_priority'] ?? 0),
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
            lookup_type_uuid: $model->lookup_set_uuid ?? null,
            code: $model->code ?? ($model->program_code ?? null),
            value: $model->value ?? ($model->display_name ?? null),
            description: $model->description ?? null,
            order_priority: (int) ($model->order_priority ?? $model->seq ?? 0),
            company_uuid: $model->company_uuid ?? null,
            is_active: (bool) ($model->is_active ?? true),
        );
    }
}
