<?php

namespace App\Modules\Finance\Journal\Entities;

class AccountEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?int $id = null,
        public ?string $code = null,
        public ?string $name = null,
        public ?string $type = null,
        public ?string $type_saldo = null,
        public ?bool $is_header = null,
        public ?bool $is_active = null,
        public ?string $parent_uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $actor_uuid = null,
        public ?string $search = null,
        public int $page = 1,
        public int $per_page = 20,
        public ?string $search_column = null,
        public ?string $sort_by = null,
        public ?string $sort_direction = 'asc',
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'type_saldo' => $this->type_saldo,
            'parent_uuid' => $this->parent_uuid,
            'is_header' => $this->is_header,
            'is_active' => $this->is_active,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            code: $model->code,
            name: $model->name,
            type: $model->type,
            type_saldo: $model->type_saldo,
            is_header: (bool) $model->is_header,
            is_active: (bool) $model->is_active,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            id: isset($data['id']) ? (int) $data['id'] : null,
            code: $data['code'] ?? null,
            name: $data['name'] ?? null,
            type: $data['type'] ?? null,
            type_saldo: $data['type_saldo'] ?? null,
            parent_uuid: $data['parent_uuid'] ?? null,
            is_header: array_key_exists('is_header', $data) ? filter_var($data['is_header'], FILTER_VALIDATE_BOOLEAN) : null,
            is_active: array_key_exists('is_active', $data) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            actor_uuid: $data['actor_uuid'] ?? null,
            search: $data['search'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 20),
            search_column: $data['search_column'] ?? null,
            sort_by: $data['sort_by'] ?? null,
            sort_direction: $data['sort_direction'] ?? 'asc',
        );
    }
}
