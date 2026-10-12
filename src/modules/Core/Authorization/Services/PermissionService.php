<?php

namespace App\Modules\Core\Authorization\Services;

use App\Modules\Core\Authorization\Repositories\PermissionRepositoryInterface;
use Illuminate\Support\Str;

use App\Modules\Core\Authorization\Entities\PermissionEntity;
use App\Modules\Core\Authorization\Entities\PermissionFilterEntity;
use App\Modules\Core\Authorization\Entities\PermissionListResultEntity;

class PermissionService
{
    private PermissionRepositoryInterface $repo;

    public function __construct(PermissionRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function GetPermissions(PermissionFilterEntity $filter): PermissionListResultEntity
    {
        $filter->page = $filter->page ?? 1;
        $filter->per_page = $filter->per_page ?? 10;

        return $this->repo->getPermissions($filter);
    }

    public function GetPermission(string $uuid): ?PermissionEntity
    {
        return $this->repo->findById($uuid);
    }

    public function Store(PermissionEntity $entity): bool
    {
        if (empty($entity->uuid)) {
            $entity->uuid = (string) Str::uuid();
        }
        $entity->name = Str::snake(strtolower($entity->name));
        $entity->label = $entity->label ?? $entity->name;

        return $this->repo->store($entity);
    }

    public function Update(string $uuid, PermissionEntity $entity): bool
    {
        $permission = $this->repo->findById($uuid);
        if (!$permission) {
            throw new \Exception("permission not found");
        }

        $entity->name = Str::snake(strtolower($entity->name));
        $entity->label = $entity->label ?? $entity->name;

        return $this->repo->update($uuid, $entity);
    }

    public function Destroy(string $uuid)
    {
        if (empty(trim($uuid))) {
            throw new \Exception("permission uuid is required");
        }
        return $this->repo->destroy($uuid);
    }
}
