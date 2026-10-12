<?php

namespace App\Modules\Core\Identity\Entities;

/**
 * UserEntity
 *
 * Entitas untuk mengelola data User/Pengguna aplikasi.
 */
class UserEntity
{
    public function __construct(
        public ?string $uuid = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $username = null,
        public ?string $password = null,
        public ?string $branch_uuid = null,
        public ?string $company_uuid = null,
        public bool $is_active = true,
        public array $role_ids = [],
        public array $permissions = [],
        public ?string $role_uuid = null,
        public ?string $employee_uuid = null,
        public array $roles = [],
        public ?string $search = null,
        public ?string $search_column = 'all',
        public ?string $role_filter = null,
        public ?string $sort_by = 'name',
        public ?string $sort_direction = 'asc',
        public int $page = 1,
        public int $per_page = 10,
        public ?string $actor_uuid = null
    ) {}

    public static function fromModel($model): self
    {
        $allRoles = $model->roles ?? collect();

        return new self(
            uuid: $model->uuid,
            name: $model->name,
            email: $model->email,
            username: $model->username,
            branch_uuid: $model->active_branch_uuid,
            company_uuid: $model->active_company_uuid,
            is_active: (bool) $model->is_active,
            employee_uuid: $model->employee_uuid,
            roles: $allRoles->map(fn ($role) => [
                'name' => $role->name,
                'label' => $role->label ?? $role->name,
            ])->values()->toArray(),
            role_ids: $allRoles->pluck('uuid')->toArray(),
            permissions: $model->getAllPermissions()->pluck('name')->values()->toArray(),
            role_uuid: $allRoles->first()?->uuid,
        );
    }

    public function hasRole(string $role): bool
    {
        return collect($this->roles)->contains(fn (array $item): bool => $item['name'] === $role);
    }

    public function can(string $permission): bool
    {
        return in_array('*', $this->permissions, true) || in_array($permission, $this->permissions, true);
    }

    public static function fromArray(array $data): self
    {
        // Accept role_uuids (array, from frontend) or role_ids (legacy)
        $roleIds = $data['role_uuids'] ?? $data['role_ids'] ?? [];
        if (! is_array($roleIds)) {
            $roleIds = array_filter([$roleIds]);
        }

        return new self(
            uuid: $data['uuid'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            username: $data['username'] ?? null,
            password: $data['password'] ?? null,
            branch_uuid: $data['branch_uuid'] ?? null,
            company_uuid: $data['company_uuid'] ?? null,
            role_ids: $roleIds,
            role_uuid: $data['role_uuid'] ?? ($roleIds[0] ?? null),
            employee_uuid: $data['employee_uuid'] ?? null,
            search: $data['search'] ?? null,
            search_column: $data['search_column'] ?? 'all',
            role_filter: $data['role'] ?? $data['role_filter'] ?? null,
            sort_by: $data['sort_by'] ?? 'name',
            sort_direction: $data['sort_direction'] ?? 'asc',
            page: isset($data['page']) ? (int) $data['page'] : 1,
            per_page: isset($data['per_page']) ? (int) $data['per_page'] : 10,
            actor_uuid: $data['actor_uuid'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'password' => $this->password,
            'branch_uuid' => $this->branch_uuid,
            'company_uuid' => $this->company_uuid,
            'role_ids' => $this->role_ids,
            'role_uuid' => $this->role_uuid,
            'employee_uuid' => $this->employee_uuid,
            'search' => $this->search,
            'search_column' => $this->search_column,
            'role_filter' => $this->role_filter,
            'sort_by' => $this->sort_by,
            'sort_direction' => $this->sort_direction,
            'page' => $this->page,
            'per_page' => $this->per_page,
            'actor_uuid' => $this->actor_uuid,
        ];
    }
}
