<?php

namespace App\Modules\Operational\Manufacturing\Repositories;

use App\Modules\Operational\Manufacturing\Entities\BomEntity;

/**
 * Interface BomRepositoryInterface
 *
 * Kontrak untuk pengelolaan data Formula (BOM).
 */
interface BomRepositoryInterface
{
    public function getPageData(BomEntity $entity): array;
    public function findByUUID(string $uuid): ?BomEntity;
    public function save(BomEntity $entity): ?BomEntity;
    public function saveMany(array $entities): array;
    public function delete(string $uuid, string $actorUuid): bool;
    public function getByDocumentNo(string $docNo): ?BomEntity;
    public function listForExport(BomEntity $entity): array;
}

