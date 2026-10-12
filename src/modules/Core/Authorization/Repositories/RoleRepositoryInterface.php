<?php

namespace App\Modules\Core\Authorization\Repositories;

use App\Modules\Core\Authorization\Entities\RoleEntity;

/**
 * RoleRepositoryInterface
 * 
 * Kontrak untuk repository yang mengelola Role dan Permission.
 */
interface RoleRepositoryInterface
{
    public function getRoles(RoleEntity $entity): array;
    public function findById(string $uuid): ?RoleEntity;
    public function findByName(string $name): ?RoleEntity;
    public function store(RoleEntity $entity): RoleEntity;
    public function update(RoleEntity $entity): RoleEntity;
    public function delete(string $uuid, string $actorUuid): void;
    public function syncPermissions(string $roleUuid, array $permissionIds, string $actorUuid): void;
    public function getAllPermissions(): array;
}
