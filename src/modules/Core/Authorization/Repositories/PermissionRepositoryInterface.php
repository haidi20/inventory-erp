<?php

namespace App\Modules\Core\Authorization\Repositories;

use App\Modules\Core\Authorization\Entities\PermissionEntity;
use App\Modules\Core\Authorization\Entities\PermissionFilterEntity;
use App\Modules\Core\Authorization\Entities\PermissionListResultEntity;

interface PermissionRepositoryInterface
{
    public function getPermissions(PermissionFilterEntity $filter): PermissionListResultEntity;
    public function findById(string $uuid): ?PermissionEntity;
    public function store(PermissionEntity $entity): bool;
    public function update(string $uuid, PermissionEntity $entity): bool;
    public function destroy(string $uuid): bool;
}
