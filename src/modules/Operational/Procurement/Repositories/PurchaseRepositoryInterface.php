<?php

namespace App\Modules\Operational\Procurement\Repositories;

use App\Modules\Operational\Procurement\Entities\PurchaseRecordEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseFilterEntity;
use App\Modules\Operational\Procurement\Entities\PurchaseListResultEntity;
use App\Modules\Operational\Procurement\Models\Purchase;

interface PurchaseRepositoryInterface
{
    public function save(PurchaseRecordEntity $entity): PurchaseRecordEntity;
    public function findAll(PurchaseFilterEntity $filter): PurchaseListResultEntity;
    public function findByUuid(string $uuid, string $branchUuid, string $companyUuid): ?PurchaseRecordEntity;
    public function delete(string $uuid, string $branchUuid, string $companyUuid, string $actorUuid): void;
}

