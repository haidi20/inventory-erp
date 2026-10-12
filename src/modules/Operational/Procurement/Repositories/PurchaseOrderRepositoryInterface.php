<?php

namespace App\Modules\Operational\Procurement\Repositories;

use App\Modules\Operational\Procurement\Entities\PurchaseOrderEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderFilterEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseOrderListResultEntity;
use App\Modules\Operational\Procurement\Models\PurchaseOrder;

interface PurchaseOrderRepositoryInterface
{
    public function findAll(PurchaseOrderFilterEntity $filter): PurchaseOrderListResultEntity;
    public function findByUuid(string $uuid, ?string $company_uuid = null): ?PurchaseOrder;
    public function save(PurchaseOrderEntity $entity): ?PurchaseOrder;
    public function delete(string $uuid, string $actorUuid): bool;
}

