<?php

namespace App\Modules\Operational\Procurement\Services;

use App\Modules\Operational\Procurement\Entities\PurchaseReturnEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseReturnItemEntity;
use App\Modules\Operational\Procurement\Repositories\PurchaseReturnRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PurchaseReturnService
{
    protected $repo;

    public function __construct(PurchaseReturnRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getList(PurchaseReturnEntity $entity): Collection
    {
        $result = $this->repo->getPageData($entity);
        return collect($result['items'] ?? []);
    }

    public function getDetail(string $uuid, string $branchUuid, string $companyUuid): ?PurchaseReturnEntity
    {
        return $this->repo->findByUUID($uuid, $branchUuid, $companyUuid);
    }

    public function createOrUpdate(PurchaseReturnEntity $entity): PurchaseReturnEntity
    {
        if (empty($entity->uuid)) {
            $entity->uuid = (string) Str::uuid();
        }
        
        $grossTotal = 0;
        foreach ($entity->items as $item) {
            $subtotal = ($item->quantity * $item->price) - ($item->discount ?? 0);
            $grossTotal += $subtotal;
            $item->subtotal = $subtotal;
        }

        $entity->total = $grossTotal;
        $entity->net_amount = $grossTotal + ($entity->tax_amount ?? 0);

        $this->repo->save($entity);

        return $this->repo->findByUUID($entity->uuid, $entity->branch_uuid, $entity->company_uuid);
    }

    public function delete(PurchaseReturnEntity $entity): bool
    {
        return $this->repo->delete($entity->uuid, $entity->branch_uuid, $entity->company_uuid, $entity->actor_uuid);
    }
}

