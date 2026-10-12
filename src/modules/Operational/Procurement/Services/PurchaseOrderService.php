<?php

namespace App\Modules\Operational\Procurement\Services;

use App\Modules\Operational\Procurement\Repositories\PurchaseOrderRepositoryInterface;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderFilterEntity;
use Illuminate\Support\Str;

class PurchaseOrderService
{
    private PurchaseOrderRepositoryInterface $repo;

    public function __construct(PurchaseOrderRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function GetList(PurchaseOrderFilterEntity $filter)
    {
        return $this->repo->findAll($filter);
    }

    public function GetDetail(string $uuid)
    {
        return $this->repo->findByUuid($uuid);
    }

    public function CreateOrUpdate(PurchaseOrderEntity $entity)
    {
        if (empty($entity->uuid)) {
            $entity->uuid = (string) Str::uuid();
        }

        foreach ($entity->items as $item) {
            if (empty($item->uuid)) {
                $item->uuid = (string) Str::uuid();
            }
            $item->subtotal = ($item->quantity * $item->price) - ($item->discount ?? 0);
        }

        return $this->repo->save($entity);
    }

    public function Delete(string $uuid, string $actorUuid)
    {
        return $this->repo->delete($uuid, $actorUuid);
    }

    public function findAll(PurchaseOrderFilterEntity $filter)
    {
        return $this->repo->findAll($filter);
    }

    public function findByUuid(string $uuid, ?string $companyUuid = null)
    {
        return $this->repo->findByUuid($uuid, $companyUuid);
    }

    public function save(PurchaseOrderEntity $entity)
    {
        return $this->repo->save($entity);
    }
}
