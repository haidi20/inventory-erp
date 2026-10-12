<?php

namespace App\Modules\Core\Authorization\Entities;

/**
 * RoleEntity
 * 
 * Entitas untuk mengelola Role/Peran akses pengguna.
 */
class RoleEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $name = null,
        public ?string $label = null,
        public array $permission_ids = [],
        public array $permission_names = [],
        public ?string $search = null,
        public int $page = 1,
        public int $per_page = 10,
        public ?string $actor_uuid = null
    ) {}


    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'] ?? null,
            name: $data['name'] ?? null,
            label: $data['label'] ?? null,
            permission_ids: $data['permissions'] ?? $data['permission_ids'] ?? [],
            permission_names: $data['permission_names'] ?? [],
            search: $data['search'] ?? null,
            page: (int) ($data['page'] ?? 1),
            per_page: (int) ($data['per_page'] ?? 10),
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }

    public static function fromModel($model): self
    {
        $permissions = $model->permissions ?? collect();

        return new self(
            uuid: $model->uuid,
            name: $model->name,
            label: $model->label,
            permission_ids: method_exists($permissions, 'pluck') ? $permissions->pluck('uuid')->toArray() : [],
            permission_names: method_exists($permissions, 'pluck') ? $permissions->pluck('name')->toArray() : [],
        );
    }
}
