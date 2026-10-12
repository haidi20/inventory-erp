<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * SupplierEntity
 * 
 * Entitas untuk mengelola data pemasok (Supplier) dalam sistem.
 */
class SupplierEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?int $id = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $name = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public ?int $term_days = 0,
        public bool $is_active = true,
        public ?string $search = null,
        public ?string $search_column = null,
        public ?string $sort_by = 'name',
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
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'term_days' => $this->term_days,
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
            name: $model->name,
            phone: $model->phone,
            email: $model->email,
            address: $model->address,
            term_days: (int) $model->term_days,
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
            name: $data['name'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            address: $data['address'] ?? null,
            term_days: isset($data['term_days']) ? (int) $data['term_days'] : 0,
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 20),
            actor_uuid: $data['actor_uuid'] ?? null,
            filter_active: array_key_exists('filter_active', $data) && $data['filter_active'] !== null ? filter_var($data['filter_active'], FILTER_VALIDATE_BOOLEAN) : null,
        );
    }
}
