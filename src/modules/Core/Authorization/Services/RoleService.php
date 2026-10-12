<?php

namespace App\Modules\Core\Authorization\Services;

use App\Modules\Core\Authorization\Repositories\RoleRepositoryInterface;
use App\Modules\Core\Authorization\Entities\RoleEntity;
use Illuminate\Support\Str;
use Exception;

class RoleService
{
    protected $repo;

    public function __construct(RoleRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function GetRoles(RoleEntity $entity): array
    {
        return $this->repo->getRoles($entity);
    }

    public function GetRole(string $uuid): ?RoleEntity
    {
        return $this->repo->findById($uuid);
    }

    public function Store(RoleEntity $entity): RoleEntity
    {
        if (empty($entity->uuid)) {
            $entity->uuid = (string) Str::uuid();
        }
        if (empty($entity->name) && !empty($entity->label)) {
            $entity->name = strtolower(str_replace(' ', '_', $entity->label));
        }

        $role = $this->repo->store($entity);

        // Permission sync logic can be handled here if needed via entity properties
        return $role;
    }

    public function Update(RoleEntity $entity): RoleEntity
    {
        if (empty($entity->uuid)) {
            throw new Exception("role uuid is required");
        }
        
        $role = $this->repo->findById($entity->uuid);
        if (!$role) {
            throw new Exception("role not found");
        }

        if (empty($entity->name) && !empty($entity->label)) {
            $entity->name = strtolower(str_replace(' ', '_', $entity->label));
        }

        return $this->repo->update($entity);
    }

    public function Destroy(RoleEntity $entity): bool
    {
        if (empty($entity->uuid)) {
            throw new Exception("role uuid is required");
        }
        $this->repo->delete($entity->uuid, $entity->actor_uuid ?? '');

        return true;
    }

    public function CopyPermissions(string $sourceUUID, string $targetUUID, string $actorUuid = null): void
    {
        $sourceRole = $this->repo->findById($sourceUUID);
        if (!$sourceRole) {
            throw new Exception("source role not found");
        }

        $permissionIDs = [];
        // Assuming RoleEntity has permission names/uuids
        if (!empty($sourceRole->permission_names)) {
             $permissionIDs = $sourceRole->permission_names;
        }

        $this->repo->syncPermissions($targetUUID, $permissionIDs, $actorUuid ?? '');
    }
}
