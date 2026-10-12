<?php

namespace App\Modules\Operational\Procurement\Services;

use App\Modules\Operational\Procurement\Repositories\ProcurementRepository;
use App\Modules\Operational\Procurement\Entities\PurchaseReturnEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseReturnItemEntity;

/**
 * ProcurementPublicService
 *
 * Service publik Operational\Procurement yang boleh di-import modul lain.
 * Semua parameter dan return value berbasis Entity/DTO — tidak boleh return Model Eloquent.
 */
class ProcurementPublicService
{
    public function __construct(
        private ProcurementRepository $repository
    ) {}

    public function findPurchaseByUuid(string $uuid): mixed
    {
        return $this->repository->findPurchaseByUuid($uuid);
    }

    public function markPurchaseReceived(mixed $purchase): void
    {
        $this->repository->markPurchaseReceived($purchase);
    }

    public function countReturnsToday(): int
    {
        return $this->repository->countReturnsToday();
    }

    public function deleteReturn(mixed $return): void
    {
        $this->repository->deleteReturn($return);
    }

    public function findReturnByUuid(string $uuid): mixed
    {
        return $this->repository->findReturnByUuid($uuid);
    }

    public function createReturn(PurchaseReturnEntity $entity): mixed
    {
        return $this->repository->createReturn($entity);
    }

    public function createReturnItem(PurchaseReturnItemEntity $entity): mixed
    {
        return $this->repository->createReturnItem($entity);
    }
}
