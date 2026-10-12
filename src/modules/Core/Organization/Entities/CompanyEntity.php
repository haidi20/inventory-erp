<?php

namespace App\Modules\Core\Organization\Entities;

/**
 * CompanyEntity
 * 
 * Entitas untuk mengelola data perusahaan dalam sistem.
 */
class CompanyEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $name = null,
        public ?string $clinic_type = 'klinik pratama',
        public ?string $registration_number = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $website = null,
        public ?string $timezone = 'Asia/Makassar',
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
            'name' => $this->name,
            'clinic_type' => $this->clinic_type,
            'registration_number' => $this->registration_number,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'website' => $this->website,
            'timezone' => $this->timezone,
            'is_active' => $this->is_active,
        ];
    }

    public static function fromModel($model): self
    {
        return new self(
            uuid: $model->uuid,
            name: $model->name,
            clinic_type: $model->clinic_type,
            registration_number: $model->registration_number,
            phone: $model->phone,
            email: $model->email,
            address: $model->address,
            website: $model->website,
            timezone: $model->timezone ?? 'Asia/Makassar',
            is_active: (bool) $model->is_active,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            name: $data['name'] ?? null,
            clinic_type: $data['clinic_type'] ?? 'klinik pratama',
            registration_number: $data['registration_number'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            address: $data['address'] ?? null,
            website: $data['website'] ?? null,
            timezone: $data['timezone'] ?? 'Asia/Makassar',
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
