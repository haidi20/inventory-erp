<?php

namespace App\Modules\Operational\Master\Entities;

/**
 * MasterCustomerEntity
 * 
 * Entitas untuk mengelola data pelanggan (Customer).
 */
class MasterCustomerEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public ?string $name = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public array $addresses = [],
        public array $contacts = [],
        public bool $is_active = true,
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
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'addresses' => $this->addresses,
            'contacts' => $this->contacts,
            'is_active' => $this->is_active,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            branch_uuid: $model->branch_uuid,
            company_uuid: $model->company_uuid,
            name: $model->name,
            phone: $model->phone,
            email: $model->email,
            address: $model->address,
            addresses: $model->relationLoaded('addresses') ? $model->addresses->toArray() : [],
            contacts: $model->relationLoaded('contacts') ? $model->contacts->toArray() : [],
            is_active: (bool) $model->is_active,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            name: $data['name'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            address: $data['address'] ?? null,
            addresses: $data['addresses'] ?? [],
            contacts: $data['contacts'] ?? [],
            is_active: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            search: $data['search'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 15),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }
}
