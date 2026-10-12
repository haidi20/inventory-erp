<?php

namespace App\Modules\Operational\Procurement\Repositories;

use App\Modules\Operational\Procurement\Entities\PurchaseReturnEntity;

/**
 * Interface PurchaseReturnRepositoryInterface
 *
 * Kontrak untuk pengelolaan retur pembelian kepada supplier.
 */
interface PurchaseReturnRepositoryInterface
{
    public function getPageData(PurchaseReturnEntity $entity): array;
    public function findByUUID(string $uuid, string $branchUuid, string $companyUuid): ?PurchaseReturnEntity;
    public function save(PurchaseReturnEntity $entity): ?PurchaseReturnEntity;
    public function delete(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): bool;
}

