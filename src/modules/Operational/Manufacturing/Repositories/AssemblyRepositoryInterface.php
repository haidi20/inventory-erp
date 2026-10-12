<?php

namespace App\Modules\Operational\Manufacturing\Repositories;

use App\Modules\Operational\Manufacturing\Entities\AssemblyEntity;

/**
 * Interface AssemblyRepositoryInterface
 *
 * Kontrak untuk pengelolaan data perakitan (assembly) barang.
 */
interface AssemblyRepositoryInterface
{
    public function index(AssemblyEntity $entity): array;
    public function findByID(string $uuid): ?AssemblyEntity;
    public function save(AssemblyEntity $entity): ?AssemblyEntity;
    public function saveMany(array $entities): array;
    public function delete(string $uuid): bool;
}

